<?php
header("Content-Type: application/json");
require_once '../library/crud.php';

$db = new Database();
$db->connect();

$action = $_SERVER['REQUEST_METHOD'];
$response = [
    'status' => 200,
    'message' => '',
    'data' => []
];

// Determine the environment and set the base URL for the PDF path
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$basePath = ($host === 'localhost') ? '/cl.englivia.com/uploads/pdf/' : '/uploads/pdf/';
$baseURL = $protocol . $host . $basePath;

switch ($action) {
    case 'GET':
        handleGetRequest($db, $response, $baseURL);
        break;

    case 'POST':
        handlePostRequest($db, $response);
        break;

    case 'PUT':
        handlePutRequest($db, $response);
        break;

    case 'DELETE':
        handleDeleteRequest($db, $response);
        break;

    default:
        $response['status'] = 405;
        $response['message'] = 'Method Not Allowed';
        echo json_encode($response);
        exit();
}

echo json_encode($response);
$db->disconnect();

function handleGetRequest($db, &$response, $baseURL)
{
    $type = isset($_GET['type']) ? intval($_GET['type']) : 2;
    $whereClauses[] = 'type = ' . $type;

    if (isset($_GET['language'])) {
        $whereClauses[] = 'language = ' . $_GET['language'];
    }

    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $whereClauses[] = 'id = ' . $id;

        $whereClause = implode(' AND ', $whereClauses);
        $db->select('tbl_categories', 'id, category_name, language, pdf, type', null, $whereClause);
        $result = $db->getResult();

        if ($result) {
            $result[0]['pdf'] = $result[0]['pdf'] != null ? $baseURL . $result[0]['pdf'] : 'Not Set';
            $response['data'] = $result;
            $response['message'] = 'PDF fetched successfully';
        } else {
            $response['status'] = 404;
            $response['message'] = 'PDF not found';
        }
    } elseif (isset($_GET['keyword'])) {
        $keyword = $db->escapeString($_GET['keyword']);
        $whereClauses[] = 'pdf LIKE "%' . $keyword . '%"';
        $whereClauses[] = 'pdf IS NOT NULL';

        $whereClause = implode(' AND ', $whereClauses);
        $db->select('tbl_categories', 'id, category_name, language, pdf, type', null, $whereClause);
        $data = $db->getResult();
        foreach ($data as &$item) {
            $item['pdf'] = $item['pdf'] != null ? $baseURL . $item['pdf'] : 'Not Set';
        }
        $response['data'] = $data;
        $response['message'] = 'PDFs fetched successfully';
    } elseif (isset($_GET['table'])) {
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
        $search = isset($_GET['search']) ? $db->escapeString($_GET['search']) : '';

        $offset = ($page - 1) * $limit;
        if (!empty($search)) {
            $whereClauses[] = 'pdf LIKE "%' . $search . '%"';
        }

        $whereClause = implode(' AND ', $whereClauses);
        $totalQuery = "SELECT COUNT(*) AS total FROM tbl_categories WHERE $whereClause";
        $db->sql($totalQuery);
        $totalResult = $db->getResult();
        $totalRecords = $totalResult[0]['total'];

        $query = "SELECT id, category_name, language, pdf, type FROM tbl_categories WHERE $whereClause LIMIT $limit OFFSET $offset";
        $db->sql($query);
        $data = $db->getResult();
        foreach ($data as &$item) {
            $item['pdf'] = $item['pdf'] != null ? $baseURL . $item['pdf'] : 'Not Set';
        }

        $response = [
            'total' => $totalRecords,
            'page' => $page,
            'limit' => $limit,
            'data' => $data,
        ];
    } else {
        $whereClauses[] = 'pdf IS NOT NULL';
        $whereClause = implode(' AND ', $whereClauses);
        $db->select('tbl_categories', 'id, category_name, language, pdf, type', null, $whereClause);
        $data = $db->getResult();
        foreach ($data as &$item) {
            $item['pdf'] = $item['pdf'] != null ? $baseURL . $item['pdf'] : 'Not Set';
        }
        $response['data'] = $data;
        $response['message'] = 'PDFs fetched successfully';
    }
}

function handlePostRequest($db, &$response)
{
    $id = intval($_POST['id']);
    $category_name = $db->escapeString($_POST['category_name']);  // Ensure the category name is sanitized
    $timestamp = time();  // Get the current timestamp

    // Initialize response
    $response = [
        'status' => 200,
        'message' => '',
        'data' => []
    ];

    // Get the protocol, host, and base path
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];

    // Determine the base directory dynamically
    $baseDir = ($host === 'localhost') ? $_SERVER['DOCUMENT_ROOT'] . '/cl.englivia.com/uploads/pdf/' : $_SERVER['DOCUMENT_ROOT'] . '/uploads/pdf/';

    // Fetch the current PDF from the database
    $db->select('tbl_categories', 'pdf', null, 'id = ' . $id . ' AND pdf IS NOT NULL');
    $result = $db->getResult();

    if (!empty($result) && isset($result[0]['pdf'])) {
        $oldPdfFileName = $result[0]['pdf'];
        $oldPdfPath = $baseDir . $oldPdfFileName;

        // Delete the old PDF file from the server
        if (file_exists($oldPdfPath)) {
            unlink($oldPdfPath);
        }
    }

    // Check if a file is selected
    if (isset($_FILES["pdf"]) && $_FILES["pdf"]["error"] == UPLOAD_ERR_OK) {
        // Define the tag
        $tag = $_POST['tag'];

        // Generate the new file name
        $fileExtension = strtolower(pathinfo($_FILES["pdf"]["name"], PATHINFO_EXTENSION));
        $newFileName = $tag . "_" . $timestamp . "_" . str_replace(' ', '_', $category_name) . "." . $fileExtension;

        // Define the full file path
        $targetFile = $baseDir . $newFileName;

        // Save the file to the server
        if (move_uploaded_file($_FILES["pdf"]["tmp_name"], $targetFile)) {
            // Update the database with the new file name
            $db->update('tbl_categories', ['category_name' => $category_name, 'pdf' => $newFileName], 'id = ' . $id);
            $response['message'] = 'Category name and PDF updated successfully';
            $response['data'] = $newFileName;
            // $response['data'] = $db->getResult();
        } else {
            // Handle error
            $response['status'] = 500;
            $response['message'] = 'Error uploading the file';
        }
    } else {
        // No file uploaded, only update the category name
        $db->update('tbl_categories', ['category_name' => $category_name], 'id = ' . $id);
        $response['message'] = 'Category name updated successfully';
        $response['data'] = $db->getResult();
    }

    echo json_encode($response);
    exit();

}

function handlePutRequest($db, &$response)
{
    $data = json_decode(file_get_contents("php://input"), true);
    $id = intval($_GET['id']);
    $params = [];

    if (isset($data['pdf'])) {
        $params['pdf'] = $db->escapeString($data['pdf']);
    }
    if (isset($data['type'])) {
        $params['type'] = intval($data['type']);
    }

    if (!empty($params)) {
        $db->update('tbl_categories', $params, 'id = ' . $id . ' AND pdf IS NOT NULL');
        outputResponse($db, $response, 'PDF updated successfully');
    } else {
        $response['status'] = 400;
        $response['message'] = 'No valid PDF data provided';
        echo json_encode($response);
        exit();
    }
}

function handleDeleteRequest($db, &$response)
{
    $data = json_decode(file_get_contents("php://input"), true);
    $id = intval($data['id']);

    // Initialize response
    $response = [
        'status' => 200,
        'message' => ''
    ];

    // Determine the base directory dynamically
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $baseDir = ($host === 'localhost') ? $_SERVER['DOCUMENT_ROOT'] . '/cl.englivia.com/uploads/pdf/' : $_SERVER['DOCUMENT_ROOT'] . '/uploads/pdf/';

    // First, fetch the current PDF file name from the database
    $db->select('tbl_categories', 'pdf', null, 'id = ' . $id . ' AND pdf IS NOT NULL');
    $result = $db->getResult();

    if (!empty($result) && isset($result[0]['pdf'])) {
        $pdfFileName = $result[0]['pdf'];
        $targetFile = $baseDir . $pdfFileName;

        // Delete the file from the server
        if (file_exists($targetFile)) {
            if (unlink($targetFile)) {
                // If the file is successfully deleted, update the database
                $query = "UPDATE tbl_categories SET pdf = NULL WHERE id = $id AND pdf IS NOT NULL";
                if ($db->sql($query)) {
                    $response['message'] = 'PDF deleted successfully from both server and database';
                } else {
                    $response['status'] = 500;
                    $response['message'] = 'Error updating the database after file deletion';
                }
            } else {
                $response['status'] = 500;
                $response['message'] = 'Error deleting the file from the server';
            }
        } else {
            $response['status'] = 404;
            $response['message'] = 'File not found on the server';
        }
    } else {
        $response['status'] = 404;
        $response['message'] = 'PDF not found in the database';
    }

    echo json_encode($response);
    exit();
}


function outputResponse($db, &$response, $message)
{
    $response['data'] = $db->getResult();
    $response['message'] = $message;
    http_response_code($response['status']);
}
?>
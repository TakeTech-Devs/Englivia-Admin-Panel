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

switch ($action) {
    case 'GET':
        handleGetRequest($db, $response);
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
        // echo json_encode($response);
        exit();
}

// echo json_encode($response);
$db->disconnect();

function handleGetRequest($db, &$response)
{
    $conditions = [];

    if (isset($_GET['id'])) {
        $conditions[] = 'id = ' . intval($_GET['id']);
    }
    if (isset($_GET['type'])) {
        $conditions[] = 'type = ' . intval($_GET['type']);
    }
    if (isset($_GET['keyword'])) {
        $keyword = $db->escapeString($_GET['keyword']);
        $conditions[] = 'category_name LIKE "%' . $keyword . '%"';
    }

    // New filter for 'category' field
    if (isset($_GET['category'])) {
        $conditions[] = 'category = ' . intval($_GET['category']);
    }

    $whereClause = !empty($conditions) ? implode(' AND ', $conditions) : null;

    if (isset($_GET['table'])) {
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
        $search = isset($_GET['search']) ? $db->escapeString($_GET['search']) : '';

        $offset = ($page - 1) * $limit;
        $totalQuery = "SELECT COUNT(*) AS total FROM tbl_subcategories WHERE category_name LIKE '%$search%'" . ($whereClause ? " AND $whereClause" : '');
        $db->sql($totalQuery);
        $totalResult = $db->getResult();
        $totalRecords = $totalResult[0]['total'];

        if ($totalRecords == 0) {
            // Return 206 Partial Content if no complete match but some partial data exists
            $data = ["message" => "No data available", "status" => 206];
            http_response_code(206);
            echo json_encode($data);
            exit();
        }

        $query = "SELECT * FROM tbl_subcategories WHERE category_name LIKE '%$search%'" . ($whereClause ? " AND $whereClause" : '') . " LIMIT $limit OFFSET $offset";
        $db->sql($query);
        $data = $db->getResult();

        foreach ($data as &$item) {
            if (isset($item['instructions'])) {
                $item['instructions'] = parseInstructions($item['instructions']);
            }
            $item['questions'] = getTotalQuestions($db, $item['id']);
            $item['total_duration'] = getTotalDuration($db, $item['id']);
        }

        $response = [
            'total' => $totalRecords,
            'page' => $page,
            'limit' => $limit,
            'data' => $data,
        ];
        sendResponse($response);
        return; // Ensure the response is sent immediately
    } else {
        $db->select('tbl_subcategories', '*', null, $whereClause);
    }
    $result = $db->getResult();

    if (!empty($result)) {
        foreach ($result as &$item) {
            if (isset($item['instructions'])) {
                $item['instructions'] = parseInstructions($item['instructions']);
            }
            $item['questions'] = getTotalQuestions($db, $item['id']);
            $item['total_duration'] = getTotalDuration($db, $item['id']);
        }
    } else {
        // Return 206 Partial Content if no complete match but some partial data exists
        $data = ["message" => "No data available", "status" => 206];
        http_response_code(206);
        echo json_encode($data);
        exit();
    }

    $response['status'] = 200;
    $response['data'] = $result;
    $response['message'] = 'Data fetched successfully';
    // http_response_code($response['status']);
    http_response_code(200);
    echo json_encode($response);
    exit();
}

// function handlePostRequest($db, &$response)
// {
//     $data = json_decode(file_get_contents("php://input"), true);
//     $params = [
//         'category_name' => $db->escapeString($data['category_name']),
//         'type' => intval($data['type']),
//         'category' => intval($data['category']),
//     ];

//     // Generate a custom ID
//     // Get the current day, hour, minute, and second
//     $currentDateTime = date('dmyHis');

//     // Combine the type with the current datetime components
//     $new_id = $data['type'] . '0' . $currentDateTime;

//     $params['id'] = intval($new_id); // Convert to integer if needed

//     if (isset($data['image'])) {
//         $params['image'] = $db->escapeString($data['image']);
//     }

//     if (isset($data['instructions'])) {
//         $params['instructions'] = $data['instructions'];
//     }

//     if (!empty($params['category_name'])) {
//         $db->insert('tbl_subcategories', $params);
//         outputResponse($db, $response, 'Category created successfully');
//     } else {
//         $response['status'] = 400;
//         $response['message'] = 'No valid data provided';
//         echo json_encode($response);
//         exit();
//     }
// }
function respond($response)
{
    // echo json_encode($response);
    exit();
}

function handlePostRequest($db, &$response)
{
    if (isset($_GET['upload_subcat_csv']) && $_GET['upload_subcat_csv'] == 1) {
        // CSV upload mode
        if (
            isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK &&
            (
                isset($_POST['narration_category_id']) ||
                isset($_POST['current_affairs_category_id'])
            )
        ) {
            // Determine which category type is being used
            if (isset($_POST['narration_category_id'])) {
                $categoryIdRaw = $_POST['narration_category_id'];
                $categoryTypeForAll = 9;
            } elseif (isset($_POST['current_affairs_category_id'])) {
                $categoryIdRaw = $_POST['current_affairs_category_id'];
                $categoryTypeForAll = 2;
            } else {
                echo json_encode(['status' => 400, 'message' => 'No valid category ID provided.']);
                exit();
            }

            if ($categoryIdRaw === 'all') {
                $db->sql("SELECT id FROM tbl_categories WHERE type = $categoryTypeForAll");
                $allCategories = $db->getResult();
                $categoryIds = array_column($allCategories, 'id');
            } else {
                $categoryIds = [intval($categoryIdRaw)];
            }

            $type = intval($_POST['category_type']);
            $filePath = $_FILES['csv_file']['tmp_name'];
            $handle = fopen($filePath, "r");

            if ($handle === false) {
                echo json_encode(['status' => 500, 'message' => 'Failed to open CSV file.']);
                exit();
            }

            $row = 0;
            $csvRows = [];
            $skipped = 0;

            // Step 1: Read all valid CSV rows
            while (($data = fgetcsv($handle)) !== false) {
                if ($row++ === 0) continue; // skip header
                if (empty(trim($data[0]))) {
                    $skipped++;
                    continue;
                }
                $csvRows[] = trim($data[0]);
            }

            fclose($handle);

            // Step 2: For each category, insert the entire CSV set
            $inserted = 0;
            foreach ($categoryIds as $categoryId) {
                foreach ($csvRows as $categoryName) {
                    $params = [
                        'category_name' => $db->escapeString($categoryName),
                        'category' => $categoryId,
                        'type' => $type,
                        'status' => 1
                    ];
                    // file_put_contents('params_log.txt', print_r($params, true), FILE_APPEND);
                    // file_put_contents('category_log.txt', "CSV Insert Category ID: " . $categoryId . "\n", FILE_APPEND);
                    $db->insert('tbl_subcategories', $params);
                    $inserted++;
                }
            }

            echo json_encode([
                'status' => 200,
                'message' => "$inserted subcategories inserted successfully.",
                'skipped' => $skipped
            ]);
        } else {
            echo json_encode(['status' => 400, 'message' => 'CSV upload failed or category not selected.']);
        }
    } else {
        // Manual insert mode
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['category_name']) || empty($data['category'])) {
            echo json_encode(['status' => 422, 'message' => 'Missing category name or parent category.']);
            return;
        }

        $params = [
            'category_name' => $db->escapeString(trim($data['category_name'])),
            'category' => intval(trim($data['category'])),
            'type' => intval($data['type']),
            'status' => 1
        ];

        // file_put_contents('params_log.txt', print_r($params, true), FILE_APPEND);
        // file_put_contents('category_log.txt', "CSV Insert Category ID: " . $params['category'] . "\n", FILE_APPEND);

        $db->insert('tbl_subcategories', $params);
        echo json_encode(['status' => 200, 'message' => 'Subcategory inserted successfully.']);
    }
}






function handlePutRequest($db, &$response)
{
    $data = json_decode(file_get_contents("php://input"), true);
    $id = intval($_GET['id']);
    $params = [];

    if (isset($data['category_name'])) {
        $params['category_name'] = $db->escapeString($data['category_name']);
    }
    if (isset($data['image'])) {
        $params['image'] = $db->escapeString($data['image']);
    }
    if (isset($data['instructions'])) {
        $params['instructions'] = $data['instructions'];
    }
    if (isset($data['status'])) {
        $params['status'] = intval($data['status']);
    }
    if (isset($data['type'])) {
        $params['type'] = intval($data['type']);
    }
    if (isset($data['category'])) {
        $params['category'] = intval($data['category']);
    }

    if (!empty($params)) {
        $db->update('tbl_subcategories', $params, 'id = ' . $id);
        outputResponse($db, $response, 'Category updated successfully');
    } else {
        $response['status'] = 400;
        $response['message'] = 'No valid data provided';
        // echo json_encode($response);
        exit();
    }
}


function handleDeleteRequest($db, &$response)
{
    $data = json_decode(file_get_contents("php://input"), true);
    if (!isset($data['id']) || empty($data['id'])) {
        respond(['error' => 'ID is required'], 400);
    }
    $id = intval($data['id']);

    // Delete all questions related to this category in a single query
    $db->delete('tbl_questions', 'category_id = ' . $id);

    $db->delete('tbl_subcategories', 'id = ' . $id);
    // outputResponse($db, $response, 'Category deleted successfully');

    header('Content-Type: application/json');
    http_response_code(200);
    echo json_encode([
        'status' => 200,
        'message' => 'Category deleted successfully',
    ]);
    exit;
}

function parseInstructions($instructions)
{
    $instructionsArray = explode('|', $instructions);
    $parsedInstructions = [];

    foreach ($instructionsArray as $index => $instruction) {
        $parsedInstructions[$index + 1] = trim($instruction);
    }

    return $parsedInstructions;
}

function getTotalDuration($db, $categoryId)
{
    $db->select('tbl_questions', 'SUM(duration) as total_duration', null, 'category_id = ' . $categoryId);
    $result = $db->getResult();
    return isset($result[0]['total_duration']) ? $result[0]['total_duration'] : 0;
}

function getTotalQuestions($db, $categoryId)
{
    $db->select('tbl_questions', 'COUNT(*) as total_questions', null, 'category_id = ' . $categoryId);
    $result = $db->getResult();
    return isset($result[0]['total_questions']) ? $result[0]['total_questions'] : 0;
}

function outputResponse($db, &$response, $message)
{
    $response['data'] = $db->getResult();
    $response['message'] = $message;
    http_response_code($response['status']);
}

function sendResponse($response, $code = 200)
{
    $data = ["response" => $response, "status" => $code];
    http_response_code($code);
    echo json_encode($data);
    exit();
}

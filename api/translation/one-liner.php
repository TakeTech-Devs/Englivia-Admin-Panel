<?php
header("Content-Type: application/json");
require_once '../../library/crud.php';

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
    $conditions = [];

    if (isset($_GET['id'])) {
        $conditions[] = 'id = ' . intval($_GET['id']);
    }

    // Adding type = 4 condition
    $conditions[] = 'type = 4';

    if (isset($_GET['language'])) {
        $conditions[] = 'language = ' . $db->escapeString($_GET['language']);
    }

    // Adding Tag = 'oneliner' condition
    $conditions[] = "tag = 'oneliner'";

    if (isset($_GET['keyword'])) {
        $keyword = $db->escapeString($_GET['keyword']);
        $conditions[] = 'category_name LIKE "%' . $keyword . '%"';
    }

    $whereClause = !empty($conditions) ? implode(' AND ', $conditions) : '1'; // '1' ensures a valid WHERE clause

    if (isset($_GET['table'])) {
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
        $search = isset($_GET['search']) ? $db->escapeString($_GET['search']) : '';

        $offset = ($page - 1) * $limit;
        $totalQuery = "SELECT COUNT(*) AS total FROM tbl_categories WHERE category_name LIKE '%$search%' AND $whereClause";
        $db->sql($totalQuery);
        $totalResult = $db->getResult();
        $totalRecords = $totalResult[0]['total'];

        if ($totalRecords == 0) {
            // Return 206 Partial Content if no complete match but there might be some partial data
            $response['status'] = 206;
            $response['message'] = 'Partial data available';
            sendResponse($response, 206); // Send response and exit
            return;
        }

        $query = "SELECT * FROM tbl_categories WHERE category_name LIKE '%$search%' AND $whereClause LIMIT $limit OFFSET $offset";
        $db->sql($query);
        $data = $db->getResult();

        foreach ($data as &$item) {
            if (isset($item['instructions'])) {
                $item['instructions'] = parseInstructions($item['instructions']);
            }
            $item['questions'] = getTotalQuestions($db, $item['id']);
            $item['total_duration'] = getTotalDuration($db, $item['id']);
            if (isset($item['pdf'])) {
                $item['pdf'] = $baseURL . $item['pdf'];
            }
        }

        $response = [
            'total' => $totalRecords,
            'page' => $page,
            'limit' => $limit,
            'data' => $data,
        ];

        sendResponse($response); // Send success response if data found
        return;
    } else {
        $db->select('tbl_categories', '*', null, $whereClause);
    }

    $result = $db->getResult();

    if (empty($result)) {
        // Return 206 Partial Content if no full data match but partial results
        $response['status'] = 206;
        $response['message'] = 'No data available';
        sendResponse($response, 206); // Send response and exit
        return;
    }

    foreach ($result as &$item) {
        if (isset($item['instructions'])) {
            $item['instructions'] = parseInstructions($item['instructions']);
        }
        $item['questions'] = getTotalQuestions($db, $item['id']);
        $item['total_duration'] = getTotalDuration($db, $item['id']);
        if (isset($item['pdf'])) {
            $item['pdf'] = $baseURL . $item['pdf'];
        }
    }

    $response['data'] = $result;
    $response['message'] = 'Data fetched successfully';
    http_response_code(200); // Ensure success status code
    sendResponse($response); // Send the response
}

function handlePostRequest($db, &$response)
{
    $data = json_decode(file_get_contents("php://input"), true);

    // Generate a custom ID based on the type and current datetime
    $currentDateTime = date('dmyHis'); // Current day, hour, minute, second
    $new_id = $data['type'] . '0' . $currentDateTime;

    $params = [
        'id' => intval($new_id), // Set the custom ID
        'category_name' => $db->escapeString($data['category_name']),
        'type' => intval($data['type']),
        'tag' => $db->escapeString($data['tag']),
        'language' => intval($data['language']),
    ];

    if (isset($data['image'])) {
        $params['image'] = $db->escapeString($data['image']);
    }

    if (isset($data['instructions'])) {
        $params['instructions'] = $data['instructions'];
    }

    if (!empty($params['category_name'])) {
        $db->insert('tbl_categories', $params);
        outputResponse($db, $response, 'Category created successfully');
    } else {
        $response['status'] = 400;
        $response['message'] = 'No valid data provided';
        echo json_encode($response);
        exit();
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
    if (isset($data['language'])) {
        $params['language'] = $db->escapeString($data['language']);
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

    if (!empty($params)) {
        $db->update('tbl_categories', $params, 'id = ' . $id);
        outputResponse($db, $response, 'Category updated successfully');
    } else {
        $response['status'] = 400;
        $response['message'] = 'No valid data provided';
        echo json_encode($response);
        exit();
    }
}


function handleDeleteRequest($db, &$response)
{
    $data = json_decode(file_get_contents("php://input"), true);
    if (!isset($data['id']) || empty($data['id'])) {
        $response['status'] = 400;
        $response['message'] = 'ID is required';
        echo json_encode($response);
        exit();
    }
    $id = intval($data['id']);
    $db->delete('tbl_categories', 'id = ' . $id);
    outputResponse($db, $response, 'Category deleted successfully');
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
?>
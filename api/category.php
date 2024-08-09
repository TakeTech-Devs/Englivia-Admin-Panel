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
        echo json_encode($response);
        exit();
}

echo json_encode($response);
$db->disconnect();

function handleGetRequest($db, &$response) {
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $db->select('ssc_category', '*', null, 'id = ' . $id);
    } elseif (isset($_GET['keyword'])) {
        $keyword = $db->escapeString($_GET['keyword']);
        $db->select('ssc_category', '*', null, 'category_name LIKE "%' . $keyword . '%"');
    } elseif (isset($_GET['table'])) {
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
        $search = isset($_GET['search']) ? $db->escapeString($_GET['search']) : '';
        
        $offset = ($page - 1) * $limit;
        $totalQuery = "SELECT COUNT(*) AS total FROM ssc_category WHERE category_name LIKE '%$search%'";
        $db->sql($totalQuery);
        $totalResult = $db->getResult();
        $totalRecords = $totalResult[0]['total'];

        $query = "SELECT * FROM ssc_category WHERE category_name LIKE '%$search%' LIMIT $limit OFFSET $offset";
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
        $db->select('ssc_category');
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
    }

    $response['data'] = $result;
    $response['message'] = 'Data fetched successfully';
    http_response_code($response['status']);
}

function handlePostRequest($db, &$response) {
    $data = json_decode(file_get_contents("php://input"), true);
    $params = [
        'category_name' => $db->escapeString($data['category_name']),
    ];

    if (isset($data['image'])) {
        $params['image'] = $db->escapeString($data['image']);
    }

    if (isset($data['instructions'])) {
        $params['instructions'] = $data['instructions'];
    }

    if (!empty($params['category_name'])) {
        $db->insert('ssc_category', $params);
        outputResponse($db, $response, 'Category created successfully');
    } else {
        $response['status'] = 400;
        $response['message'] = 'No valid data provided';
        echo json_encode($response);
        exit();
    }
}

function handlePutRequest($db, &$response) {
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
        $params['status'] = $data['status'];
    }

    if (!empty($params)) {
        $db->update('ssc_category', $params, 'id = ' . $id);
        outputResponse($db, $response, 'Category updated successfully');
    } else {
        $response['status'] = 400;
        $response['message'] = 'No valid data provided';
        echo json_encode($response);
        exit();
    }
}

function handleDeleteRequest($db, &$response) {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!isset($data['id']) || empty($data['id'])) {
    respond(['error' => 'ID is required'], 400);
    }
    $id = intval($data['id']);
    $db->delete('ssc_category', 'id = ' . $id);
    outputResponse($db, $response, 'Category deleted successfully');
}

function parseInstructions($instructions) {
    $instructionsArray = explode('|', $instructions);
    $parsedInstructions = [];

    foreach ($instructionsArray as $index => $instruction) {
        $parsedInstructions[$index + 1] = trim($instruction);
    }

    return $parsedInstructions;
}

function getTotalDuration($db, $categoryId) {
    $db->select('ssc_question', 'SUM(duration) as total_duration', null, 'ssc_category = ' . $categoryId);
    $result = $db->getResult();
    return isset($result[0]['total_duration']) ? $result[0]['total_duration'] : 0;
}

function getTotalQuestions($db, $categoryId){
    $db->select('ssc_question', 'COUNT(*) as total_questions', null, 'ssc_category = ' . $categoryId);
    $result = $db->getResult();
    return isset($result[0]['total_questions']) ? $result[0]['total_questions'] : 0;
}

function outputResponse($db, &$response, $message) {
    $response['data'] = $db->getResult();
    $response['message'] = $message;
    http_response_code($response['status']);
}

function sendResponse($response,$code=200) {
    $data = ["response" => $response, "status" => $code];
    http_response_code($code);
    echo json_encode($data);
    exit();
}
?>
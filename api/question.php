<?php
header("Content-Type: application/json");
require_once '../library/crud.php';

$db = new Database();
$db->connect();

$action = $_SERVER['REQUEST_METHOD'];

function respond($data, $code = 200) {
    $data = ["response" => $data, "status" => $code];
    http_response_code($code);
    echo json_encode($data);
    exit();
}

function getParamsFromBody() {
    return json_decode(file_get_contents("php://input"), true);
}

function validateParams($data, $requiredFields) {
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            respond(['error' => "Missing required field: $field"], 400);
        }
    }
}

switch ($action) {
    case 'GET':
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $db->select('ssc_question', '*', null, 'id = ' . $id);
            respond($db->getResult());
        } elseif (isset($_GET['category_id'])) {
            $category_id = intval($_GET['category_id']);
            $db->select('ssc_question', '*', null, 'ssc_category = ' . $category_id);
            respond($db->getResult());
        } elseif (isset($_GET['category']) && !isset($_GET['table'])) {
            $category = $db->escapeString($_GET['category']);
            $db->select('ssc_question', '*', null, 'ssc_category = "' . $category . '"');
            respond($db->getResult());
        } elseif (isset($_GET['table'])) {
            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
            $search = isset($_GET['search']) ? $db->escapeString($_GET['search']) : '';
            $category = isset($_GET['category']) ? $db->escapeString($_GET['category']) : '';

            $offset = ($page - 1) * $limit;
            $totalQuery = "SELECT COUNT(*) AS total FROM ssc_question WHERE `ssc_category` Like '%$category%' AND question LIKE '%$search%'";
            $db->sql($totalQuery);
            $totalResult = $db->getResult();
            $totalRecords = $totalResult[0]['total'];

            $query = "SELECT * FROM ssc_question WHERE `ssc_category` Like '%$category%' AND question LIKE '%$search%'  LIMIT $limit OFFSET $offset";
            $db->sql($query);
            $data = $db->getResult();

            $response = [
                'total' => $totalRecords,
                'page' => $page,
                'limit' => $limit,
                'data' => $data,
            ];
            respond($response);
        } else {
            $db->select('ssc_question');
            respond($db->getResult());
        }
        break;

    case 'POST':
        $data = getParamsFromBody();
        $requiredFields = ['ssc_category', 'question', 'optiona', 'optionb', 'optionc', 'optiond', 'answer', 'duration'];
        validateParams($data, $requiredFields);

        $params = [
            'ssc_category' => $db->escapeString($data['ssc_category']),
            'question' => $db->escapeString($data['question']),
            'optiona' => $db->escapeString($data['optiona']),
            'optionb' => $db->escapeString($data['optionb']),
            'optionc' => $db->escapeString($data['optionc']),
            'optiond' => $db->escapeString($data['optiond']),
            'answer' => $db->escapeString($data['answer']),
            'duration' => intval($data['duration']) * 60000  // Convert minutes to milliseconds
        ];

        if (isset($data['optione'])) $params['optione'] = $db->escapeString($data['optione']);
        if (isset($data['image'])) $params['image'] = $db->escapeString($data['image']);
        if (isset($data['note'])) $params['note'] = $db->escapeString($data['note']);

        $db->insert('ssc_question', $params);
        respond(['status' => 200, 'message' => 'Question created successfully']);
        break;

    case 'PUT':
        $id = intval($_GET['id']);
        $data = getParamsFromBody();

        $params = [];
        foreach (['ssc_category', 'question', 'optiona', 'optionb', 'optionc', 'optiond', 'optione', 'answer', 'note', 'image', 'duration'] as $field) {
            if (isset($data[$field])) {
                $params[$field] = $db->escapeString($data[$field]);
                if ($field == 'duration') {
                    $params[$field] = intval($data[$field]) * 60000;  // Convert minutes to milliseconds
                }
            }
        }

        if (!empty($params)) {
            $db->update('ssc_question', $params, 'id = ' . $id);
            respond(['message' => 'Question updated successfully']);
        } else {
            respond(['error' => 'No valid data provided'], 400);
        }
        break;

    case 'DELETE':
        $data = getParamsFromBody();
        if (!isset($data['id']) || empty($data['id'])) {
            respond(['error' => 'ID is required'], 400);
        }
        $id = intval($data['id']);
        $db->delete('ssc_question', 'id = ' . $id);
        if ($db->getResult()) {
            respond(['message' => 'Question deleted successfully']);
        } else {
            respond(['error' => 'Failed to delete question'], 500);
        }
        break;

    default:
        respond(['error' => 'Method Not Allowed'], 405);
}

$db->disconnect();
?>

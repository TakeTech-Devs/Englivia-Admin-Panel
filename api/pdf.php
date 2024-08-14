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
$basePath = ($host === 'localhost') ? '/cl.englivia.com/uploads/currentaffairs/' : '/uploads/currentaffairs/';
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
    $type = isset($_GET['type']) ? intval($_GET['type']) : null;
    $whereClause = 'pdf IS NOT NULL';

    if ($type !== null) {
        $whereClause .= ' AND type = ' . $type;
    }

    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $db->select('tbl_categories', 'id, pdf, type', null, 'id = ' . $id . ' AND ' . $whereClause);
        $result = $db->getResult();
        if ($result) {
            $result[0]['pdf'] = $baseURL . $result[0]['pdf'];
            $response['data'] = $result;
            $response['message'] = 'PDF fetched successfully';
        } else {
            $response['status'] = 404;
            $response['message'] = 'PDF not found';
        }
    } elseif (isset($_GET['keyword'])) {
        $keyword = $db->escapeString($_GET['keyword']);
        $db->select('tbl_categories', 'id, pdf, type', null, 'pdf LIKE "%' . $keyword . '%" AND ' . $whereClause);
        $data = $db->getResult();
        foreach ($data as &$item) {
            $item['pdf'] = $baseURL . $item['pdf'];
        }
        $response['data'] = $data;
        $response['message'] = 'PDFs fetched successfully';
    } elseif (isset($_GET['table'])) {
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
        $search = isset($_GET['search']) ? $db->escapeString($_GET['search']) : '';

        $offset = ($page - 1) * $limit;
        $totalQuery = "SELECT COUNT(*) AS total FROM tbl_categories WHERE $whereClause AND pdf LIKE '%$search%'";
        $db->sql($totalQuery);
        $totalResult = $db->getResult();
        $totalRecords = $totalResult[0]['total'];

        $query = "SELECT id, pdf, type FROM tbl_categories WHERE $whereClause AND pdf LIKE '%$search%' LIMIT $limit OFFSET $offset";
        $db->sql($query);
        $data = $db->getResult();
        foreach ($data as &$item) {
            $item['pdf'] = $baseURL . $item['pdf'];
        }

        $response = [
            'total' => $totalRecords,
            'page' => $page,
            'limit' => $limit,
            'data' => $data,
        ];
    } else {
        $db->select('tbl_categories', 'id, pdf, type', null, $whereClause);
        $data = $db->getResult();
        foreach ($data as &$item) {
            $item['pdf'] = $baseURL . $item['pdf'];
        }
        $response['data'] = $data;
        $response['message'] = 'PDFs fetched successfully';
    }
}

function handlePostRequest($db, &$response)
{
    $data = json_decode(file_get_contents("php://input"), true);
    $params = [
        'pdf' => $db->escapeString($data['pdf']),
        'type' => isset($data['type']) ? intval($data['type']) : null,
    ];

    if (!empty($params['pdf']) && $params['type'] !== null) {
        $db->insert('tbl_categories', $params);
        outputResponse($db, $response, 'PDF added successfully');
    } else {
        $response['status'] = 400;
        $response['message'] = 'No valid PDF data provided';
        echo json_encode($response);
        exit();
    }
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
    $id = intval($_GET['id']);
    $db->update('tbl_categories', ['pdf' => NULL], 'id = ' . $id . ' AND pdf IS NOT NULL');
    $response['message'] = 'PDF deleted successfully';
}

function outputResponse($db, &$response, $message)
{
    $response['data'] = $db->getResult();
    $response['message'] = $message;
    http_response_code($response['status']);
}
?>

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

// function respond($data, $code = 200)
function respond($data, $code = 200)
{
    if (empty($data)) {
        $data = ["response" => $data, "message" => "No data available", "status" => 206];
        http_response_code(206);
    } else {
        $data = ["response" => $data, "message" => "Data fetched successfully", "status" => $code];
        http_response_code($code);
    }
    echo json_encode($data);
    exit();
}

function getParamsFromBody()
{
    return json_decode(file_get_contents("php://input"), true);
}

function validateParams($data, $requiredFields)
{
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            respond(['error' => "Missing required field: $field"], 400);
        }
    }
}

function outputResponse($db, &$response, $message)
{
    $response['data'] = $db->getResult();
    $response['message'] = $message;
    http_response_code($response['status']);
}

switch ($action) {
    case 'GET':
        $type = isset($_GET['type']) ? intval($_GET['type']) : 1;
        $isCombinedType = in_array($type, [2, 6, 7, 8, 9]);

        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);

            if ($isCombinedType) {
                $sql = "
                    SELECT q.*, c.type, c.category_name
                    FROM tbl_questions q
                    JOIN tbl_subcategories c ON q.category_id = c.id
                    WHERE q.id = $id AND c.type = $type
    
                    UNION
    
                    SELECT q.*, c.type, c.category_name
                    FROM tbl_questions q
                    JOIN tbl_categories c ON q.category_id = c.id
                    WHERE q.id = $id AND c.type = $type
                ";
            } else {
                $sql = "
                    SELECT q.*, c.type, c.category_name
                    FROM tbl_questions q
                    JOIN tbl_categories c ON q.category_id = c.id
                    WHERE q.id = $id AND c.type = $type
                ";
            }

            $db->sql($sql);
            respond($db->getResult());
        } elseif (isset($_GET['category']) && !isset($_GET['table'])) {
            $category_id = intval($_GET['category']);

            if ($isCombinedType) {
                $sql = "
                    SELECT q.*, c.type, c.category_name
                    FROM tbl_questions q
                    JOIN tbl_subcategories c ON q.category_id = c.id
                    WHERE q.category_id = $category_id AND c.type = $type
    
                    UNION
    
                    SELECT q.*, c.type, c.category_name
                    FROM tbl_questions q
                    JOIN tbl_categories c ON q.category_id = c.id
                    WHERE q.category_id = $category_id AND c.type = $type
                ";
            } else {
                $sql = "
                    SELECT q.*, c.type, c.category_name
                    FROM tbl_questions q
                    JOIN tbl_categories c ON q.category_id = c.id
                    WHERE q.category_id = $category_id AND c.type = $type
                ";
            }

            $db->sql($sql);
            respond($db->getResult());
        } elseif (isset($_GET['table'])) {
            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
            $search = isset($_GET['search']) ? $db->escapeString($_GET['search']) : '';
            $category = isset($_GET['category']) ? intval($_GET['category']) : '';

            $offset = ($page - 1) * $limit;

            if ($isCombinedType) {
                // Total count
                $totalQuery = "
                    SELECT COUNT(*) AS total FROM (
                        SELECT q.id
                        FROM tbl_questions q
                        JOIN tbl_subcategories c ON q.category_id = c.id
                        WHERE (q.question LIKE '%$search%' OR q.optiona LIKE '%$search%' OR q.optionb LIKE '%$search%' OR q.optionc LIKE '%$search%' OR q.optiond LIKE '%$search%')
                        AND c.id LIKE '%$category%' AND c.type = $type
    
                        UNION
    
                        SELECT q.id
                        FROM tbl_questions q
                        JOIN tbl_categories c ON q.category_id = c.id
                        WHERE (q.question LIKE '%$search%' OR q.optiona LIKE '%$search%' OR q.optionb LIKE '%$search%' OR q.optionc LIKE '%$search%' OR q.optiond LIKE '%$search%')
                        AND c.id LIKE '%$category%' AND c.type = $type
                    ) AS combined
                ";

                $db->sql($totalQuery);
                $totalResult = $db->getResult();
                $totalRecords = $totalResult[0]['total'];

                if ($totalRecords == 0) {
                    http_response_code(206);
                    echo json_encode(["message" => "No data available", "status" => 206]);
                    return;
                }

                // Paginated query
                $query = "
                    SELECT * FROM (
                        SELECT q.*, c.type, c.category_name
                        FROM tbl_questions q
                        JOIN tbl_subcategories c ON q.category_id = c.id
                        WHERE (q.question LIKE '%$search%' OR q.optiona LIKE '%$search%' OR q.optionb LIKE '%$search%' OR q.optionc LIKE '%$search%' OR q.optiond LIKE '%$search%')
                        AND c.id LIKE '%$category%' AND c.type = $type
    
                        UNION
    
                        SELECT q.*, c.type, c.category_name
                        FROM tbl_questions q
                        JOIN tbl_categories c ON q.category_id = c.id
                        WHERE (q.question LIKE '%$search%' OR q.optiona LIKE '%$search%' OR q.optionb LIKE '%$search%' OR q.optionc LIKE '%$search%' OR q.optiond LIKE '%$search%')
                        AND c.id LIKE '%$category%' AND c.type = $type
                    ) AS combined
                    LIMIT $limit OFFSET $offset
                ";

                $db->sql($query);
                $data = $db->getResult();

                respond([
                    'total' => $totalRecords,
                    'page' => $page,
                    'limit' => $limit,
                    'data' => $data,
                ]);
            } else {
                // Only categories table used
                $totalQuery = "
                    SELECT COUNT(*) AS total
                    FROM tbl_questions q
                    JOIN tbl_categories c ON q.category_id = c.id
                    WHERE (q.question LIKE '%$search%' OR q.optiona LIKE '%$search%' OR q.optionb LIKE '%$search%' OR q.optionc LIKE '%$search%' OR q.optiond LIKE '%$search%')
                    AND c.id LIKE '%$category%' AND c.type = $type
                ";
                $db->sql($totalQuery);
                $totalResult = $db->getResult();
                $totalRecords = $totalResult[0]['total'];

                if ($totalRecords == 0) {
                    http_response_code(206);
                    echo json_encode(["message" => "No data available", "status" => 206]);
                    return;
                }

                $query = "
                    SELECT q.*, c.type, c.category_name
                    FROM tbl_questions q
                    JOIN tbl_categories c ON q.category_id = c.id
                    WHERE (q.question LIKE '%$search%' OR q.optiona LIKE '%$search%' OR q.optionb LIKE '%$search%' OR q.optionc LIKE '%$search%' OR q.optiond LIKE '%$search%')
                    AND c.id LIKE '%$category%' AND c.type = $type
                    LIMIT $limit OFFSET $offset
                ";
                $db->sql($query);
                $data = $db->getResult();

                respond([
                    'total' => $totalRecords,
                    'page' => $page,
                    'limit' => $limit,
                    'data' => $data,
                ]);
            }
        } else {
            if ($isCombinedType) {
                $sql = "
                    SELECT q.*, c.type, c.category_name
                    FROM tbl_questions q
                    JOIN tbl_subcategories c ON q.category_id = c.id
                    WHERE c.type = $type
    
                    UNION
    
                    SELECT q.*, c.type, c.category_name
                    FROM tbl_questions q
                    JOIN tbl_categories c ON q.category_id = c.id
                    WHERE c.type = $type
                ";
            } else {
                $sql = "
                    SELECT q.*, c.type, c.category_name
                    FROM tbl_questions q
                    JOIN tbl_categories c ON q.category_id = c.id
                    WHERE c.type = $type
                ";
            }

            $db->sql($sql);
            respond($db->getResult());
        }
        break;





    // case 'POST':
    // $data = getParamsFromBody();
    // // print_r($data);
    // // exit();

    // $requiredFields = ['category_id', 'question', 'optiona', 'optionb', 'optionc', 'optiond', 'answer', 'duration'];
    // validateParams($data, $requiredFields);

    // // Generate a custom ID based on the current timestamp
    // $currentDateTime = date('dmyHis'); // Day, Month, Year, Hour, Minute, Second
    // $custom_id = intval($currentDateTime);

    // $params = [
    //     'id' => $custom_id, // Custom ID
    //     'category_id' => $db->escapeString($data['category_id']),
    //     'question' => $db->escapeString($data['question']),
    //     'optiona' => $db->escapeString($data['optiona']),
    //     'optionb' => $db->escapeString($data['optionb']),
    //     'optionc' => $db->escapeString($data['optionc']),
    //     'optiond' => $db->escapeString($data['optiond']),
    //     'answer' => $db->escapeString($data['answer']),
    //     'duration' => intval($data['duration']) * 60000  // Convert minutes to milliseconds
    // ];

    // if (isset($data['optione']))
    //     $params['optione'] = $db->escapeString($data['optione']);
    // if (isset($data['image']))
    //     $params['image'] = $db->escapeString($data['image']);
    // if (isset($data['note']))
    //     $params['note'] = $db->escapeString($data['note']);


    // $db->insert('tbl_questions', $params);
    // respond(['status' => 200, 'message' => 'Question created successfully']);
    // break;

    case 'POST':
        handlePostRequest($db, $response);
        break;

    case 'PUT':
        $id = intval($_GET['id']);
        $data = getParamsFromBody();

        $params = [];
        foreach (['category_id', 'question', 'optiona', 'optionb', 'optionc', 'optiond', 'optione', 'answer', 'note', 'image', 'duration'] as $field) {
            if (isset($data[$field]) && $data[$field] !== '') {
                $params[$field] = $db->escapeString($data[$field]);
                if ($field == 'duration') {
                    $params[$field] = intval($data[$field]) * 60000;
                }
            }
        }



        if (!empty($params)) {
            $db->update('tbl_questions', $params, 'id = ' . $id);
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
        $db->delete('tbl_questions', 'id = ' . $id);
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



// function handlePostRequest($db, &$response)
// {
//     // file_put_contents('entry_log.txt', "handlePostRequest called\n", FILE_APPEND);

//     if (isset($_GET['upload_csv']) && $_GET['upload_csv'] == 1) {
//         // file_put_contents('entry_log.txt', "handlePostRequest called for csv\n", FILE_APPEND);
//         $type = isset($_POST['type']) ? intval($_POST['type']) : 6;

//         // Fetch all categories with their language
//         // $sql = "SELECT id, category_name, Tag, language FROM tbl_categories WHERE type = $type ORDER BY id DESC";
//         if ($type === 9) {
//             // Join subcategories with categories to get Tag
//             $sql = "SELECT s.id, s.category_name, s.type, s.category AS parent_category_id, c.Tag, c.language FROM tbl_subcategories s JOIN tbl_categories c ON s.category = c.id WHERE s.type = $type ORDER BY s.id ASC ";
//         } else {
//             $sql = "SELECT id, category_name, Tag, language FROM tbl_categories WHERE type = $type ORDER BY id ASC
//     ";
//         }


//         $db->sql($sql);
//         $categoryRows = $db->getResult();

//         $categoryMap = [];
//         // foreach ($categoryRows as $rowCat) {
//         //     $lang = $rowCat['language'];
//         //     $categoryMap[$lang][] = $rowCat['id'];
//         // }

//         foreach ($categoryRows as $rowCat) {
//             $lang = $rowCat['language']; // Consistent field now
//             $categoryMap[$lang][] = $rowCat['id'];
//         }

//         if (empty($categoryMap)) {
//             respond(['status' => 400, 'message' => 'No categories found for the selected type.']);
//             exit();
//         }

//         if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
//             $filePath = $_FILES['csv_file']['tmp_name'];
//             $handle = fopen($filePath, "r");

//             if ($handle === false) {
//                 respond(['status' => 500, 'message' => 'Failed to open CSV file.']);
//                 exit();
//             }

//             $row = 0;
//             $questions = [];

//             while (($data = fgetcsv($handle)) !== false) {
//                 if ($row++ === 0) continue; // skip header
//                 if (count($data) < 8) continue;
//                 $questions[] = $data;
//             }

//             fclose($handle);

//             // Log questions for debugging
//             foreach ($questions as $question) {
//                 $q = $question[0] ?? '';
//                 // file_put_contents('question_log.txt', "Question: " . $q . "\n", FILE_APPEND);
//             }

//             $inserted = 0;


//             $selectedLang = $_POST['language'] ?? null;

//             if (!$selectedLang) {
//                 respond(['status' => 400, 'message' => 'Language is required.']);
//                 exit();
//             }

//             $questionsByLang = [];

//             if ($selectedLang === 'all') {
//                 // Assign same questions to all languages in the category map
//                 foreach ($categoryMap as $langId => $categoryIds) {
//                     $questionsByLang[$langId] = $questions;
//                 }
//             } else {
//                 if (!isset($categoryMap[$selectedLang])) {
//                     respond(['status' => 400, 'message' => 'Invalid language selected.']);
//                     exit();
//                 }
//                 $questionsByLang[$selectedLang] = $questions;
//             }


//             foreach ($questionsByLang as $lang => $questionsSet) {
//                 if (!isset($categoryMap[$lang])) continue;

//                 $categories = $categoryMap[$lang];
//                 $categoryCount = count($categories);
//                 $questionIndex = 0;

//                 foreach ($categories as $catId) {
//                     for ($i = 0; $i < 10 && $questionIndex < count($questionsSet); $i++, $questionIndex++) {
//                         $data = $questionsSet[$questionIndex];
//                         $custom_id = intval(date('dmyHis') . str_pad($inserted + 1, 2, '0', STR_PAD_LEFT));

//                         $params = [
//                             'id' => $custom_id,
//                             'category_id' => $catId,
//                             'question' => $db->escapeString($data[0] ?? ''),
//                             'optiona' => $db->escapeString($data[1] ?? ''),
//                             'optionb' => $db->escapeString($data[2] ?? ''),
//                             'optionc' => $db->escapeString($data[3] ?? ''),
//                             'optiond' => $db->escapeString($data[4] ?? ''),
//                             'answer' => strtolower($db->escapeString($data[5] ?? '')),
//                             'duration' => intval($data[6]) * 60000,
//                             'note' => $db->escapeString($data[7] ?? '')
//                         ];

//                         if (isset($data[9])) $params['optione'] = $db->escapeString($data[9]);
//                         if (isset($data[10])) $params['image'] = $db->escapeString($data[10]);

//                         file_put_contents('params_log.txt', print_r($params, true), FILE_APPEND);
//                         file_put_contents('category_log.txt', "CSV Insert Category ID: " . $params['category_id'] . "\n", FILE_APPEND);
//                         // $db->insert('tbl_questions', $params);
//                         $inserted++;
//                     }
//                 }
//             }

//             respond(['status' => 200, 'message' => "$inserted questions inserted successfully."]);
//         } else {
//             respond(['status' => 400, 'message' => 'CSV file upload failed.']);
//         }
//     } else {
//         // file_put_contents('entry_log.txt', "handlePostRequest for manual called\n", FILE_APPEND);
//         $data = json_decode(file_get_contents("php://input"), true);
//         $custom_id = intval(date('dmyHis'));

//         $params = [
//             'id' => $custom_id,
//             'category_id' => $db->escapeString($data['category_id']),
//             'question' => $db->escapeString($data['question'] ?? ''),
//             'optiona' => $db->escapeString($data['optiona']),
//             'optionb' => $db->escapeString($data['optionb']),
//             'optionc' => $db->escapeString($data['optionc']),
//             'optiond' => $db->escapeString($data['optiond']),
//             'answer' => $db->escapeString($data['answer']),
//             'duration' => intval($data['duration']) * 60000,
//             'note' => $db->escapeString($data['note'] ?? '')
//         ];

//         if (isset($data['optione'])) $params['optione'] = $db->escapeString($data['optione']);
//         if (isset($data['image'])) $params['image'] = $db->escapeString($data['image']);

//         // file_put_contents('params_log.txt', print_r($params, true), FILE_APPEND);
//         // file_put_contents('category_log.txt', "Manual Insert Category ID: " . $params['category_id'] . "\n", FILE_APPEND);

//         $db->insert('tbl_questions', $params);

//         respond(['status' => 200, 'message' => 'Question created successfully']);
//     }
// }


function handlePostRequest($db, &$response)
{
    if (isset($_GET['upload_csv']) && $_GET['upload_csv'] == 1) {
        $type = isset($_POST['type']) ? intval($_POST['type']) : 6;

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            respond(['status' => 400, 'message' => 'CSV file upload failed.']);
            return;
        }

        $filePath = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($filePath, "r");

        if ($handle === false) {
            respond(['status' => 500, 'message' => 'Failed to open CSV file.']);
            return;
        }

        $row = 0;
        $questions = [];

        while (($data = fgetcsv($handle)) !== false) {
            if ($row++ === 0) continue; // Skip header
            if (count($data) < 8) continue;
            $questions[] = $data;
        }

        fclose($handle);

        $inserted = 0;

        // if ($type === 9 && isset($_POST['category_id'])) {
        //     $catId = intval($_POST['category_id']);

        //     foreach ($questions as $data) {
        //         $custom_id = intval(date('dmyHis') . str_pad($inserted + 1, 2, '0', STR_PAD_LEFT));

        //         $params = [
        //             'id' => $custom_id,
        //             'category_id' => $catId,
        //             'question' => $db->escapeString($data[0] ?? ''),
        //             'optiona' => $db->escapeString($data[1] ?? ''),
        //             'optionb' => $db->escapeString($data[2] ?? ''),
        //             'optionc' => $db->escapeString($data[3] ?? ''),
        //             'optiond' => $db->escapeString($data[4] ?? ''),
        //             'answer' => strtolower($db->escapeString($data[5] ?? '')),
        //             'duration' => intval($data[6]) * 60000,
        //             'note' => $db->escapeString($data[7] ?? '')
        //         ];

        //         if (isset($data[9])) $params['optione'] = $db->escapeString($data[9]);
        //         if (isset($data[10])) $params['image'] = $db->escapeString($data[10]);

        //         file_put_contents('params_log.txt', print_r($params, true), FILE_APPEND);
        //         // file_put_contents('category_log.txt', "CSV Insert Category ID: " . $params['category_id'] . "\n", FILE_APPEND);
        //         // $db->insert('tbl_questions', $params);
        //         $inserted++;
        //     }

        //     respond(['status' => 200, 'message' => "$inserted questions inserted into selected category."]);
        //     return;
        // }

        if (in_array($type, [2, 9]) && isset($_POST['category_id']) && isset($_POST['language'])) {
            $mainCatId = intval($_POST['category_id']);
            $languageId = intval($_POST['language']);

            // Get subcategories under the selected category
            $sql = "SELECT id FROM tbl_subcategories WHERE category = $mainCatId AND type = $type  ORDER BY id ASC";
            $db->sql($sql);
            $subcategories = $db->getResult();

            if (empty($subcategories)) {
                respond(['status' => 400, 'message' => 'No subcategories found for selected category.']);
                return;
            }

            $subcatIds = array_column($subcategories, 'id');
            $inserted = 0;
            $questionIndex = 0;
            $totalQuestions = count($questions);
            $questionsPerSet = 10; // You can change this if needed

            foreach ($subcatIds as $subcatId) {
                for ($i = 0; $i < $questionsPerSet && $questionIndex < $totalQuestions; $i++, $questionIndex++) {
                    $data = $questions[$questionIndex];
                    $custom_id = intval(date('dmyHis') . str_pad($inserted + 1, 2, '0', STR_PAD_LEFT));

                    $params = [
                        'id' => $custom_id,
                        'category_id' => $subcatId,
                        'question' => $db->escapeString($data[0] ?? ''),
                        'optiona' => $db->escapeString($data[1] ?? ''),
                        'optionb' => $db->escapeString($data[2] ?? ''),
                        'optionc' => $db->escapeString($data[3] ?? ''),
                        'optiond' => $db->escapeString($data[4] ?? ''),
                        'answer' => strtolower($db->escapeString($data[5] ?? '')),
                        'duration' => intval($data[6]) * 60000,
                        'note' => $db->escapeString($data[7] ?? '')
                    ];

                    if (isset($data[9])) $params['optione'] = $db->escapeString($data[9]);
                    if (isset($data[10])) $params['image'] = $db->escapeString($data[10]);

                    file_put_contents('params_log.txt', print_r($params, true), FILE_APPEND);
                    file_put_contents('category_log.txt', "CSV Insert Category ID: " . $params['category_id'] . "\n", FILE_APPEND);

                    $db->insert('tbl_questions', $params);
                    $inserted++;
                }

                if ($questionIndex >= $totalQuestions) {
                    break; // No more questions to insert
                }
            }

            respond(['status' => 200, 'message' => "$inserted questions inserted into subcategories."]);
            return;
        }



        // For types other than 9 – fallback to original language logic
        $sql = "SELECT id, category_name, Tag, language FROM tbl_categories WHERE type = $type ORDER BY id ASC";
        $db->sql($sql);
        $categoryRows = $db->getResult();

        $categoryMap = [];
        foreach ($categoryRows as $rowCat) {
            $lang = $rowCat['language'];
            $categoryMap[$lang][] = $rowCat['id'];
        }

        $selectedLang = $_POST['language'] ?? null;

        if (!$selectedLang) {
            respond(['status' => 400, 'message' => 'Language is required.']);
            return;
        }

        $questionsByLang = [];

        if ($selectedLang === 'all') {
            foreach ($categoryMap as $langId => $categoryIds) {
                $questionsByLang[$langId] = $questions;
            }
        } else {
            if (!isset($categoryMap[$selectedLang])) {
                respond(['status' => 400, 'message' => 'Invalid language selected.']);
                return;
            }
            $questionsByLang[$selectedLang] = $questions;
        }

        foreach ($questionsByLang as $lang => $questionsSet) {
            if (!isset($categoryMap[$lang])) continue;

            $categories = $categoryMap[$lang];
            $questionIndex = 0;

            foreach ($categories as $catId) {
                for ($i = 0; $i < 10 && $questionIndex < count($questionsSet); $i++, $questionIndex++) {
                    $data = $questionsSet[$questionIndex];
                    $custom_id = intval(date('dmyHis') . str_pad($inserted + 1, 2, '0', STR_PAD_LEFT));

                    $params = [
                        'id' => $custom_id,
                        'category_id' => $catId,
                        'question' => $db->escapeString($data[0] ?? ''),
                        'optiona' => $db->escapeString($data[1] ?? ''),
                        'optionb' => $db->escapeString($data[2] ?? ''),
                        'optionc' => $db->escapeString($data[3] ?? ''),
                        'optiond' => $db->escapeString($data[4] ?? ''),
                        'answer' => strtolower($db->escapeString($data[5] ?? '')),
                        'duration' => intval($data[6]) * 60000,
                        'note' => $db->escapeString($data[7] ?? '')
                    ];

                    if (isset($data[9])) $params['optione'] = $db->escapeString($data[9]);
                    if (isset($data[10])) $params['image'] = $db->escapeString($data[10]);

                    //  file_put_contents('params_log.txt', print_r($params, true), FILE_APPEND);
                    //  file_put_contents('category_log.txt', "CSV Insert Category ID: " . $params['category_id'] . "\n", FILE_APPEND);

                    $db->insert('tbl_questions', $params);
                    $inserted++;
                }
            }
        }

        respond(['status' => 200, 'message' => "$inserted questions inserted successfully."]);
    } else {
        // manual entry
        $data = json_decode(file_get_contents("php://input"), true);
        $custom_id = intval(date('dmyHis'));

        $params = [
            'id' => $custom_id,
            'category_id' => $db->escapeString($data['category_id']),
            'question' => $db->escapeString($data['question'] ?? ''),
            'optiona' => $db->escapeString($data['optiona']),
            'optionb' => $db->escapeString($data['optionb']),
            'optionc' => $db->escapeString($data['optionc']),
            'optiond' => $db->escapeString($data['optiond']),
            'answer' => $db->escapeString($data['answer']),
            'duration' => intval($data['duration']) * 60000,
            'note' => $db->escapeString($data['note'] ?? '')
        ];

        if (isset($data['optione'])) $params['optione'] = $db->escapeString($data['optione']);
        if (isset($data['image'])) $params['image'] = $db->escapeString($data['image']);

        // file_put_contents('params_log.txt', print_r($params, true), FILE_APPEND);
        // file_put_contents('category_log.txt', "Manual Insert Category ID: " . $params['category_id'] . "\n", FILE_APPEND);
        $db->insert('tbl_questions', $params);
        respond(['status' => 200, 'message' => 'Question created successfully']);
    }
}

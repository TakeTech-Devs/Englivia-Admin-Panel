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
    $questions[]=$data;
    }

    fclose($handle);

    $inserted=0;

    if ($type===9 && isset($_POST['category_id'])) {
    $catId=intval($_POST['category_id']);

    foreach ($questions as $data) {
    $custom_id=intval(date('dmyHis') . str_pad($inserted + 1, 2, '0' , STR_PAD_LEFT));

    $params=[ 'id'=> $custom_id,
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

    file_put_contents('params_log.txt', print_r($params, true), FILE_APPEND);
    file_put_contents('category_log.txt', "CSV Insert Category ID: " . $params['category_id'] . "\n", FILE_APPEND);
    $db->insert('tbl_questions', $params);
    $inserted++;
    }

    respond(['status' => 200, 'message' => "$inserted questions inserted into selected category."]);
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
        $data=$questionsSet[$questionIndex];
        $custom_id=intval(date('dmyHis') . str_pad($inserted + 1, 2, '0' , STR_PAD_LEFT));

        $params=[ 'id'=> $custom_id,
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
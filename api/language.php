<?php
header("Content-Type: application/json");
require_once '../library/crud.php';

$db = new Database();
$db->connect();

$query = "SELECT * from languages";

$db->sql($query);
$result = $db->getResult();
echo json_encode(['status' => 200, 'data' => $result]);


$db->disconnect();
?>
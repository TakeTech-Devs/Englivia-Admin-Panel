<?php
require 'library/crud.php'; // Include database connection
require 'library/Functions.php'; // Include the class where `verify_otp` is defined

header('Content-Type: application/json');

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['otp']) || !isset($_POST['user_id'])) {
        $response['error'] = true;
        $response['message'] = "OTP and User ID are required.";
        echo json_encode($response);
        return;
    }

    $db = new Database(); // Assuming `db_connection.php` initializes this class
    $db->connect();

    $fn = new Functions($db); // Assuming `Functions.php` initializes this class

    $otp = $db->escapeString(trim($_POST['otp']));
    $user_id = $db->escapeString(trim($_POST['user_id'])); // Assuming the email is used as `user_id`.

    // Call the verify_otp function
    $verify_response = $fn->verify_otp($user_id, $otp);

    if ($verify_response['success']) {
        $response['error'] = false;
        $response['message'] = $verify_response['message'];
        echo json_encode($response); // Respond with success flag for the JavaScript logic
    } else {
        $response['error'] = true;
        $response['message'] = $verify_response['message'];
        echo json_encode($response); // Provide detailed error message
    }
} else {
    $response['error'] = true;
    $response['message'] = "Invalid request method.";
    echo json_encode($response);
}

<?php
require 'library/crud.php'; // Include database connection
require 'library/Functions.php'; // Include the class where `verify_otp` is defined

header('Content-Type: application/json');

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['email']) || !isset($_POST['new_password']) || !isset($_POST['confirm_password'])) {
        $response['error'] = true;
        $response['message'] = "All fields are required.";
        echo json_encode($response);
        return;
    }

    $db = new Database(); // Assuming `db_connection.php` initializes this class
    $db->connect();

    $fn = new Functions($db); // Assuming `Functions.php` initializes this class

    $email = $db->escapeString(trim($_POST['email']));
    $new_password = $db->escapeString(trim($_POST['new_password']));
    $confirm_password = $db->escapeString(trim($_POST['confirm_password']));

    // Validate the passwords
    if ($new_password !== $confirm_password) {
        $response['error'] = true;
        $response['message'] = "Passwords do not match.";
        echo json_encode($response);
        return;
    }

    if (strlen($new_password) < 6) {
        $response['error'] = true;
        $response['message'] = "Password must be at least 6 characters long.";
        echo json_encode($response);
        return;
    }

    // Hash the password using md5
    $hashed_password = md5($new_password);

    // Check if the user exists
    $user_check_query = "SELECT auth_username FROM authenticate WHERE auth_mail = '$email'";
    $user_check_result = $db->sql($user_check_query);
    if ($db->numRows($user_check_result) > 0) {
        // Update the password
        $update_query = "UPDATE authenticate SET auth_pass = '$hashed_password' WHERE auth_mail = '$email'";
        if ($db->sql($update_query)) {
            $response['error'] = false;
            $response['message'] = "Password reset successfully.";
        } else {
            $response['error'] = true;
            $response['message'] = "Failed to reset password. Please try again.";
        }
    } else {
        $response['error'] = true;
        $response['message'] = "Email not registered.";
    }

    echo json_encode($response);
} else {
    $response['error'] = true;
    $response['message'] = "Invalid request method.";
    echo json_encode($response);
}

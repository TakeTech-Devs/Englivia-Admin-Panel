<?php
require_once 'library/crud.php';

// Include PHPMailer files
require 'library/mail/Exception.php';
require 'library/mail/PHPMailer.php';
require 'library/mail/SMTP.php';

require 'library/secrets.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];

    $db = new Database();
    $db->connect();

    // Function to send OTP
    function send_otp_to_email($db, $user_email, $otp_length = 6)
    {
        // Check if the user exists in the database
        $user_check_sql = "SELECT * FROM `authenticate` WHERE `auth_mail` = '$user_email' LIMIT 1";
        $db->sql($user_check_sql);
        $user = $db->getResult();

        if (empty($user)) {
            return [
                'success' => false,
                'message' => 'Email not registered.'
            ];
        }

        // Generate a random OTP
        $otp = str_pad(mt_rand(0, pow(10, $otp_length) - 1), $otp_length, '0', STR_PAD_LEFT);

        // Get current time
        $created_at = date('Y-m-d H:i:s');

        // Save OTP in the database
        $insert_sql = "INSERT INTO `otp` (`user`, `otp`, `created_at`) VALUES ('{$user[0]['auth_mail']}', '$otp', '$created_at')";
        $db->sql($insert_sql);

        // Send OTP via email
        $subject = "Your OTP Code for Password Reset";
        $message = "Dear User,\n\nYour OTP is $otp. Please use this code to reset your password. This OTP is valid for 5 minutes.";

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $secrets = new Secrets();

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $secrets->getvar('mail_username');
            $mail->Password = $secrets->getvar('mail_password');
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('englivia21@gmail.com', 'Englivia Admin Panel');
            $mail->addAddress($user_email);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = nl2br($message);

            $mail->send();
            return [
                'success' => true,
                'message' => 'OTP sent successfully.'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to send OTP. Please try again later.',
                'error' => $mail->ErrorInfo
            ];
        }
    }

    // Send the OTP and get the response
    $otp_response = send_otp_to_email($db, $email);

    if ($otp_response['success']) {
        echo '1'; // OTP sent successfully
    } else {
        echo $otp_response['message']; // Error message
    }
} else {
    echo 'Invalid request. Please provide a valid email address.';
}

<?php

/*
  API v5.6
  Quiz Online - WRTeam.in
  WRTeam Developers
 */
require_once 'crud.php';

// Include PHPMailer files
require 'mail/Exception.php';
require 'mail/PHPMailer.php';
require 'mail/SMTP.php';

// require 'secrets.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/*
  Functions
  -------------
  1. get_configurations()
  2. is_language_mode_enabled()
  3. is_option_e_mode_enabled()
  4. get_user_by_id()
  5. get_count()
  6. get_sum()
  7. get_fields()
  8. upload_file()
  9. no_of_days_bw_dates()
  10. get_user_IP()
  11. is_refer_code_set($user_id)
  12. credit_coins_to_friends_code($friends_code)
  13. check_friends_code_is_used_by_user($user_id)
  14. valid_friends_refer_code($friends_code)
  14. send_otp()
  15. verify_otp()
 */

class Functions
{

    private $db;

    function __construct()
    {
        $this->db = new Database();
        $this->db->connect();
    }

    public function get_configurations()
    {
        $this->db->sql("SET NAMES 'utf8'");
        $sql = "SELECT * FROM settings WHERE type='system_configurations' LIMIT 1";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res)) {
            return json_decode($res[0]['message'], true);
        } else {
            return false;
        }
    }

    public function is_language_mode_enabled()
    {
        $configs = $this->get_configurations();
        if (!empty($configs)) {
            if (isset($configs['language_mode']) && $configs['language_mode'] == 1)
                return true;
            else
                return false;
        } else {
            return false;
        }
    }

    public function is_option_e_mode_enabled()
    {
        $configs = $this->get_configurations();
        if (!empty($configs)) {
            if (isset($configs['option_e_mode']) && $configs['option_e_mode'] == 1)
                return true;
            else
                return false;
        } else {
            return false;
        }
    }

    public function get_user_by_id($id)
    {
        $sql = "Select * from `users` where `id` = '$id'";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res)) {
            return $res[0];
        } else {
            return false;
        }
    }

    public function get_count($field, $table, $where = '')
    {
        if (!empty($where))
            $where = "where " . $where;

        $sql = "SELECT COUNT(" . $field . ") as total FROM " . $table . " " . $where;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res)) {
            return $res[0]['total'];
        } else {
            return 0;
        }
    }

    public function get_sum($field, $table, $where = '')
    {
        if (!empty($where))
            $where = "where " . $where;

        $sql = "SELECT SUM(" . $field . ") as total FROM " . $table . " " . $where;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res)) {
            return $res[0]['total'];
        } else {
            return 0;
        }
    }

    public function get_fields($table, $fields = '*', $where = '', $order = '', $limit = '')
    {
        $this->db->select($table, $fields, '', $where, $order, $limit);
        $res = $this->db->getResult();
        if (!empty($res)) {
            if (count($res) == 1) {
                return $res[0];
            } else {
                return $res;
            }
        } else {
            return false;
        }
    }

    public function upload_file($file, $target_path, $allowed_extensions)
    {
        $extension = end(explode(".", $file["name"]));
        if (!(in_array($extension, $allowed_extensions))) {
            $response['error'] = true;
            $response['message'] = "Invalid image format. only jpeg, jpg, png or gif format images are allowed";
            return $response;
        }
        if (!is_dir($target_path)) {
            mkdir($target_path, 0777, true);
        }
        $filename = microtime(true) . '.' . strtolower($extension);
        $full_path = $target_path . "" . $filename;
        if (!move_uploaded_file($file["tmp_name"], $full_path)) {
            $response['error'] = true;
            $response['message'] = "File could not be uploaded.";
            return $response;
        } else {
            $response['error'] = false;
            $response['message'] = "File uploaded successfully";
            $response['filename'] = $filename;
            $response['target_path'] = $target_path;
            $response['full_path'] = $full_path;
            return $response;
        }
    }

    public function no_of_days_bw_dates($from, $to)
    {
        $from = strtotime($from);
        $to = strtotime($to);
        $datediff = $to - $from;

        return round($datediff / (60 * 60 * 24));
    }

    function get_user_IP()
    {
        // Get real visitor IP behind CloudFlare network
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
            $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        $client = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote = $_SERVER['REMOTE_ADDR'];

        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }
        return $ip;
    }

    public function is_refer_code_set($user_id)
    {
        $sql = "SELECT `refer_code` FROM users WHERE id='" . $user_id . "'";
        $this->db->sql($sql);
        $res = $this->db->getResult();

        if (!empty($res[0]['refer_code'])) {
            return true;
        } else {
            return false;
        }
    }

    public function credit_coins_to_friends_code($friends_code)
    {
        $configs = $this->get_configurations();

        $sql = "UPDATE `users` SET `coins` = `coins` + " . $configs['earn_coin'] . " WHERE `refer_code`='" . $friends_code . "'";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        // return $res;
        $response['credited'] = true;
        return $response;
    }

    public function check_friends_code_is_used_by_user($user_id)
    {
        $sql = "SELECT friends_code FROM users WHERE id='" . $user_id . "'";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        // return $res;

        if (!empty($res[0]['friends_code'])) {
            $response['is_used'] = true;
        } else {
            $response['is_used'] = false;
        }
        return $response;
    }

    public function valid_friends_refer_code($friends_code)
    {
        $sql = "SELECT id,name,email FROM users WHERE refer_code='" . $friends_code . "'";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        // return $res;

        if (!empty($res)) {
            $response['is_valid'] = true;
            $response['user_id'] = $res[0]['id'];
            $response['name'] = $res[0]['name'];
            $response['email'] = $res[0]['email'];
        } else {
            $response['is_valid'] = false;
        }
        return $response;
    }

    public function get_battle_settings()
    {
        $this->db->sql("SET NAMES 'utf8'");
        $sql = "SELECT * FROM settings WHERE type='battle_settings' LIMIT 1";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res)) {
            return json_decode($res[0]['message'], true);
        } else {
            return false;
        }
    }

    public function is_battle_category_mode_enabled()
    {
        $configs = $this->get_configurations();
        if (!empty($configs)) {
            if (isset($configs['battle_random_category_mode']) && $configs['battle_random_category_mode'] == 1)
                return true;
            else
                return false;
        } else {
            return false;
        }
    }

    public function is_room_category_mode_enabled()
    {
        $configs = $this->get_configurations();
        if (!empty($configs)) {
            if (isset($configs['battle_group_category_mode']) && $configs['battle_group_category_mode'] == 1)
                return true;
            else
                return false;
        } else {
            return false;
        }
    }

    public function send_otp($user_id, $user_check = true, $otp_length = 6)
    {
        // Check if the user exists in the database
        $user_check_sql = "SELECT * FROM `users` WHERE `mobile` = '$user_id' LIMIT 1";
        $this->db->sql($user_check_sql);
        $user = $this->db->getResult();

        if ($user_check && empty($user)) {
            // User does not exist
            return [
                'success' => false,
                'message' => 'User does not exist.'
            ];
        }

        // Generate a random OTP
        $otp = str_pad(mt_rand(0, pow(10, $otp_length) - 1), $otp_length, '0', STR_PAD_LEFT);

        // Get current time for record creation
        $created_at = date('Y-m-d H:i:s');

        // Insert OTP into the database
        $sql = "INSERT INTO `otp` (`user`, `otp`, `created_at`) VALUES ('$user_id', '$otp', '$created_at')";
        $this->db->sql($sql);

        // Send the OTP via Fast2SMS API
        $phone_number = $user_id; // Assuming the user's phone number is stored in the `phone` column
        $api_key = "ltVk2HoWMu0iqXBedN1m6rGYRhwKcfEOD54yP7QT3SUJxLnAzbvLSAHgEP0NRiU6XZcDhJzMsdwb7u2t";
        $url = "https://www.fast2sms.com/dev/bulkV2";

        $payload = [
            "route" => "otp",
            "variables_values" => $otp,
            "schedule_time" => "", // Optional: Specify a scheduled time or leave it empty
            "numbers" => $phone_number
        ];

        $headers = [
            "authorization: $api_key",
            "Content-Type: application/json"
        ];

        // Initialize cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Execute cURL request and fetch response
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Check if SMS was sent successfully
        if ($http_code == 200) {
            return [
                'success' => true,
                'message' => 'OTP sent successfully.',
                // 'otp' => $otp // Remove this in production
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.',
                'error' => $response // Log this for debugging
            ];
        }
    }


    function send_otp_to_email($user_email, $otp_length = 6)
    {
        // Check if the user exists in the database
        $user_check_sql = "SELECT * FROM `users` WHERE `email` = '$user_email' LIMIT 1";
        $this->db->sql($user_check_sql);
        $user = $this->db->getResult();

        if (empty($user)) {
            // User does not exist
            return [
                'success' => false,
                'message' => 'User does not exist.'
            ];
        }

        // Generate a random OTP
        $otp = str_pad(mt_rand(0, pow(10, $otp_length) - 1), $otp_length, '0', STR_PAD_LEFT);

        // Get current time for record creation
        $created_at = date('Y-m-d H:i:s');

        // Insert OTP into the database
        $sql = "INSERT INTO `otp` (`user`, `otp`, `created_at`) VALUES ('{$user[0]['email']}', '$otp', '$created_at')";
        $this->db->sql($sql);

        // Email details
        $subject = "Your OTP Code";
        $message = "Dear {$user[0]['name']},\n\nYour OTP is $otp. Please use this to verify your email. The OTP is valid for 5 minutes.";

        // Send the email
        $mail = new PHPMailer(true);
        $secrets = new Secrets();

        try {
            // SMTP configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $secrets->getvar('mail_username'); // Replace with your Gmail address
            $mail->Password = $secrets->getvar('mail_password');   // Replace with your Gmail App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587; // Use 465 for SSL if preferred

            // Sender and recipient settings
            $mail->setFrom('englivia21@gmail.com', 'Englivia'); // Replace with your details
            $mail->addAddress($user_email); // Replace with recipient's email

            // Email content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $message;
            $mail->AltBody = 'This is a plain-text version of the email.';

            // Send the email
            $mail->send();
            return [
                'success' => true,
                'message' => 'OTP sent successfully.',
                'otp' => $otp // Only for debugging; remove in production.
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to send OTP. Please try again later.',
                'error' => $mail->ErrorInfo
            ];
        }
    }

    public function verify_otp($user_id, $otp, $expiry_minutes = 5)
    {
        // Calculate expiry time
        $expiry_time = date('Y-m-d H:i:s', strtotime("-$expiry_minutes minutes"));

        // Trim User ID
        $user_id = trim($user_id);

        // Check if the OTP is valid and not expired
        $sql = "SELECT * FROM `otp` WHERE `user` = '$user_id' AND `otp` = '$otp' AND `created_at` >= '$expiry_time' LIMIT 1";
        $this->db->sql($sql);
        $res = $this->db->getResult();

        if (!empty($res)) {
            // OTP is valid, remove it from the database to prevent reuse
            $delete_sql = "DELETE FROM `otp` WHERE `id` = " . $res[0]['id'];
            $this->db->sql($delete_sql);

            return [
                'success' => true,
                'message' => 'OTP verified successfully.'
            ];
        } else {
            // Remove all expired user's otp
            $sql = "DELETE FROM `otp` WHERE `user` = '$user_id' AND `created_at` >= '$expiry_time' LIMIT 1";
            $this->db->sql($sql);

            return [
                'success' => false,
                'message' => 'Invalid or expired OTP.'
            ];
        }
    }
}

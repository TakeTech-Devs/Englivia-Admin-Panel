<?php
// Include necessary libraries and dependencies
include_once('library/jwt.php');
include_once('library/crud.php');
include_once('library/functions.php');

// Function to verify the token and handle response
function verify_token_response()
{
    $jwt = new JWT();
    try {
        // Extract the token from the Authorization header
        $token = $jwt->getBearerToken();
    } catch (Exception $e) {
        return [
            'error' => true,
            'message' => $e->getMessage()
        ];
    }

    if (!empty($token)) {
        try {
            // Decode the token
            $payload = $jwt->decode($token, JWT_SECRET_KEY, ['HS256']);

            // Verify 'iss' field and other claims
            if (!isset($payload->iss) || $payload->iss !== 'quiz') {
                return [
                    'error' => true,
                    'message' => 'Invalid issuer (iss) in token.'
                ];
            }

            // Token verification passed
            return [
                'error' => false,
                'message' => 'Token is valid.',
                'payload' => $payload
            ];
        } catch (Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    } else {
        return [
            'error' => true,
            'message' => 'Unauthorized access. Token not found.'
        ];
    }
}

// Handle API response
header('Content-Type: application/json');
$response = verify_token_response();
echo json_encode($response);

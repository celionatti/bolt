<?php


use Firebase\JWT\JWT;

// User data
$userData = [
    'user_id' => 123,
    'username' => 'john_doe',
    'role' => 'user'
];

// JWT secret key (keep this secret!)
$secretKey = 'your_secret_key';

// JWT payload (claims)
$payload = [
    'iss' => 'your_app_name',
    'sub' => 'auth_token',
    'exp' => time() + 3600, // Token expiration time (e.g., 1 hour)
    'data' => $userData
];

// Generate the JWT
$token = JWT::encode($payload, $secretKey, 'HS256');


/**
 * Send to client
 */

 $response = [
    'message' => 'Login successful',
    'token' => $token
];

echo json_encode($response);


/**
 * Protec the routes
 */

 use Firebase\JWT\JWT;

// JWT secret key (must be the same as used for token generation)
$secretKey = 'your_secret_key';

// Get the JWT from the request headers
$jwt = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

if ($jwt) {
    try {
        // Verify the JWT
        $decoded = JWT::decode($jwt, $secretKey, ['HS256']);
        $userData = $decoded->data;

        // Access user data (e.g., $userData->user_id, $userData->username)
        // Perform authorized actions

    } catch (Exception $e) {
        // JWT is invalid or expired
        http_response_code(401); // Unauthorized
        echo json_encode(['message' => 'Authentication failed']);
    }
} else {
    // JWT is missing
    http_response_code(401); // Unauthorized
    echo json_encode(['message' => 'Authentication required']);
}

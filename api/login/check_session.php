<?php
// Enable CORS

header('Access-Control-Allow-Origin: '. getenv('ORIGIN_PATH'));
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true'); // Add this line

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Set content type to JSON
header('Content-Type: application/json');

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

try {
    // Check if user session exists
    if (isset($_SESSION['username'])) {
        echo json_encode(['loggedIn' => true]);
    } else {
        echo json_encode(['loggedIn' => false]);
    }
} catch (Exception $e) {
    // Handle any exceptions and return an error response
    http_response_code(500);
    echo json_encode([
        'error' => 'An error occurred while processing your request.',
        'message' => $e->getMessage()
    ]);
    error_log($e->getMessage());
}
?>

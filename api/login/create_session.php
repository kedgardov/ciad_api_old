<?php
// Enable CORS
header('Access-Control-Allow-Origin: '. getenv('ORIGIN_PATH'));
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true'); // Allow credentials

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Set content type to JSON
header('Content-Type: application/json');

require_once '../../classes/Database.php';
require_once '../../classes/User.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

try {
    // Create a new Database instance
    $config = include('../../config/db_config.php');
    $db = new Database($config['servername'], $config['username'], $config['password'], $config['dbname']);

    // Get the input data
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input === null) {
        throw new Exception('Invalid JSON input');
    }

    // Validate the input data
    if (!isset($input['username']) || !isset($input['password'])) {
        throw new Exception('Invalid input data');
    }

    $username = $input['username'];
    $password = $input['password'];

    // Create a new User instance
    $user = new User($db);

    // Check user credentials
    if ($user->checkCredentials($username, $password)) {
        // User found, create session
        $_SESSION['username'] = $username;
        $userName = $user->getUserName($username);
        echo json_encode(["success" => true, "message" => "Login successful", "name" => $userName]);
    } else {
        // User not found
        echo json_encode(["success" => false, "message" => "Invalid username or password"]);
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

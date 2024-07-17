<?php

// Enable CORS
header('Access-Control-Allow-Origin: '. getenv('ORIGIN_PATH'));
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include necessary files
require_once '../../classes/Database.php';
require_once '../../classes/Curso.php';
$config = include '../../config/db_config.php';

session_start();

try {
    // Check if user is authenticated
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];

        // Create database connection
        $db = new Database($config['servername'], $config['username'], $config['password'], $config['dbname']);

        // Instantiate Curso class and fetch courses
        $curso = new Curso($db);
        $courses = $curso->selectAllWhereUsername($username);

        // Return courses as JSON response
        echo json_encode(['courses' => $courses]);
    } else {
        // Return unauthorized or empty response if session username is not set
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
    }
} catch (Exception $e) {
    // Handle any exceptions and return an error response
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred']);
}
?>

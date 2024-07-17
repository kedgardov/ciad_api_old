<?php
// Enable CORS
header('Access-Control-Allow-Origin: '. getenv('ORIGIN_PATH'));
header('Access-Control-Allow-Methods: POST, OPTIONS');
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
    // Decode input data
    $input = json_decode(file_get_contents('php://input'), true);

    // Check if user is authenticated
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];

        // Create database connection
        $db = new Database($config['servername'], $config['username'], $config['password'], $config['dbname']);

        // Instantiate Curso class
        $curso = new Curso($db);

        // Update curso
        $result = $curso->updateCurso($input);

        // Return success response with input data
        $response = [
            'success' => true,
        ];

        echo json_encode($response);
    } else {
        // Return unauthorized response if session username is not set
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
    }
} catch (Exception $e) {
    // Handle any exceptions and return an error response
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}
?>

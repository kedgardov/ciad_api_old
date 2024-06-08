<?php

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Set content type to JSON
header('Content-Type: application/json');

require_once '../../classes/Database.php';
require_once '../../classes/Curso.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Load database configuration
$config = include '../../config/db_config.php';

try {
    // Create a new Database instance
    $db = new Database($config['servername'], $config['username'], $config['password'], $config['dbname']);
    $conn = $db->getConnection();
    if (!$conn) {
        throw new Exception('Failed to connect to the database');
    }

    // Get the curso ID from the query parameters
    $cursoId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($cursoId <= 0) {
        throw new Exception('Invalid curso ID');
    }

    // Create a new Curso instance
    $curso = new Curso($db);

    // Retrieve the curso details
    $cursoDetails = $curso->selectOne($cursoId,'id_curso');
    if (!$cursoDetails) {
        throw new Exception('Curso not found');
    }

    // Return the data as JSON
    echo json_encode($cursoDetails);
} catch (Exception $e) {
    // Handle any exceptions and return an error response
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred while processing your request.']);
    error_log($e->getMessage());
}
?>

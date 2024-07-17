<?php

// Enable CORS
header('Access-Control-Allow-Origin: '. getenv('ORIGIN_PATH'));
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Set content type to JSON
header('Content-Type: application/json');

require_once '../../classes/Database.php';
require_once '../../classes/Docente.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Load database configuration
$config = include '../../config/db_config.php';

try {
    // Create a new Database instance
    $db = new Database($config['servername'], $config['username'], $config['password'], $config['dbname']);

    // Create a new Docente instance
    $docente = new Docente($db);

    // Retrieve all docentes
    $docentes = $docente->selectAll();

    // Return the data as JSON
    echo json_encode(['docentes' => $docentes]);
} catch (Exception $e) {
    // Handle any exceptions and return an error response
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred while processing your request.']);
    error_log($e->getMessage());
}
?>

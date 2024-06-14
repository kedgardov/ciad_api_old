<?php

// Enable CORS
header('Access-Control-Allow-Origin: *');
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
require_once '../../classes/Seriacion.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load database configuration
$config = include '../../config/db_config.php';

try {
    // Create a new Database instance
    $db = new Database($config['servername'], $config['username'], $config['password'], $config['dbname']);
    $conn = $db->getConnection();
    if (!$conn) {
        throw new Exception('Failed to connect to the database');
    }

    // Get the input data
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate the input data
    if (!isset($input['id_curso']) || !isset($input['id_requisito'])) {
        throw new Exception('Invalid input data');
    }

    // Create a new Seriacion instance
    $seriacion = new Seriacion($db);

    // Remove by id_curso and id_requisito
    $result = $seriacion->deleteByCursoAndRequisito($input['id_curso'], $input['id_requisito']);

    if (!$result) {
        throw new Exception('Failed to remove the seriacion');
    }

    // Return a success response
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Handle any exceptions and return an error response
    http_response_code(500);
    $errorMessage = $e->getMessage();
    echo json_encode(['error' => $errorMessage]);
    error_log($errorMessage);
}

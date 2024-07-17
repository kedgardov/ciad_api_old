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
require_once '../../classes/Colaborador.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Load database configuration
$config = include '../../config/db_config.php';

try {
    // Create a new Database instance
    $db = new Database($config['servername'], $config['username'], $config['password'], $config['dbname']);

    // Get the input data
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input === null) {
        throw new Exception('Invalid JSON input');
    }

    // Validate the input data
    if (!isset($input['id_colaboracion'])) {
        throw new Exception('Invalid input data');
    }

    $id_colaboracion = intval($input['id_colaboracion']);

    // Create a new Colaborador instance
    $colaboradorObj = new Colaborador($db);

    // Delete the colaborador
    $result = $colaboradorObj->deleteById('id_rol_curso', $id_colaboracion);

    if (!$result) {
        throw new Exception('Failed to delete the colaborador');
    }

    // Return a success response
    echo json_encode(['success' => true]);
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

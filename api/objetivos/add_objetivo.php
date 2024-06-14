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
require_once '../../classes/Objetivo.php';

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

    // Get the input data
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate the input data
    if (!isset($input['id_curso']) || !isset($input['tipo']) || !isset($input['numero']) || !isset($input['objetivo'])) {
        throw new Exception('Invalid input data');
    }

    $id_curso = intval($input['id_curso']);
    $tipo = $input['tipo'];
    $numero = intval($input['numero']);
    $objetivo = $input['objetivo'];

    // Create a new Objetivo instance
    $objetivoInstance = new Objetivo($db);

    $result = $objetivoInstance->insert([
        'id_curso' => $id_curso,
        'tipo' => $tipo,
        'numero' => $numero,
        'objetivo' => $objetivo
    ]);

    if (!$result) {
        throw new Exception('Failed to add the objetivo');
    }

    $id_objetivo = $conn->insert_id;

    // Return a success response with the new objetivo ID
    echo json_encode(['success' => true, 'id_objetivo' => $id_objetivo]);
} catch (Exception $e) {
    // Handle any exceptions and return an error response
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred while processing your request.']);
    error_log($e->getMessage());
}

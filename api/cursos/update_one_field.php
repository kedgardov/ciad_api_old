<?php

// Set content type to JSON
header('Access-Control-Allow-Origin: '. getenv('ORIGIN_PATH'));
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

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

    // Get the input data
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate the input data
    if (!isset($input['id']) || !isset($input['field']) || !isset($input['value'])) {
        throw new Exception('Invalid input data');
    }

    $id = intval($input['id']);
    $field = $input['field'];
    $value = $input['value'];

    // Create a new Curso instance
    $curso = new Curso($db);

    $result = $curso->update($id, $field, $value, 'id_curso');

    if (!$result) {
        // Log detailed SQL error
        echo json_encode([
            'error' => 'Failed to update the curso',
            'sql' => "UPDATE cursos SET $field = ? WHERE id = $id"
        ]);
        exit;
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
?>

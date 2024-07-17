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
    if (!isset($input['id_curso'])) {
        throw new Exception('Invalid input data');
    }

    $id_curso = intval($input['id_curso']);

    // Create a new Objetivo instance
    $objetivoInstance = new Objetivo($db);

    // Fetch current objetivos
    $currentObjetivos = $objetivoInstance->selectAllWhereId('id_curso', $id_curso);
    $especificos = array_filter($currentObjetivos, function($obj) {
        return $obj['tipo'] === 'especifico';
    });

    // Sort by numero
    usort($especificos, function($a, $b) {
        return $a['numero'] <=> $b['numero'];
    });

    // Update the numeros to be consecutive starting from 1
    foreach ($especificos as $index => $obj) {
        $newNumero = $index + 1;
        $objetivoInstance->updateNumero($obj['id_objetivo'], $newNumero);
    }

    // Return a success response
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Handle any exceptions and return an error response
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred while processing your request.']);
    error_log($e->getMessage());
}
?>

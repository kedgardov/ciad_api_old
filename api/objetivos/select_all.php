<?php

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

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

    // Get the curso ID from the query parameters
    $id_curso = isset($_GET['id_curso']) ? intval($_GET['id_curso']) : 0;
    if ($id_curso <= 0) {
        throw new Exception('Invalid curso ID');
    }

    $objetivo = new Objetivo($db);

    // Retrieve all objetivos for the given curso
    $objetivos = $objetivo->selectAllWhereId('id_curso', $id_curso);

    // Return the data as JSON
    echo json_encode(['success' => true, 'objetivos' => $objetivos]);
} catch (Exception $e) {
    // Handle any exceptions and return an error response
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred while processing your request.']);
    error_log($e->getMessage());
}

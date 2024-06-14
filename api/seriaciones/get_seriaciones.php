<?php

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Set content type to JSON
header('Content-Type: application/json');

require_once '../../classes/Database.php';
require_once '../../classes/Seriacion.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Load database configuration
$config = include '../../config/db_config.php';

try {
    // Create a new Database instance
    $db = new Database($config['servername'], $config['username'], $config['password'], $config['dbname']);

    // Create a new Seriacion instance
    $seriacion = new Seriacion($db);

    // Get course ID from query parameters
    $courseId = isset($_GET['id_curso']) ? intval($_GET['id_curso']) : 0;
    if ($courseId <= 0) {
        throw new Exception('Invalid course ID');
    }

    // Retrieve all seriaciones for the course
    $seriaciones = $seriacion->selectAllWhereId("id_curso",$courseId);

    // Return the data as JSON
    echo json_encode(['seriaciones' => $seriaciones]);
} catch (Exception $e) {
    // Handle any exceptions and return an error response
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred while processing your request.']);
    error_log($e->getMessage());
}
?>

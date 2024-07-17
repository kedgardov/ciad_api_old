<?php

// Enable CORS
header('Access-Control-Allow-Origin: '. getenv('ORIGIN_PATH'));
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Set content type to JSON
header('Content-Type: application/json');

require_once '../../classes/Database.php';
require_once '../../classes/Curso.php';
require_once '../../classes/Habilidad.php';
require_once '../../classes/Actividad.php';
require_once '../../classes/TaxBloom.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Load database configuration
$config = include '../../config/db_config.php';

try {
    // Create a new Database instance
    $db = new Database($config['servername'], $config['username'], $config['password'], $config['dbname']);
    // Create a new Curso instance
    $curso = new Curso($db);
    $habilidad = new Habilidad($db);
    $actividad = new Actividad($db);
    $TaxBloom = new TaxBloom($db);
    // Retrieve all courses
    $actividades = $actividad->selectAll();
    $habilidades = $habilidad->selectAll();
    $blooms = $TaxBloom->selectAll();
    // Return the data as JSON
    echo json_encode(['actividades' => $actividades,
                      'habilidades' => $habilidades,
                      'tax_bloom' => $blooms]);
} catch (Exception $e) {
    // Handle any exceptions and return an error response
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred while processing your request.']);
    error_log($e->getMessage());
}

<?php

// Enable CORS
header('Access-Control-Allow-Origin: '. getenv('ORIGIN_PATH'));
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include necessary files
require_once '../../classes/Database.php';
require_once '../../utilities/check_permissions.php';
$config = include '../../config/db_config.php';

session_start();

try {
    // Retrieve id from the query string
    $id_unidad = filter_input(INPUT_GET, 'id_unidad', FILTER_VALIDATE_INT);
    if (!$id_unidad) {
        throw new Exception('Invalid or missing id_unidad parameter');
    }

    // Check if user is authenticated
    if (!isset($_SESSION['username'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit();
    }

    $username = $_SESSION['username'];

    // Create database connection
    $db = new Database($config['servername'], $config['username'], $config['password'], $config['dbname']);
    $conn = $db->getConnection();
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    if (!userHasPermissionsInUnidad($username, $id_unidad, $conn)) {
        throw new Exception('User is not allowed to see these temas');
    }

    $sql = "SELECT * FROM temas WHERE id_unidad = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error);
    }

    $stmt->bind_param('i', $id_unidad);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception('Failed to execute query: ' . $stmt->error);
    }

    $temas = [];
    while ($row = $result->fetch_assoc()) {
        $temas[] = $row;
    }

    echo json_encode(['temas' => $temas]);

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    // Handle any exceptions and return an error response
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}
?>

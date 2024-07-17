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
$config = include '../../config/db_config.php';

session_start();

try {
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];

        // Create database connection
        $db = new Database($config['servername'], $config['username'], $config['password'], $config['dbname']);
        $conn = $db->getConnection();

        // Prepare the SQL query
        $sql = "SELECT * FROM catalogo_coordinaciones WHERE 1";

        // Prepare and execute the statement
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        // Get result set
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $coordinaciones = [];
            while ($row = $result->fetch_assoc()) {
                $coordinaciones[] = $row;
            }
            echo json_encode(['success' => true, 'coordinaciones' => $coordinaciones]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No coordinaciones found for the given curso ID']);
        }

        // Close the statement and database connection
        $stmt->close();
        $conn->close();
    } else {
        // Return unauthorized or empty response if session username is not set
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
    }
} catch (Exception $e) {
    // Handle any exceptions and return an error response
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}
?>

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
    if (!isset($_GET['id'])) {
        throw new Exception('Missing id parameter');
    }

    $id = intval($_GET['id']);

    // Check if user is authenticated
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];

        // Create database connection
        $db = new Database($config['servername'], $config['username'], $config['password'], $config['dbname']);
        $conn = $db->getConnection();

        if( userHasPermissionsInCurso($username, $id, $conn) ){
            $sql = "
            SELECT id_coordinacion_curso, ciudad, estado
            FROM catalogo_coordinaciones cc
            INNER JOIN coordinaciones_cursos ccr ON cc.id_coordinacion = ccr.id_coordinacion
            WHERE ccr.id_curso = ?
            ";

            // Execute the query
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if($result->num_rows > 0) {
                $coordinaciones = [];
                while ($row = $result->fetch_assoc()) {
                    $coordinaciones[] = $row;
                }
                echo json_encode(['success' => true, 'coordinaciones' => $coordinaciones]);
            } else {
                echo json_encode(['success' => true, 'coordinaciones' => []]);
            }
        }else{
            throw new Exception('User is not allowed to see this curso');
        }

        $stmt->close();

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

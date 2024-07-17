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

    $input = json_decode(file_get_contents('php://input'),true);

    $id_curso = isset($input['id_curso']) ? filter_var($input['id_curso'], FILTER_VALIDATE_INT) : 0;
    $id_unidad = isset($input['id_unidad']) ? filter_var($input['id_unidad'], FILTER_VALIDATE_INT) : 0;

    if ( $id_curso === false || $id_objetivo === false ) {
        throw new Exception('Invalid input parameters');
    }

    // Check if user is authenticated
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];

        // Create database connection
        $db = new Database($config['servername'], $config['username'], $config['password'], $config['dbname']);
        $conn = $db->getConnection();

        if (userHasPermissionsInCurso($username, $id_curso, $conn)) {
            $sql = "
            SELECT *
            FROM unidades
            WHERE id_curso = ? AND id_unidad = ?
            ";

            // Execute the query
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ii', $id_curso, $id_unidad);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $unidad = $result->fetch_assoc();
                echo json_encode(['unidad' => $unidad]);
            }
            else {
                throw new Exception('Unidad not found');
            }
        } else {
            throw new Exception('User is not allowed to see these unidades');
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

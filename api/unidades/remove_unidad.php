<?php
// Enable CORS
header('Access-Control-Allow-Origin: '. getenv('ORIGIN_PATH'));
header('Access-Control-Allow-Methods: POST, OPTIONS');
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
    // Decode input data
    $input = json_decode(file_get_contents('php://input'), true);

    $id_curso = isset($input['id_curso']) ? filter_var($input['id_curso'], FILTER_VALIDATE_INT) : null;
    $id_unidad = isset($input['id_unidad']) ? filter_var($input['id_unidad'], FILTER_VALIDATE_INT) : null;

    if ($id_curso === false || $id_unidad === false || $id_curso <= 0 || $id_unidad <= 0) {
        throw new Exception('Invalid input parameters');
    }

    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];

        $db = new Database($config['servername'], $config['username'], $config['password'], $config['dbname']);
        $conn = $db->getConnection();

        if (userHasPermissionsInCurso($username, $id_curso, $conn)) {
            $sql = "
                DELETE FROM unidades
                WHERE id_unidad = ? AND id_curso = ?
            ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ii', $id_unidad, $id_curso);
            $stmt->execute();

            if ($stmt->error) {
                throw new Exception('Failed:' . $stmt->error);
            }

            $response = [
                'success' => true,
            ];

            echo json_encode($response);
        } else {
            throw new Exception('User is not allowed to modify this curso');
        }
    } else {
        // Return unauthorized response if session username is not set
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
    }
} catch (Exception $e) {
    // Handle any exceptions and return an error response
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}
?>

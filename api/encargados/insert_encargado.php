<?php
// Enable error logging
ini_set('log_errors', 'On');
ini_set('error_log', '/path/to/php-error.log');
error_reporting(E_ALL);

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
    error_log('Input data: ' . print_r($input, true)); // Log input data

    // Extract and validate input data
    $id_curso = isset($input['id_curso']) ? filter_var($input['id_curso'], FILTER_VALIDATE_INT) : null;
    $encargado = isset($input['encargado']) ? $input['encargado'] : null;

    if (!$encargado) {
        throw new Exception('Invalid parameters');
    }

    $id_docente = isset($encargado['id_docente']) ? filter_var($encargado['id_docente'], FILTER_VALIDATE_INT) : null;
    $id_rol = isset($encargado['id_rol']) ? filter_var($encargado['id_rol'], FILTER_VALIDATE_INT) : null;

    // Validate required parameters
    if ($id_curso === false || $id_docente === false || $id_rol === false) {
        throw new Exception('Invalid input parameters');
    }

    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
        error_log('Username from session: ' . $username); // Log username

        $db = new Database($config['servername'], $config['username'], $config['password'], $config['dbname']);
        $conn = $db->getConnection();

        $hasPermissions = userHasPermissionsInCurso($username, $id_curso, $conn);
        error_log('User permissions: ' . ($hasPermissions ? 'Granted' : 'Denied')); // Log permissions

        if ($hasPermissions) {
            $sql = "
                INSERT INTO roles_cursos (id_curso, id_docente, id_rol)
                VALUES (?, ?, ?)
            ";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Failed to prepare statement: ' . $conn->error);
            }

            $stmt->bind_param('iii', $id_curso, $id_docente, $id_rol);
            $stmt->execute();

            if ($stmt->error) {
                throw new Exception('Failed: ' . $stmt->error);
            }

            $response = [
                'success' => true,
                'id_rol_curso' => $stmt->insert_id
            ];

            echo json_encode($response);
        } else {
            throw new Exception('User is not allowed to insert this encargado');
        }
    } else {
        // Return unauthorized response if session username is not set
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
    }
} catch (Exception $e) {
    // Handle any exceptions and return an error response
    error_log($e->getMessage()); // Log error message to error log
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}
?>

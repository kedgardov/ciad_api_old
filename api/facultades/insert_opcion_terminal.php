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
    $opcionTerminal = isset($input['opcionTerminal']) ? $input['opcionTerminal'] : null;

    if (!$opcionTerminal) {
        throw new Exception('Invalid parameters');
    }

    $id_opcion_terminal = isset($opcionTerminal['id_opcion_terminal']) ? filter_var($opcionTerminal['id_opcion_terminal'], FILTER_VALIDATE_INT) : null;
    $id_programa = isset($opcionTerminal['id_programa']) ? filter_var($opcionTerminal['id_programa'], FILTER_VALIDATE_INT) : null;
    $id_modalidad = isset($opcionTerminal['id_modalidad']) ? filter_var($opcionTerminal['id_modalidad'], FILTER_VALIDATE_INT) : null;

    // Validate required parameters
    if ($id_curso === false || $id_opcion_terminal === false || $id_programa === false || $id_modalidad === false) {
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
                INSERT INTO opciones_terminales_cursos (id_curso, id_opcion_terminal, id_programa, id_modalidad)
                VALUES (?, ?, ?, ?)
            ";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Failed to prepare statement: ' . $conn->error);
            }

            $stmt->bind_param('iiii', $id_curso, $id_opcion_terminal, $id_programa, $id_modalidad);
            $stmt->execute();

            if ($stmt->error) {
                throw new Exception('Failed: ' . $stmt->error);
            }

            $response = [
                'success' => true,
                'id_opcion_terminal_curso' => $stmt->insert_id
            ];

            echo json_encode($response);
        } else {
            throw new Exception('User is not allowed to insert this Opcion Terminal');
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

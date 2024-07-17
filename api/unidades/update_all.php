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

    // Get parameters
    $id_curso = isset($input['id_curso']) ? filter_var($input['id_curso'], FILTER_VALIDATE_INT) : null;
    $unidadList = isset($input['unidades']) ? $input['unidades'] : [];
    error_log('id_curso: ' . $id_curso); // Log id_curso
    error_log('unidadList: ' . print_r($unidadList, true)); // Log unidadList

    // Validate parameters
    if ($id_curso === false || empty($unidadList)) {
        throw new Exception('Invalid input parameters');
    }

    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];

        $db = new Database($config['servername'], $config['username'], $config['password'], $config['dbname']);
        $conn = $db->getConnection();

        if (userHasPermissionsInCurso($username, $id_curso, $conn)) {
            $conn->begin_transaction();

            $sql = "UPDATE unidades SET numero = ?, nombre = ? WHERE id_unidad = ? AND id_curso = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Failed to prepare statement: ' . $conn->error);
            }

            foreach ($unidadList as $unidad) {
                // Retrieve and validate each parameter
                $id_unidad = isset($unidad['id_unidad']) ? filter_var($unidad['id_unidad'], FILTER_VALIDATE_INT) : null;
                $numero = isset($unidad['numero']) ? filter_var($unidad['numero'], FILTER_VALIDATE_INT) : null;
                $nombre = isset($unidad['nombre']) ? $unidad['nombre'] : '';

                // Validate required parameters
                if ($id_unidad === false || $numero === false) {
                    error_log('Invalid unidad item parameters: ' . print_r($unidad, true));
                    throw new Exception('Invalid unidad item parameters');
                }

                $stmt->bind_param('isii', $numero, $nombre, $id_unidad, $id_curso);
                $stmt->execute();

                if ($stmt->error) {
                    throw new Exception('Failed: ' . $stmt->error);
                }
            }

            $conn->commit();

            $response = [
                'success' => true
            ];

            echo json_encode($response);
        } else {
            throw new Exception('User is not allowed to update these unidades');
        }
    } else {
        // Return unauthorized response if session username is not set
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
    }
} catch (Exception $e) {
    // Handle any exceptions and return an error response
    error_log($e->getMessage()); // Log error message to error log
    if (isset($conn) && $conn->in_transaction) {
        $conn->rollback();
    }
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}
?>

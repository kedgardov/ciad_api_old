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
    $id_unidad = isset($input['id_unidad']) ? filter_var($input['id_unidad'], FILTER_VALIDATE_INT) : null;
    $temaList = isset($input['temas']) ? $input['temas'] : [];
    error_log('id_unidad: ' . $id_unidad); // Log id_unidad
    error_log('temaList: ' . print_r($temaList, true)); // Log temaList

    // Validate parameters
    if ($id_unidad === false || empty($temaList)) {
        throw new Exception('Invalid input parameters');
    }

    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];

        $db = new Database($config['servername'], $config['username'], $config['password'], $config['dbname']);
        $conn = $db->getConnection();

        if (userHasPermissionsInUnidad($username, $id_unidad, $conn)) {
            $conn->begin_transaction();

            $sql = "UPDATE temas SET numero = ?, nombre = ? WHERE id_tema = ? AND id_unidad = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Failed to prepare statement: ' . $conn->error);
            }

            foreach ($temaList as $tema) {
                // Retrieve and validate each parameter
                $id_tema = isset($tema['id_tema']) ? filter_var($tema['id_tema'], FILTER_VALIDATE_INT) : null;
                $numero = isset($tema['numero']) ? filter_var($tema['numero'], FILTER_VALIDATE_INT) : null;
                $nombre = isset($tema['nombre']) ? $tema['nombre'] : '';

                // Validate required parameters
                if ($id_tema === false || $numero === false) {
                    error_log('Invalid tema item parameters: ' . print_r($tema, true));
                    throw new Exception('Invalid tema item parameters');
                }

                $stmt->bind_param('isii', $numero, $nombre, $id_tema, $id_unidad);
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
            throw new Exception('User is not allowed to update these temas');
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

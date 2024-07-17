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
    error_log('Input data: ' . print_r($input, true)); // Log input data

    // Extract and validate input data
    $id_autor = isset($input['id_autor']) ? filter_var($input['id_autor'], FILTER_VALIDATE_INT) : 0;
    $id_fuente = isset($input['id_fuente']) ? filter_var($input['id_fuente'], FILTER_VALIDATE_INT) : 0;

    // Validate required parameters
    if ($id_autor === 0 || $id_fuente === 0) {
        throw new Exception('Invalid input parameters');
    }

    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
        error_log('Username from session: ' . $username); // Log username

        $db = new Database($config['servername'], $config['username'], $config['password'], $config['dbname']);
        $conn = $db->getConnection();

        $hasPermissions = userHasPermissionsInFuente($username, $id_fuente, $conn);
        error_log('User permissions: ' . ($hasPermissions ? 'Granted' : 'Denied')); // Log permissions

        if ($hasPermissions) {
            $conn->begin_transaction();

            try {
                // Set principal = false for all current principal authors for the given fuente
                $sqlUnsetPrincipal = "UPDATE autores SET principal = 0 WHERE id_fuente = ? AND principal = 1";
                $stmtUnsetPrincipal = $conn->prepare($sqlUnsetPrincipal);
                if (!$stmtUnsetPrincipal) {
                    throw new Exception('Preparation of statement failed: ' . $conn->error);
                }
                $stmtUnsetPrincipal->bind_param('i', $id_fuente);
                $stmtUnsetPrincipal->execute();
                if ($stmtUnsetPrincipal->error) {
                    throw new Exception('Failed to unset principal: ' . $stmtUnsetPrincipal->error);
                }

                // Set principal = true for the specified autor
                $sqlSetPrincipal = "UPDATE autores SET principal = 1 WHERE id_autor = ? AND id_fuente = ?";
                $stmtSetPrincipal = $conn->prepare($sqlSetPrincipal);
                if (!$stmtSetPrincipal) {
                    throw new Exception('Preparation of statement failed: ' . $conn->error);
                }
                $stmtSetPrincipal->bind_param('ii', $id_autor, $id_fuente);
                $stmtSetPrincipal->execute();
                if ($stmtSetPrincipal->error) {
                    throw new Exception('Failed to set principal: ' . $stmtSetPrincipal->error);
                }

                $conn->commit();

                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
        } else {
            throw new Exception('User is not allowed to set this autor as principal');
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

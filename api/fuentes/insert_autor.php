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

    // Extract and validate input data for id_fuente and autor object
    $id_fuente = isset($input['id_fuente']) ? filter_var($input['id_fuente'], FILTER_VALIDATE_INT) : 0;
    $autor = isset($input['autor']) ? $input['autor'] : null;

    $nombre = isset($autor['nombre']) ? $autor['nombre'] : '';
    $apellido = isset($autor['apellido']) ? $autor['apellido'] : '';
    $principal = isset($autor['principal']) ? filter_var($autor['principal'], FILTER_VALIDATE_BOOLEAN) : 0;

    // Validate new parameters, id_fuente and autor object not null
    if ($id_fuente === 0 || $autor === null || $nombre === '' || $apellido === '') {
        throw new Exception('Invalid input parameters');
    }

    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
        error_log('Username from session: ' . $username); // Log username

        $db = new Database($config['servername'], $config['username'], $config['password'], $config['dbname']);
        $conn = $db->getConnection();

        // Check permissions
        $hasPermissions = userHasPermissionsInFuente($username, $id_fuente, $conn);
        error_log('User permissions: ' . ($hasPermissions ? 'Granted' : 'Denied')); // Log permissions

        if ($hasPermissions) {
            // Insert autor into autores table
            $sql = "
                INSERT INTO autores
                (id_fuente, nombre, apellido, principal)
                VALUES (?, ?, ?, ?)
            ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('issi', $id_fuente, $nombre, $apellido, $principal);
            $stmt->execute();

            if ($stmt->error) {
                throw new Exception('Failed' . $stmt->error);
            }

            $response = [
                'success' => true,
                'id_autor' => $stmt->insert_id
            ];

            echo json_encode($response);
        } else {
            throw new Exception('User is not allowed to insert this autor');
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

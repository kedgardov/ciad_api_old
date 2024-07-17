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
    $id_fuente = isset($input['id_fuente']) ? filter_var($input['id_fuente'], FILTER_VALIDATE_INT) : 0;
    $fuente = isset($input['fuente']) ? $input['fuente'] : null;

    // Validate required parameters
    if ($id_fuente === 0 || $fuente === null) {
        throw new Exception('Invalid input parameters');
    }

    // Extract and validate fields from $fuente
    $titulo = isset($fuente['titulo']) ? $fuente['titulo'] : '';
    $fecha_publicacion = isset($fuente['fecha_publicacion']) ? $fuente['fecha_publicacion'] : '';
    $editorial = isset($fuente['editorial']) ? $fuente['editorial'] : '';
    $volumen = isset($fuente['volumen']) ? $fuente['volumen'] : '';
    $numero = isset($fuente['numero']) ? filter_var($fuente['numero'], FILTER_VALIDATE_INT) : null;
    $pagina = isset($fuente['pagina']) ? filter_var($fuente['pagina'], FILTER_VALIDATE_INT) : null;
    $doi = isset($fuente['doi']) ? $fuente['doi'] : '';
    $url = isset($fuente['url']) ? $fuente['url'] : '';
    $fecha_consulta = isset($fuente['fecha_consulta']) ? $fuente['fecha_consulta'] : '';
    $edicion = isset($fuente['edicion']) ? $fuente['edicion'] : '';
    $editor = isset($fuente['editor']) ? $fuente['editor'] : '';
    $nombre_web = isset($fuente['nombre_web']) ? $fuente['nombre_web'] : '';
    $nombre_revista = isset($fuente['nombre_revista']) ? $fuente['nombre_revista'] : '';
    $id_tipo_fuente = isset($fuente['id_tipo_fuente']) ? filter_var($fuente['id_tipo_fuente'], FILTER_VALIDATE_INT) : 0;
    $cita = isset($fuente['cita']) ? $fuente['cita'] : '';

    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
        error_log('Username from session: ' . $username); // Log username

        $db = new Database($config['servername'], $config['username'], $config['password'], $config['dbname']);
        $conn = $db->getConnection();

        $hasPermissions = userHasPermissionsInFuente($username, $id_fuente, $conn);
        error_log('User permissions: ' . ($hasPermissions ? 'Granted' : 'Denied')); // Log permissions

        if ($hasPermissions) {
            $sql = "
            UPDATE fuentes SET
                titulo = ?,
                fecha_publicacion = ?,
                editorial = ?,
                volumen = ?,
                numero = ?,
                pagina = ?,
                doi = ?,
                url = ?,
                fecha_consulta = ?,
                edicion = ?,
                editor = ?,
                nombre_web = ?,
                nombre_revista = ?,
                id_tipo_fuente = ?,
                cita = ?
            WHERE id_fuente = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Preparation of statement failed: ' . $conn->error);
            }

            $stmt->bind_param(
                'ssssiisssssssisi',
                $titulo,
                $fecha_publicacion,
                $editorial,
                $volumen,
                $numero,
                $pagina,
                $doi,
                $url,
                $fecha_consulta,
                $edicion,
                $editor,
                $nombre_web,
                $nombre_revista,
                $id_tipo_fuente,
                $cita,
                $id_fuente
            );
            $stmt->execute();

            if($stmt->error){
                throw new Exception('Failed: ' . $stmt->error);
            }

            echo json_encode(['success' => true]);
        } else {
            throw new Exception('User is not allowed to update this fuente');
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

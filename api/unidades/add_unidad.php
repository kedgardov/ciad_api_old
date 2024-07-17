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
    $numero = isset($input['numero']) ? filter_var($input['numero'], FILTER_VALIDATE_INT) : null;
    $nombre = isset($input['nombre']) ? $input['nombre'] : '';
    $objetivo = isset($input['objetivo']) ? $input['objetivo'] : '';
    $id_habilidad = isset($input['id_habilidad']) ? filter_var($input['id_habilidad'], FILTER_VALIDATE_INT) : null;
    $id_bloom = isset($input['id_bloom']) ? filter_var($input['id_bloom'], FILTER_VALIDATE_INT) : null;
    $id_actividad_presencial = isset($input['id_actividad_presencial']) ? filter_var($input['id_actividad_presencial'], FILTER_VALIDATE_INT) : null;
    $id_actividad_independiente = isset($input['id_actividad_independiente']) ? filter_var($input['id_actividad_independiente'], FILTER_VALIDATE_INT) : null;
    $id_actividad_tarea = isset($input['id_actividad_tarea']) ? filter_var($input['id_actividad_tarea'], FILTER_VALIDATE_INT) : null;
    $descripcion_actividad_presencial = isset($input['descripcion_actividad_presencial']) ? $input['descripcion_actividad_presencial'] : '';
    $descripcion_actividad_independiente = isset($input['descripcion_actividad_independiente']) ? $input['descripcion_actividad_independiente'] : '';
    $descripcion_actividad_tarea = isset($input['descripcion_actividad_tarea']) ? $input['descripcion_actividad_tarea'] : '';
    $evidencia_presencial = isset($input['evidencia_presencial']) ? $input['evidencia_presencial'] : '';
    $evidencia_independiente = isset($input['evidencia_independiente']) ? $input['evidencia_independiente'] : '';
    $evidencia_tarea = isset($input['evidencia_tarea']) ? $input['evidencia_tarea'] : '';
    $id_tipo_habilidad = isset($input['id_tipo_habilidad']) ? filter_var($input['id_tipo_habilidad'], FILTER_VALIDATE_INT) : null;

    // Validate required parameters
    if ($id_curso === false || $numero === false) {
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
                INSERT INTO unidades (
                    id_curso, numero, nombre, objetivo, id_habilidad, id_bloom, id_actividad_presencial, id_actividad_independiente, id_actividad_tarea,
                    descripcion_actividad_presencial, descripcion_actividad_independiente, descripcion_actividad_tarea,
                    evidencia_presencial, evidencia_independiente, evidencia_tarea, id_tipo_habilidad
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Failed to prepare statement: ' . $conn->error);
            }

            $stmt->bind_param('iissiisssssssssi', $id_curso, $numero, $nombre, $objetivo, $id_habilidad, $id_bloom,
                $id_actividad_presencial, $id_actividad_independiente, $id_actividad_tarea, $descripcion_actividad_presencial,
                $descripcion_actividad_independiente, $descripcion_actividad_tarea, $evidencia_presencial,
                $evidencia_independiente, $evidencia_tarea, $id_tipo_habilidad);
            $stmt->execute();

            if ($stmt->error) {
                throw new Exception('Failed: ' . $stmt->error);
            }

            $response = [
                'success' => true,
                'id_unidad' => $stmt->insert_id
            ];

            echo json_encode($response);
        } else {
            throw new Exception('User is not allowed to insert this unidad');
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

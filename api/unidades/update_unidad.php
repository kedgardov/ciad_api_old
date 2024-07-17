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

    // Extract the unidad object
    $unidad = isset($input['unidad']) ? $input['unidad'] : null;
    if (!$unidad) {
        throw new Exception('Invalid input parameters');
    }

    // Extract and validate input data
    $id_unidad = isset($unidad['id_unidad']) ? filter_var($unidad['id_unidad'], FILTER_VALIDATE_INT) : null;
    $id_curso = isset($unidad['id_curso']) ? filter_var($unidad['id_curso'], FILTER_VALIDATE_INT) : null;
    $numero = isset($unidad['numero']) ? filter_var($unidad['numero'], FILTER_VALIDATE_INT) : null;
    $nombre = isset($unidad['nombre']) ? $unidad['nombre'] : '';
    $objetivo = isset($unidad['objetivo']) ? $unidad['objetivo'] : '';
    $id_habilidad = isset($unidad['id_habilidad']) ? filter_var($unidad['id_habilidad'], FILTER_VALIDATE_INT) : null;
    $id_bloom = isset($unidad['id_bloom']) ? filter_var($unidad['id_bloom'], FILTER_VALIDATE_INT) : null;
    $id_actividad_presencial = isset($unidad['id_actividad_presencial']) ? filter_var($unidad['id_actividad_presencial'], FILTER_VALIDATE_INT) : null;
    $id_actividad_independiente = isset($unidad['id_actividad_independiente']) ? filter_var($unidad['id_actividad_independiente'], FILTER_VALIDATE_INT) : null;
    $id_actividad_tarea = isset($unidad['id_actividad_tarea']) ? filter_var($unidad['id_actividad_tarea'], FILTER_VALIDATE_INT) : null;
    $descripcion_actividad_presencial = isset($unidad['descripcion_actividad_presencial']) ? $unidad['descripcion_actividad_presencial'] : '';
    $descripcion_actividad_independiente = isset($unidad['descripcion_actividad_independiente']) ? $unidad['descripcion_actividad_independiente'] : '';
    $descripcion_actividad_tarea = isset($unidad['descripcion_actividad_tarea']) ? $unidad['descripcion_actividad_tarea'] : '';
    $evidencia_presencial = isset($unidad['evidencia_presencial']) ? $unidad['evidencia_presencial'] : '';
    $evidencia_independiente = isset($unidad['evidencia_independiente']) ? $unidad['evidencia_independiente'] : '';
    $evidencia_tarea = isset($unidad['evidencia_tarea']) ? $unidad['evidencia_tarea'] : '';
    $id_tipo_habilidad = isset($unidad['id_tipo_habilidad']) ? filter_var($unidad['id_tipo_habilidad'], FILTER_VALIDATE_INT) : null;

    // Validate required parameters
    if ($id_unidad === false || $id_curso === false || $numero === false) {
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
                UPDATE unidades SET
                    numero = ?, nombre = ?, objetivo = ?, id_habilidad = ?, id_bloom = ?, id_actividad_presencial = ?, id_actividad_independiente = ?, id_actividad_tarea = ?,
                    descripcion_actividad_presencial = ?, descripcion_actividad_independiente = ?, descripcion_actividad_tarea = ?,
                    evidencia_presencial = ?, evidencia_independiente = ?, evidencia_tarea = ?, id_tipo_habilidad = ?
                WHERE id_curso = ? AND id_unidad = ?
            ";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Failed to prepare statement: ' . $conn->error);
            }

            $stmt->bind_param('issiiiiissssssiii', $numero, $nombre, $objetivo, $id_habilidad, $id_bloom,
                $id_actividad_presencial, $id_actividad_independiente, $id_actividad_tarea, $descripcion_actividad_presencial,
                $descripcion_actividad_independiente, $descripcion_actividad_tarea, $evidencia_presencial,
                $evidencia_independiente, $evidencia_tarea, $id_tipo_habilidad, $id_curso, $id_unidad);
            $stmt->execute();

            if ($stmt->error) {
                throw new Exception('Failed: ' . $stmt->error);
            }

            $response = [
                'success' => true
            ];

            echo json_encode($response);
        } else {
            throw new Exception('User is not allowed to update this unidad');
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

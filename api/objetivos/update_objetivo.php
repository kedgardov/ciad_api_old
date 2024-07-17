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

    // Get parameters
    $id_curso = isset($input['id_curso']) ? filter_var($input['id_curso'], FILTER_VALIDATE_INT) : null;
    $objetivoList = isset($input['objetivos']) ? $input['objetivos'] : [];

    // Validate parameters
    if ($id_curso === false || empty($objetivoList)) {
        throw new Exception('Invalid input parameters');
    }

    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];

        $db = new Database($config['servername'], $config['username'], $config['password'], $config['dbname']);
        $conn = $db->getConnection();

        if (userHasPermissionsInCurso($username, $id_curso, $conn)) {
            $conn->begin_transaction();

            $sql = "UPDATE objetivos SET numero = ?, tipo = ?, objetivo = ? WHERE id_objetivo = ? AND id_curso = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Failed to prepare statement: ' . $conn->error);
            }

            foreach ($objetivoList as $objetivo) {
                $id_objetivo = isset($objetivo['id_objetivo']) ? filter_var($objetivo['id_objetivo'], FILTER_VALIDATE_INT) : null;
                $numero = isset($objetivo['numero']) ? filter_var($objetivo['numero'], FILTER_VALIDATE_INT) : null;
                $tipo = isset($objetivo['tipo']) ? $objetivo['tipo'] : null;
                $objetivo_text = isset($objetivo['objetivo']) ? $objetivo['objetivo'] : null;

                if ($id_objetivo === false || $numero === false || !$tipo || !$objetivo_text) {
                    throw new Exception('Invalid objetivo item parameters');
                }

                $stmt->bind_param('issii', $numero, $tipo, $objetivo_text, $id_objetivo, $id_curso);
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
            throw new Exception('User is not allowed to update these objetivos');
        }
    } else {
        // Return unauthorized response if session username is not set
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
    }
} catch (Exception $e) {
    // Handle any exceptions and return an error response
    if (isset($conn) && $conn->in_transaction) {
        $conn->rollback();
    }
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}

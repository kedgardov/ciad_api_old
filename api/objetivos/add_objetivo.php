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

    // Extract and validate input data
    $id_curso = isset($input['id_curso']) ? filter_var($input['id_curso'], FILTER_VALIDATE_INT) : null;
    $numero = isset($input['numero']) ? filter_var($input['numero'], FILTER_VALIDATE_INT) : null;
    $tipo = isset($input['tipo']) ? $input['tipo'] : null;

    // Create the objetivo object with the provided values
    $objetivo = (object)[
        'id_objetivo' => null,
        'id_curso' => $id_curso,
        'tipo' => $tipo,
        'numero' => $numero,
        'objetivo' => ''
    ];

    // Validate the objetivo object properties
    if ($id_curso === false || $numero === false || !$tipo) {
        throw new Exception('Invalid input parameters');
    }

    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];

        $db = new Database($config['servername'], $config['username'], $config['password'], $config['dbname']);
        $conn = $db->getConnection();

        if (userHasPermissionsInCurso($username, $objetivo->id_curso, $conn)) {
            $sql = "
                INSERT INTO objetivos (id_curso, numero, tipo, objetivo)
                VALUES (?, ?, ?, ?)
            ";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Failed to prepare statement: ' . $conn->error);
            }

            $stmt->bind_param('iiss', $objetivo->id_curso, $objetivo->numero, $objetivo->tipo, $objetivo->objetivo);
            $stmt->execute();

            if ($stmt->error) {
                throw new Exception('Failed: ' . $stmt->error);
            }

            $response = [
                'success' => true,
                'id_objetivo' => $stmt->insert_id
            ];

            echo json_encode($response);
        } else {
            throw new Exception('User is not allowed to insert this objetivo');
        }
    } else {
        // Return unauthorized response if session username is not set
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
    }
} catch (Exception $e) {
    // Handle any exceptions and return an error response
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}
?>

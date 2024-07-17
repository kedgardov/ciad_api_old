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
$config = include '../../config/db_config.php';



session_start();

try {
    // Decode input data
    $input = json_decode(file_get_contents('php://input'), true);

    $id_curso = isset($input['id_curso']) ? filter_var($input['id_curso'], FILTER_VALIDATE_INT) : null;
    $id_coordinacion = isset($input['id_coordinacion']) ? filter_var($input['id_coordinacion'], FILTER_VALIDATE_INT) : null;

    if ($id_curso === false || $id_coordinacion === false || $id_curso <= 0 || $id_coordinacion <= 0) {
        throw new Exception('Invalid input parameters');
    }

    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];

        $db = new Database($config['servername'], $config['username'], $config['password'], $config['dbname']);
        $conn = $db->getConnection();

        $sql = "
            INSERT INTO coordinaciones_cursos
            (id_curso, id_coordinacion)
            VALUES (?, ?)
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $id_curso, $id_coordinacion);
        $stmt->execute();

        if ($stmt->error) {
            throw new Exception('Failed to insert coordinacion: ' . $stmt->error);
        }

        $response = [
            'success' => true,
        ];

        echo json_encode($response);
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

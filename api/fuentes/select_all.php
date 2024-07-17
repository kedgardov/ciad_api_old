<?php

// Enable CORS
header('Access-Control-Allow-Origin: '. getenv('ORIGIN_PATH'));
header('Access-Control-Allow-Methods: GET, OPTIONS');
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
    // Retrieve id from the query string
    if (!isset($_GET['id_curso'])) {
        throw new Exception('Missing id parameter');
    }

    $id_curso = intval($_GET['id_curso']);

    // Check if user is authenticated
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];

        // Create database connection
        $db = new Database($config['servername'], $config['username'], $config['password'], $config['dbname']);
        $conn = $db->getConnection();

        if (userHasPermissionsInCurso($username, $id_curso, $conn)) {
            // Fetch fuentes
            $sql1 = "SELECT * FROM fuentes WHERE id_curso = ?";
            $stmt = $conn->prepare($sql1);
            $stmt->bind_param('i', $id_curso);
            $stmt->execute();
            $result = $stmt->get_result();

            $fuentes = [];
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $fuentes[] = $row;
                }
            }
            $stmt->close();

            // Fetch tipos_fuentes
            $sql2 = "SELECT * FROM catalogo_tipos_fuentes";
            $result = $conn->query($sql2);

            $tipos_fuentes = [];
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $tipos_fuentes[] = $row;
                }
            }

            // Fetch autores
            $sql3 = "
            SELECT autores.*
            FROM autores
            INNER JOIN fuentes ON autores.id_fuente = fuentes.id_fuente
            WHERE fuentes.id_curso = ?";
            $stmt = $conn->prepare($sql3);
            $stmt->bind_param('i', $id_curso);
            $stmt->execute();
            $result = $stmt->get_result();

            $autores = [];
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $autores[] = $row;
                }
            }
            $stmt->close();

            echo json_encode(['fuentes' => $fuentes, 'tipos_fuentes' => $tipos_fuentes, 'autores' => $autores]);
        } else {
            throw new Exception('User is not allowed to see these fuentes');
        }

    } else {
        // Return unauthorized or empty response if session username is not set
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
    }
} catch (Exception $e) {
    // Handle any exceptions and return an error response
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}
?>

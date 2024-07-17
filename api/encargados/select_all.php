<?php
// Enable CORS and other headers
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
    if (!isset($_SESSION['username'])) {
        throw new Exception('Unauthorized');
    }

    $username = $_SESSION['username'];
    $id_curso = isset($_GET['id_curso']) ? filter_var($_GET['id_curso'], FILTER_VALIDATE_INT) : null;

    if ($id_curso === false || $id_curso <= 0) {
        throw new Exception('Invalid input parameters');
    }

    $db = new Database($config['servername'], $config['username'], $config['password'], $config['dbname']);
    $conn = $db->getConnection();

    if (userHasPermissionsInCurso($username, $id_curso, $conn)) {
        $sql1 = "SELECT roles_cursos.id_rol_curso, roles_cursos.id_docente,
                 roles_cursos.id_rol, rol,
                 CONCAT(maestros.grado, ' ', maestros.nombre, ' ', maestros.apellido) AS nombre
                 FROM maestros
                 INNER JOIN roles_cursos ON maestros.id = roles_cursos.id_docente
                 INNER JOIN catalogo_roles ON catalogo_roles.id_rol = roles_cursos.id_rol
                 WHERE roles_cursos.id_curso = ?";
        $sql2 = 'SELECT * FROM catalogo_roles';
        $sql3 = 'SELECT id AS id_docente, CONCAT(grado, " ", nombre, " ", apellido) AS nombre FROM maestros';

        // Execute first query
        $stmt1 = $conn->prepare($sql1);
        if (!$stmt1) {
            throw new Exception('Preparation of statement failed: ' . $conn->error);
        }
        $stmt1->bind_param('i', $id_curso);
        $stmt1->execute();
        $result1 = $stmt1->get_result();

        $encargados = [];
        if ($result1->num_rows > 0) {
            while ($row = $result1->fetch_assoc()) {
                $encargados[] = $row;
            }
        }
        $stmt1->close();

        // Execute second query
        $result2 = $conn->query($sql2);
        $roles = [];
        if ($result2 && $result2->num_rows > 0) {
            while ($row = $result2->fetch_assoc()) {
                $roles[] = $row;
            }
        }

        // Execute third query
        $result3 = $conn->query($sql3);
        $docentes = [];
        if ($result3 && $result3->num_rows > 0) {
            while ($row = $result3->fetch_assoc()) {
                $docentes[] = $row;
            }
        }

        // Return combined results
        echo json_encode([
            'encargados' => $encargados,
            'roles' => $roles,
            'docentes' => $docentes
        ]);

    } else {
        echo json_encode(['success' => false, 'message' => 'User does not have permission']);
    }

    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}
?>

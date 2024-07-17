<?php

// Enable CORS
header('Access-Control-Allow-Origin: '. getenv('ORIGIN_PATH'));
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Set content type to JSON
header('Content-Type: application/json');

require_once '../../classes/Database.php';
require_once '../../classes/User.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

function getCursosByUsername($conn, $username) {
    $sql = "
        SELECT cursos.id_curso, cursos.nombre, cursos.clave, catalogo_roles.rol
        FROM cursos
        INNER JOIN roles_cursos ON cursos.id_curso = roles_cursos.id_curso
        INNER JOIN users ON users.id_docente = roles_cursos.id_docente
        INNER JOIN catalogo_roles ON roles_cursos.id_rol = catalogo_roles.id_rol
        WHERE users.username = ?;
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $cursos = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $cursos;
}

function getCursosAdmin($conn) {
    $sql = "
        SELECT cursos.id_curso, cursos.nombre, cursos.clave, catalogo_roles.rol
        FROM cursos
        INNER JOIN roles_cursos ON cursos.id_curso = roles_cursos.id_curso
        INNER JOIN users ON users.id_docente = roles_cursos.id_docente
        INNER JOIN catalogo_roles ON roles_cursos.id_rol = catalogo_roles.id_rol
        WHERE 1;
    ";
    $stmt = $conn->prepare($sql);
    //$stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $cursos = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    foreach ($cursos as &$curso) {
        $curso['rol'] = 'Admin';
    }
    return $cursos;
}

// Load database configuration
$config = include '../../config/db_config.php';

try {
    // Create a new Database instance
    $db = new Database($config['servername'], $config['username'], $config['password'], $config['dbname']);
    $user = new User($db);

    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input === null || !isset($input['username']) || !isset($input['token'])) {
        throw new Exception('Invalid input data');
    }

    $username = $input['username'];
    $token = $input['token'];

    // Verify user token
    if (!$user->verifyToken($username, $token)) {
        throw new Exception('Invalid token');
    }

    if($username === 'admin'){
        $sidebar_cursos = getCursosAdmin($db->getConnection());
    }else{
        $sidebar_cursos = getCursosByUsername($db->getConnection(), $username);
    }

    // Return the data as JSON
    echo json_encode(['sidebar_cursos' => $sidebar_cursos]);
} catch (Exception $e) {
    // Handle any exceptions and return an error response
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred while processing your request.', 'message' => $e->getMessage()]);
    error_log($e->getMessage());
}
?>

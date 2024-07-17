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
            // Begin transaction
            $conn->begin_transaction();

            try {
                // Fetch opciones_terminales
                $stmt1 = $conn->prepare("
                    SELECT otc.*, cm.modalidad, cp.programa, cot.opcion_terminal
                    FROM opciones_terminales_cursos AS otc
                    INNER JOIN catalogo_opciones_terminales AS cot ON otc.id_opcion_terminal = cot.id_opcion_terminal
                    INNER JOIN catalogo_programas AS cp ON cp.id_programa = otc.id_programa
                    INNER JOIN catalogo_modalidades AS cm ON cm.id_modalidad = otc.id_modalidad
                    WHERE otc.id_curso = ?
                ");
                $stmt1->bind_param('i', $id_curso);
                $stmt1->execute();
                $result1 = $stmt1->get_result();
                $opciones_terminales = $result1->fetch_all(MYSQLI_ASSOC);
                $stmt1->close();

                // Fetch lgacs
                $stmt2 = $conn->prepare("
                    SELECT lgacs_c.*, cm.modalidad, cp.programa, c_lgacs.lgac
                    FROM lgacs_cursos AS lgacs_c
                    INNER JOIN catalogo_lgacs AS c_lgacs ON lgacs_c.id_lgac = c_lgacs.id_lgac
                    INNER JOIN catalogo_programas AS cp ON cp.id_programa = lgacs_c.id_programa
                    INNER JOIN catalogo_modalidades AS cm ON cm.id_modalidad = lgacs_c.id_modalidad
                    WHERE lgacs_c.id_curso = ?
                ");
                $stmt2->bind_param('i', $id_curso);
                $stmt2->execute();
                $result2 = $stmt2->get_result();
                $lgacs = $result2->fetch_all(MYSQLI_ASSOC);
                $stmt2->close();

                // Fetch catalogo_lgacs
                $stmt3 = $conn->prepare("SELECT * FROM catalogo_lgacs");
                $stmt3->execute();
                $result3 = $stmt3->get_result();
                $catalogo_lgacs = $result3->fetch_all(MYSQLI_ASSOC);
                $stmt3->close();

                // Fetch catalogo_opciones_terminales
                $stmt4 = $conn->prepare("SELECT * FROM catalogo_opciones_terminales");
                $stmt4->execute();
                $result4 = $stmt4->get_result();
                $catalogo_opciones_terminales = $result4->fetch_all(MYSQLI_ASSOC);
                $stmt4->close();

                // Fetch catalogo_programas
                $stmt5 = $conn->prepare("SELECT * FROM catalogo_programas");
                $stmt5->execute();
                $result5 = $stmt5->get_result();
                $catalogo_programas = $result5->fetch_all(MYSQLI_ASSOC);
                $stmt5->close();

                $stmt6 = $conn->prepare("SELECT * FROM catalogo_modalidades");
                $stmt6->execute();
                $result6 = $stmt6->get_result();
                $catalogo_modalidades = $result6->fetch_all(MYSQLI_ASSOC);
                $stmt6->close();

                // Commit transaction
                $conn->commit();

                // Return data as JSON
                echo json_encode([
                    'success' => true,
                    'opciones_terminales' => $opciones_terminales,
                    'lgacs' => $lgacs,
                    'catalogo_lgacs' => $catalogo_lgacs,
                    'catalogo_opciones_terminales' => $catalogo_opciones_terminales,
                    'catalogo_programas' => $catalogo_programas,
                    'catalogo_modalidades' => $catalogo_modalidades,
                ]);

            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollback();
                throw $e;
            }
        } else {
            throw new Exception('User is not allowed to see these facultades');
        }

    } else {
        // Return unauthorized or empty response if session username is not set
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized','success' => false]);
    }
} catch (Exception $e) {
    // Handle any exceptions and return an error response
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage(),
                      'success' => false]);
}
?>

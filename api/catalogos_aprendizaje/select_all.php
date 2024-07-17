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
    // Check if user is authenticated
    if (isset($_SESSION['username'])) {

        $db = new Database($config['servername'], $config['username'], $config['password'], $config['dbname']);
        $conn = $db->getConnection();

        // Query 1: catalogo_actividades
        $sql1 = "SELECT * FROM catalogo_actividades";
        $result1 = $conn->query($sql1);
        $actividades = [];
        if ($result1->num_rows > 0) {
            while ($row1 = $result1->fetch_assoc()) {
                $actividades[] = $row1;
            }
        }

        // Query 2: catalogo_habilidades
        $sql2 = "SELECT * FROM catalogo_habilidades";
        $result2 = $conn->query($sql2);
        $habilidades = [];
        if ($result2->num_rows > 0) {
            while ($row2 = $result2->fetch_assoc()) {
                $habilidades[] = $row2;
            }
        }

        // Query 3: catalogo_tax_bloom
        $sql3 = "SELECT * FROM catalogo_tax_bloom";
        $result3 = $conn->query($sql3);
        $taxBloom = [];
        if ($result3->num_rows > 0) {
            while ($row3 = $result3->fetch_assoc()) {
                $taxBloom[] = $row3;
            }
        }

        // Query 4: catalogo_tipos_habilidades
        $sql4 = "SELECT * FROM catalogo_tipos_habilidades";
        $result4 = $conn->query($sql4);
        $tiposHabilidades = [];
        if ($result4->num_rows > 0) {
            while ($row4 = $result4->fetch_assoc()) {
                $tiposHabilidades[] = $row4;
            }
        }


        // Return combined result
        echo json_encode([
            'actividades' => $actividades,
            'habilidades' => $habilidades,
            'taxBloom' => $taxBloom,
            'tiposHabilidades' => $tiposHabilidades
        ]);

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

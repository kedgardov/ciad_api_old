<?php
function isTrue(){
    return true;
}

function userHasPermissionsInCurso($username, $id_curso, $conn) {
    $sql = "SELECT rol FROM catalogo_roles
            INNER JOIN roles_cursos ON catalogo_roles.id_rol = roles_cursos.id_rol
            INNER JOIN maestros ON maestros.id = roles_cursos.id_docente
            WHERE maestros.usuario = ? AND roles_cursos.id_curso = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Preparation of statement failed: ' . $conn->error);
    }

    $stmt->bind_param('si', $username, $id_curso);
    $stmt->execute();
    if ($stmt->error) {
        throw new Exception('userHasPermissionsInCurso tuvo un problema: ' . $stmt->error);
    }

    $result = $stmt->get_result();

    if ($result === false) {
        throw new Exception('Error getting result: ' . $stmt->error);
    }

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if ($row['rol'] === 'Titular' or $row['rol' === 'Responsable']) {
            return true;
        }
    }

    return false;
}


function userHasPermissionsInUnidad($username, $id_unidad, $conn) {
    $sql = "SELECT rol FROM catalogo_roles
            INNER JOIN roles_cursos ON catalogo_roles.id_rol = roles_cursos.id_rol
            INNER JOIN maestros ON maestros.id = roles_cursos.id_docente
            INNER JOIN unidades ON roles_cursos.id_curso = unidades.id_curso
            WHERE maestros.usuario = ? AND unidades.id_unidad = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Preparation of statement failed: ' . $conn->error);
    }

    $stmt->bind_param('si', $username, $id_unidad);
    $stmt->execute();
    if ($stmt->error) {
        throw new Exception('userHasPermissionsInUnidad tuvo un problema: ' . $stmt->error);
    }

    $result = $stmt->get_result();

    if ($result === false) {
        throw new Exception('Error getting result: ' . $stmt->error);
    }

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if ($row['rol'] === 'Titular' or $row['rol'] === 'Responsable') {
            return true;
        }
    }

    return false;
}


function userHasPermissionsInFuente($username, $id_fuente, $conn) {
    $sql = "SELECT rol FROM catalogo_roles
            INNER JOIN roles_cursos ON catalogo_roles.id_rol = roles_cursos.id_rol
            INNER JOIN maestros ON maestros.id = roles_cursos.id_docente
            INNER JOIN fuentes ON roles_cursos.id_curso = fuentes.id_curso
            WHERE maestros.usuario = ? AND fuentes.id_fuente = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('Preparation of statement failed: ' . $conn->error);
        throw new Exception('Preparation of statement failed: ' . $conn->error);
    }

    $stmt->bind_param('si', $username, $id_fuente);
    $stmt->execute();
    if ($stmt->error) {
        error_log('userHasPermissionsInFuente encountered a problem: ' . $stmt->error);
        throw new Exception('userHasPermissionsInFuente encountered a problem: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result === false) {
        error_log('Error getting result: ' . $stmt->error);
        throw new Exception('Error getting result: ' . $stmt->error);
    }

    $hasPermission = false;
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if ($row['rol'] === 'Titular' or $row['rol'] === 'Responsable') {
            $hasPermission = true;
        }
    }

    $stmt->close();
    return $hasPermission;
}


?>

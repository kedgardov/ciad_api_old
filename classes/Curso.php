<?php

require_once 'General.php';
require_once 'dynamic/DynamicGetSet.php';

class Curso extends General {
    private int $id;
    private string $curso_clave;
    private string $curso_nombre;
    private ?string $curso_name;
    private ?string $curso_modalidad;
    private ?string $curso_coordinacion;
    private ?int $curso_horas_semestre;
    private ?int $curso_creditos;
    private ?string $curso_objetivo_general;
    private ?string $curso_vinculo_perfil_egreso;

    public function __construct(Database $db) {
        parent::__construct($db, 'cursos', [
            'curso_clave', 'curso_nombre', 'curso_name',
            'curso_modalidad', 'curso_coordinacion', 'curso_horas_semestre',
            'curso_creditos', 'curso_objetivo_general', 'curso_vinculo_perfil_egreso'
        ]);
    }

    use DynamicGetSet;


    public function selectAllWhereUsername($username) {
    $sql = "SELECT cursos.*
            FROM cursos
            INNER JOIN roles_cursos ON cursos.id_curso = roles_cursos.id_curso
            INNER JOIN maestros ON roles_cursos.id_docente = maestros.id
            WHERE maestros.usuario = ?";

    $stmt = $this->db->getConnection()->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }

    return $courses;
    }


    // public function selectAllWhereUsername($username) {
    // $sql = "SELECT cursos.*
    //         FROM cursos
    //         INNER JOIN roles_cursos ON cursos.id_curso = roles_cursos.id_curso
    //         INNER JOIN users ON roles_cursos.id_docente = users.id_docente
    //         WHERE users.username = ?";

    // $stmt = $this->db->getConnection()->prepare($sql);
    // $stmt->bind_param("s", $username);
    // $stmt->execute();
    // $result = $stmt->get_result();

    // $courses = [];
    // while ($row = $result->fetch_assoc()) {
    //     $courses[] = $row;
    // }

    // return $courses;
    // }


    public function selectAllWhereUsernameAndId($username,$id) {
    $sql = "SELECT cursos.*
            FROM cursos
            INNER JOIN roles_cursos ON cursos.id_curso = roles_cursos.id_curso
            INNER JOIN maestros ON roles_cursos.id_docente = maestros.id
            WHERE maestros.usuario = ? AND cursos.id_curso = ?";

    $stmt = $this->db->getConnection()->prepare($sql);
    $stmt->bind_param("si", $username, $id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc();
    }


    // public function selectAllWhereUsernameAndId($username,$id) {
    // $sql = "SELECT cursos.*
    //         FROM cursos
    //         INNER JOIN roles_cursos ON cursos.id_curso = roles_cursos.id_curso
    //         INNER JOIN users ON roles_cursos.id_docente = users.id_docente
    //         WHERE users.username = ? AND cursos.id_curso = ?";

    // $stmt = $this->db->getConnection()->prepare($sql);
    // $stmt->bind_param("si", $username, $id);
    // $stmt->execute();
    // $result = $stmt->get_result();

    // return $result->fetch_assoc();
    // }

    public function updateCurso($data)
    {
        // Extract curso data from payload
        $id_curso = $data['id_curso'];
        $clave = $data['clave'];
        $nombre = $data['nombre'];
        $nombre_ingles = $data['nombre_ingles'];
        $horas_teoricas = $data['horas_teoricas'];
        $horas_practicas = $data['horas_practicas'];
        $horas_independientes = $data['horas_independientes'];
        $horas_semana = $data['horas_semana'];
        $horas_semestre = $data['horas_semestre'];
        $vinculo_objetivos_posgrado = $data['vinculo_objetivos_posgrado'];

        // Construct and execute SQL query
        $sql = "UPDATE cursos SET
                    clave = ?,
                    nombre = ?,
                    nombre_ingles = ?,
                    horas_teoricas = ?,
                    horas_practicas = ?,
                    horas_independientes = ?,
                    horas_semana = ?,
                    horas_semestre = ?,
                    vinculo_objetivos_posgrado = ?
                WHERE id_curso = ?";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bind_param("sssiiiiisi", $clave, $nombre, $nombre_ingles, $horas_teoricas, $horas_practicas, $horas_independientes, $horas_semana, $horas_semestre, $vinculo_objetivos_posgrado, $id_curso);
        $stmt->execute();

        // Check for errors
        if ($stmt->error) {
            throw new Exception('Failed to update curso: ' . $stmt->error);
        }

        // Close statement
        $stmt->close();

        // Return true if update was successful
        return true;
    }

    
}
?>

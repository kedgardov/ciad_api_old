<?php

require_once 'General.php';

class Colaborador extends General {
    private int $id_rol_curso;
    private int $id_rol;
    private int $id_curso;
    private int $id_docente;

    public function __construct(Database $db) {
        parent::__construct($db, 'roles_cursos', ['id_rol', 'id_curso', 'id_docente']);
    }
}

?>

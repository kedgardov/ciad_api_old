<?php

require_once 'General.php';

class Docente extends General {
    private int $id_docente;
    private string $nombre;
    private ?string $correo;
    private ?string $telefono;
    private ?string $info;

    public function __construct(Database $db) {
        parent::__construct($db, 'catalogo_docentes', ['nombre', 'correo', 'telefono', 'info']);
    }
}

?>

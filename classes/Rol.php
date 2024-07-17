<?php

require_once 'General.php';

class Rol extends General {
    private int $id_rol;
    private string $rol;

    public function __construct(Database $db) {
        parent::__construct($db, 'catalogo_roles', ['rol']);
    }
}

?>

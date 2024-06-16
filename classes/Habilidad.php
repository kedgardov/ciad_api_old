<?php

require_once 'General.php';

class Habilidad extends General {
    private int $id;
    private string $tipo;
    private string $habilidad;

    public function __construct(Database $db) {
        parent::__construct($db, 'catalogo_habilidades', [
            'id_habilidad',
            'tipo',
            'habilidad'
        ]);
    }
}
?>

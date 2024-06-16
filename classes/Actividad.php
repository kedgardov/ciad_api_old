<?php

require_once 'General.php';

class Actividad extends General {
    private int $id;
    private int $id_habilidad;
    private string $actividad;
    private string $descripcion;

    public function __construct(Database $db) {
        parent::__construct($db, 'catalogo_actividades', [
            'id_actividad',
            'id_habilidad',
            'actividad',
            'descripcion'
        ]);
    }
}
?>

<?php

require_once 'General.php';

class Unidad extends General {
    private int $id_unidad;
    private int $id_curso;
    private int $numero;
    private string $nombre;
    private string $objetivo;
    private int $id_habilidad;
    private int $id_bloom;
    private string $actividad_sincro;
    private string $actividad_asincro;
    private string $evidencia_sincro;
    private string $evidencia_asincro;
    private int $horas_sesion;

    public function __construct(Database $db) {
        parent::__construct($db, 'unidades', [
            'id_unidad',
            'id_curso',
            'numero',
            'nombre',
            'objetivo',
            'id_habilidad',
            'id_bloom',
            'actividad_sincro',
            'actividad_asincro',
            'evidencia_sincro',
            'evidencia_asincro',
            'horas_sesion'
        ]);
    }
}
?>

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

    // Additional methods specific to Curso can be added here
}
?>

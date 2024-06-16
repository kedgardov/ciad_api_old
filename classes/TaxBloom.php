<?php

require_once 'General.php';
require_once 'dynamic/DynamicGetSet.php';

class TaxBloom extends General {
    private int $id;
    private int $id_habilidad;
    private int $verbo;

    public function __construct(Database $db) {
        parent::__construct($db, 'catalogo_tax_bloom', [
          'id_bloom',
          'id_habilidad',
          'verbo'
        ]);
    }

    use DynamicGetSet;

    // Additional methods specific to Curso can be added here
}
?>

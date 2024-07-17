<?php

require_once 'General.php';

class Fuente extends General {
    private int $id_fuente;
    private int $id_curso;
    private ?string $tipo;
    private ?string $titulo;
    private ?int $fecha_publicacion;
    private ?string $editorial;
    private ?int $volumen;
    private ?int $numero;
    private ?int $pagina;
    private ?string $doi;
    private ?string $url;
    private ?string $fecha_consulta;
    private ?int $edicion;
    private ?string $editor;
    private ?string $nombre_web;
    private ?string $nombre_revista;
    private ?string $cita;

    public function __construct(Database $db) {
        parent::__construct($db, 'fuentes', [
            'id_curso', 'tipo', 'titulo', 'fecha_publicacion', 'editorial',
            'volumen', 'numero', 'pagina', 'doi', 'url', 'fecha_consulta',
            'edicion', 'editor', 'nombre_web', 'nombre_revista', 'cita'
        ]);
    }

    public function updateById($idField, $fuente): bool {
        try {
            $data = [
                'id_curso' => $fuente->id_curso,
                'tipo' => $fuente->tipo,
                'titulo' => $fuente->titulo,
                'fecha_publicacion' => $fuente->fecha_publicacion,
                'editorial' => $fuente->editorial,
                'volumen' => $fuente->volumen,
                'numero' => $fuente->numero,
                'pagina' => $fuente->pagina,
                'doi' => $fuente->doi,
                'url' => $fuente->url,
                'fecha_consulta' => $fuente->fecha_consulta,
                'edicion' => $fuente->edicion,
                'editor' => $fuente->editor,
                'nombre_web' => $fuente->nombre_web,
                'nombre_revista' => $fuente->nombre_revista,
                'cita' => $fuente->cita
            ];

            $setClauses = [];
            $values = [];
            $types = '';

            foreach ($data as $field => $value) {
                if ($value !== null) { // Only update fields that are not null
                    $setClauses[] = "$field = ?";
                    $values[] = $value;
                    $types .= $this->getType($value);
                }
            }

            $setClause = implode(', ', $setClauses);
            $values[] = $fuente->id_fuente;
            $types .= 'i'; // Assuming id_fuente is an integer

            $sql = "UPDATE $this->table SET $setClause WHERE $idField = ?";
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception('Failed to prepare statement: ' . $conn->error);
            }

            $stmt->bind_param($types, ...$values);
            $result = $stmt->execute();

            if (!$result) {
                throw new Exception('Database error: ' . $stmt->error);
            }

            $stmt->close();
            return $result;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

        private function getType($value): string {
        switch (gettype($value)) {
            case 'integer':
                return 'i';
            case 'double':
                return 'd';
            case 'string':
                return 's';
            case 'boolean':
                return 'i'; // MySQL has no BOOLEAN type, typically represented as TINYINT
            case 'NULL':
                return 's'; // NULL is usually represented by an empty string or as per database default
            default:
                return 's';
        }
    }




}
?>

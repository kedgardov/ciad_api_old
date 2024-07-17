<?php

require_once 'General.php';

class Criterio extends General {
    private int $id_criterio;
    private string $criterio;
    private float $valor;

    public function __construct(Database $db) {
        parent::__construct($db, 'criterios', [
            'criterio', 'valor', 'id_curso'
        ]);
    }

    public function insert(array $data): bool {
        try {
            $data = $this->sanitizeData($data);
            $columns = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            $types = $this->getParamTypes($data);
            $values = array_values($data);

            $sql = "INSERT INTO $this->table ($columns) VALUES ($placeholders)";
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
            echo json_encode([
                'error' => 'Database insert error',
                'message' => $e->getMessage()
            ]);
            return false;
        }
    }

    private function getParamTypes(array $data): string {
        $types = '';
        foreach ($data as $value) {
            $types .= $this->getType($value);
        }
        return $types;
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

    private function sanitizeData(array $data): array {
        $sanitizedData = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $this->fields)) {
                $sanitizedData[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        }
        return $sanitizedData;
    }
}
?>

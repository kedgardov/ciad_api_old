<?php

class General {
    protected string $table;
    protected array $fields;
    protected Database $db;

    public function __construct(Database $db, string $table, array $fields) {
        $this->db = $db;
        $this->table = $table;
        $this->fields = $fields;
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
            return false;
        }
    }

    public function delete(int $id): bool {
        try {
            $sql = "DELETE FROM $this->table WHERE id = ?";
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Failed to prepare statement: ' . $conn->error);
            }
            $stmt->bind_param('i', $id);
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

    public function update(int $id, array $data): bool {
        try {
            $data = $this->sanitizeData($data);
            $fields = [];
            $types = "";
            $values = [];

            foreach ($data as $key => $value) {
                $fields[] = "$key = ?";
                $types .= $this->getType($value);
                $values[] = $value;
            }
            $values[] = $id;
            $types .= "i";

            $conn = $this->db->getConnection();
            $sql = "UPDATE $this->table SET " . implode(", ", $fields) . " WHERE id = ?";
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

    public function selectOne(int $id, string $target_field): ?array {
        try {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("SELECT * FROM $this->table WHERE $target_field = ?");
            if (!$stmt) {
                throw new Exception('Failed to prepare statement: ' . $conn->error);
            }
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $record = $result->fetch_assoc();
            $stmt->close();
            return $record ?: null;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public function selectAll(): array {
        try {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("SELECT * FROM $this->table");
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
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

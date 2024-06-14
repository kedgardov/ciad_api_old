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
    public function deleteById(string $idField,int $id): bool {
        try {
            $sql = "DELETE FROM $this->table WHERE $idField = ?";
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

    public function updateByField(int $id, string $field, $data, string $targetField): bool {
        try {
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
            $field = htmlspecialchars($field, ENT_QUOTES, 'UTF-8');
            $targetField = htmlspecialchars($targetField, ENT_QUOTES, 'UTF-8');

            $dataType = $this->getType($data);
            $idType = $this->getType($id);

            $conn = $this->db->getConnection();
            $sql = "UPDATE $this->table SET $field = ? WHERE $targetField = ?";

            error_log("SQL: $sql");
            error_log("Types: $dataType$idType");
            error_log("Values: $data, $id");

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Failed to prepare statement: ' . $conn->error);
            }

            $stmt->bind_param($dataType . $idType, $data, $id);
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


    public function update(int $id, string $field, $data, string $target_field = 'id'): bool {
        try {
            // Sanitize the inputs
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
            $field = htmlspecialchars($field, ENT_QUOTES, 'UTF-8');
            $target_field = htmlspecialchars($target_field, ENT_QUOTES, 'UTF-8');

            // Get the type for the data and id
            $dataType = $this->getType($data);
            $idType = $this->getType($id);

            // Connect to the database
            $conn = $this->db->getConnection();
            $sql = "UPDATE $this->table SET $field = ? WHERE $target_field = ?";

            // Log the query and values for debugging
            error_log("SQL: $sql");
            error_log("Types: $dataType$idType");
            error_log("Values: $data, $id");

            // Prepare the statement
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Failed to prepare statement: ' . $conn->error);
            }

            // Bind the parameters
            $stmt->bind_param($dataType . $idType, $data, $id);

            // Execute the statement
            $result = $stmt->execute();
            if (!$result) {
                throw new Exception('Database error: ' . $stmt->error);
            }

            // Close the statement
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

    public function deleteWithCondition(array $conditions): bool {
        try {
            $conditionStrings = [];
            $values = [];
            $types = '';
            foreach ($conditions as $field => $value) {
                $conditionStrings[] = "$field = ?";
                $values[] = $value;
                $types .= $this->getType($value);
            }
            $conditionString = implode(' AND ', $conditionStrings);

            $sql = "DELETE FROM $this->table WHERE $conditionString";
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

    public function selectAllWhereId(string $field, int $value): array {
    try {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM $this->table WHERE $field = ?");
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $conn->error);
        }
        $stmt->bind_param('i', $value);
        $stmt->execute();
        $result = $stmt->get_result();
        $records = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $records;
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

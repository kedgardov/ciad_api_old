<?php

require_once 'General.php';
require_once 'dynamic/DynamicGetSet.php';

class Objetivo extends General {
    private int $id_objetivo;
    private int $id_curso;
    private string $tipo_objetivo;
    private ?int $numero;
    private ?string $objetivo;

    public function __construct(Database $db) {
        parent::__construct($db, 'objetivos', [
            'id_objetivo', 'id_curso', 'tipo', 'numero', 'objetivo'
        ]);
    }

    use DynamicGetSet;

    public function updateNumero(int $id, int $numero): bool {
        return $this->updateByField($id, 'numero', $numero, 'id_objetivo');
    }

public function updateById(int $id, string $field, $data): bool {
    try {
        // Sanitize the inputs
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        $field = htmlspecialchars($field, ENT_QUOTES, 'UTF-8');

        // Connect to the database
        $conn = $this->db->getConnection();
        $sql = "UPDATE $this->table SET $field = ? WHERE id_objetivo = ?";

        // Log the query and values for debugging
        error_log("SQL: $sql");
        error_log("Values: $data, $id");

        // Prepare the statement
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $conn->error);
        }

        // Bind the parameters
        if (!$stmt->bind_param('si', $data, $id)) {
            throw new Exception('Failed to bind parameters: ' . $stmt->error);
        }

        // Execute the statement
        if (!$stmt->execute()) {
            throw new Exception('Failed to execute statement: ' . $stmt->error);
        }

        // Close the statement
        $stmt->close();
        return true;
    } catch (Exception $e) {
        error_log($e->getMessage());
        error_log("Exception trace: " . $e->getTraceAsString());
        return false;
    }
}

    // Method to select all objetivos for a given curso
    public function selectAllWhereId(string $field, int $value): array {
        try {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("SELECT * FROM $this->table WHERE $field = ?");
            $stmt->bind_param("i", $value);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }
}

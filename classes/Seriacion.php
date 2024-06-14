<?php

require_once 'General.php';

class Seriacion extends General {
    private int $id_seriacion;
    private int $id_curso;
    private int $id_requisito;

    public function __construct(Database $db) {
        parent::__construct($db, 'seriaciones', [
            'id_curso', 'id_requisito'
        ]);
    }
    public function deleteByCursoAndRequisito($id_curso, $id_requisito): bool {
        try {
            $sql = "DELETE FROM $this->table WHERE id_curso = ? AND id_requisito = ?";
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Failed to prepare statement: ' . $conn->error);
            }
            $stmt->bind_param('ii', $id_curso, $id_requisito);
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
    // Additional methods specific to Seriacion can be added here
}
?>

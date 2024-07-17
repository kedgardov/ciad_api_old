<?php
class User {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // public function checkCredentials($username, $password) {
    //     $password = md5($password); // Encrypt the password using MD5
    //     $stmt = $this->db->getConnection()->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    //     $stmt->bind_param("ss", $username, $password);
    //     $stmt->execute();
    //     $result = $stmt->get_result();
    //     return $result->num_rows > 0;
    // }

    public function checkCredentials($username, $password) {
        $password = md5($password); // Encrypt the password using MD5
        $stmt = $this->db->getConnection()->prepare("SELECT * FROM maestros WHERE usuario = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }


    public function getUserName($username) {
        $stmt = $this->db->getConnection()->prepare("SELECT grado, nombre, apellido FROM maestros WHERE usuario = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['grado'] . ' ' . $row['nombre'] . ' ' . $row['apellido'];
        } else {
            return null;
        }
    }


    // public function getUserName($username) {
    //     $stmt = $this->db->getConnection()->prepare("SELECT name FROM users WHERE username = ?");
    //     $stmt->bind_param("s", $username);
    //     $stmt->execute();
    //     $result = $stmt->get_result();
    //     if ($result->num_rows > 0) {
    //         return $result->fetch_assoc();
    //     } else {
    //         return null;
    //     }
    // }

    public function verifyToken($username, $token) {
        $sql = "SELECT token FROM users WHERE username = ? LIMIT 1";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($storedToken);
        $stmt->fetch();
        $stmt->close();
        return ($token === $storedToken);
    }

    public function storeToken($username, $token) {
        $sql = "UPDATE users SET token = ? WHERE username = ?";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bind_param("ss", $token, $username);
        $stmt->execute();
        $stmt->close();
    }
}
?>

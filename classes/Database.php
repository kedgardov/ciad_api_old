<?php

class Database {
    private string $servername;
    private string $username;
    private string $password;
    private string $dbname;
    private ?mysqli $connection = null;

    public function __construct(string $servername, string $username, string $password, string $dbname) {
        $this->servername = $servername;
        $this->username = $username;
        $this->password = $password;
        $this->dbname = $dbname;
    }

    public function connect(): ?mysqli {
        if ($this->connection === null || !$this->connection->ping()) {
            $this->connection = new mysqli($this->servername, $this->username, $this->password, $this->dbname);

            if ($this->connection->connect_error) {
                $this->handleError("Connection failed: " . $this->connection->connect_error);
            }
        }
        return $this->connection;
    }

    public function disconnect(): void {
        if ($this->connection !== null) {
            $this->connection->close();
            $this->connection = null;
        }
    }

    public function getConnection(): ?mysqli {
        return $this->connect();
    }

    public function executeQuery(string $query): ?mysqli_result {
        $conn = $this->getConnection();
        $result = $conn->query($query);

        if ($result === false) {
            $this->handleError("Query failed: " . $conn->error);
        }

        return $result;
    }

    public function handleError(string $error): void {
        // Implement error handling logic here
        error_log($error);
        die("An error occurred. Please try again later.");
    }
}
?>

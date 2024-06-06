<?php
// Load database configuration
$config = include(__DIR__ . '/../config/db_config.php');

// Create connection
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL to create table unidades
$sql = "CREATE TABLE IF NOT EXISTS criterios (
    id_criterio INT AUTO_INCREMENT PRIMARY KEY,
    id_curso INT NOT NULL,
    criterio VARCHAR(50) NOT NULL,
    valor INT,
    FOREIGN KEY (id_curso) REFERENCES cursos(id_curso)
   )";

// Execute the query to create the table
if ($conn->query($sql) === TRUE) {
    echo "Table criterios created successfully\n";
} else {
    die("Error creating table: " . $conn->error);
}

// Close connection
$conn->close();
?>

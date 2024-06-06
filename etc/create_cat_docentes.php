<?php
// Load database configuration
$config = include(__DIR__ . '/../config/db_config.php');

// Create connection
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL to create table catalogo_habilidades
$sql = "CREATE TABLE IF NOT EXISTS catalogo_docentes (
    id_docente INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(40) NOT NULL,
    correo VARCHAR(50),
    telefono VARCHAR(15),
    info VARCHAR(500)
)";

// Execute the query to create the table
if ($conn->query($sql) === TRUE) {
    echo "Table catalogo_docentes created successfully\n";
} else {
    die("Error creating table: " . $conn->error);
}

// Close connection
$conn->close();
?>

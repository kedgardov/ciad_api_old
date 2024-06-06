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
$sql = "CREATE TABLE IF NOT EXISTS actualizaciones (
    id_actualizacion INT AUTO_INCREMENT PRIMARY KEY,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    descripcion VARCHAR(150),
    id_autorizador INT,
    FOREIGN KEY (id_autorizador) REFERENCES catalogo_docentes(id_docente)
)";

// Execute the query to create the table
if ($conn->query($sql) === TRUE) {
    echo "Table actualizaciones created successfully\n";
} else {
    die("Error creating table :( : " . $conn->error);
}

// Close connection
$conn->close();
?>

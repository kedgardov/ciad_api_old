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
$sql = "CREATE TABLE IF NOT EXISTS coordinaciones_cursos (
    id_coordinacion_curso INT AUTO_INCREMENT PRIMARY KEY,
    id_curso INT NOT NULL,
    id_coordinacion INT NOT NULL,
    FOREIGN KEY (id_curso) REFERENCES cursos(id_curso),
    FOREIGN KEY (id_coordinacion) REFERENCES catalogo_coordinaciones(id_coordinacion)
   )";

// Execute the query to create the table
if ($conn->query($sql) === TRUE) {
    echo "Table coordinaciones_cursos created successfully\n";
} else {
    die("Error creating table: " . $conn->error);
}

// Close connection
$conn->close();
?>

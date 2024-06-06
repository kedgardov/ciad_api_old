<?php
// Load database configuration
$config = include(__DIR__ . '/../config/db_config.php');

// Create connection
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL to create table cursos
$sql = "CREATE TABLE IF NOT EXISTS cursos (
    id_curso INT AUTO_INCREMENT PRIMARY KEY,
    clave INT NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    nombre_ingles VARCHAR(120) DEFAULT NULL,
    horas_teoricas INT DEFAULT NULL,
    horas_practicas INT DEFAULT NULL,
    horas_independientes INT DEFAULT NULL,
    horas_semana INT DEFAULT NULL,
    horas_semestre INT DEFAULT NULL,
    vinculo_objetivos_posgrado VARCHAR(1200) DEFAULT NULL
)";

// Execute the query to create the table
if ($conn->query($sql) === TRUE) {
    echo "Table cursos created successfully\n";
} else {
    die("Error creating table: " . $conn->error);
}

// Close connection
$conn->close();
?>

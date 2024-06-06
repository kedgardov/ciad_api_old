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
$sql = "CREATE TABLE IF NOT EXISTS roles_cursos (
    id_rol_curso INT AUTO_INCREMENT PRIMARY KEY,
    id_rol INT NOT NULL,
    id_curso INT NOT NULL,
    id_docente INT NOT NULL,
    FOREIGN KEY (id_rol) REFERENCES catalogo_roles(id_rol),
    FOREIGN KEY (id_curso) REFERENCES cursos(id_curso),
    FOREIGN KEY (id_docente) REFERENCES catalogo_docentes(id_docente)
)";

// Execute the query to create the table
if ($conn->query($sql) === TRUE) {
    echo "Table roles_cursos created successfully\n";
} else {
    die("Error creating table: " . $conn->error);
}

// Close connection
$conn->close();
?>

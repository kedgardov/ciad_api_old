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
$sql = "CREATE TABLE IF NOT EXISTS programas_curso (
    id_programas_curso INT AUTO_INCREMENT PRIMARY KEY,
    id_curso INT NOT NULL,
    id_programa INT NOT NULL,
    id_modalidad INT NOT NULL,
    FOREIGN KEY (id_curso) REFERENCES cursos(id_curso),
    FOREIGN KEY (id_modalidad) REFERENCES catalogo_modalidades(id_modalidad),
    FOREIGN KEY (id_programa) REFERENCES catalogo_programas(id_programa)
   )";

// Execute the query to create the table
if ($conn->query($sql) === TRUE) {
    echo "Table programas curso created successfully\n";
} else {
    die("Error creating table: " . $conn->error);
}

// Close connection
$conn->close();
?>

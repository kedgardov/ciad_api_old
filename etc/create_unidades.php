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
$sql = "CREATE TABLE IF NOT EXISTS unidades (
    id_unidad INT AUTO_INCREMENT PRIMARY KEY,
    id_curso INT NOT NULL,
    numero INT UNIQUE NOT NULL,
    nombre VARCHAR(100),
    objetivo VARCHAR(1200),
    id_habilidad INT,
    id_bloom INT,
    actividad_sinc VARCHAR(400),
    actividad_asinc VARCHAR(400),
    evidencia_sinc VARCHAR(100),
    evidencia_asinc VARCHAR(100),
    horas_sesion INT,
    FOREIGN KEY (id_habilidad) REFERENCES catalogo_habilidades(id_habilidad),
    FOREIGN KEY (id_curso) REFERENCES cursos(id_curso),
    FOREIGN KEY (id_bloom) REFERENCES catalogo_tax_bloom(id_bloom)
)";

// Execute the query to create the table
if ($conn->query($sql) === TRUE) {
    echo "Table unidades created successfully\n";
} else {
    die("Error creating table: " . $conn->error);
}

// Close connection
$conn->close();
?>

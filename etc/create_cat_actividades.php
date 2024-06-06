<?php
// Load database configuration
$config = include(__DIR__ . '/../config/db_config.php');

// Create connection
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL to create table catalogo_tax_bloom
$sql = "CREATE TABLE IF NOT EXISTS catalogo_actividades (
    id_actividad INT AUTO_INCREMENT PRIMARY KEY,
    id_habilidad INT,
    actividad VARCHAR(400) NOT NULL,
    FOREIGN KEY (id_habilidad) REFERENCES catalogo_habilidades(id_habilidad)
)";

// Execute the query to create the table
if ($conn->query($sql) === TRUE) {
    echo "Table catalogo_actividades created successfully\n";
} else {
    die("Error creating table: " . $conn->error);
}

// Close connection
$conn->close();
?>

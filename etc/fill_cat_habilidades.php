<?php
// Load database configuration
$config = include(__DIR__ . '/../config/db_config.php');

// Create connection
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$sql = "INSERT INTO catalogo_habilidades (habilidad, tipo) VALUES
('Pensamiento critico', 'Cognitivas y de autogestion'),
('Creatividad', 'Cognitivas y de autogestion'),
('Aprendizaje autonomo y continuo', 'Cognitivas y de autogestion'),
('Adaptabilidad y flexibilidad','Cognitivas y de autogestion'),
('Ciudadania global y consciencia intercultural', 'Sociales'),
('Responsabilidad social y etica','Sociales'),
('Colaboracion', 'Sociales'),
('Comunicacion efectiva', 'Comunicativas'),
('Alfabetizacion digital', 'Comunicativas')";


if ($conn->query($sql) === TRUE) {
    echo "Table catalogo_habilidades filled successfully\n";
} else {
    die("Error creating table: " . $conn->error);
}

// Close connection
$conn->close();
?>

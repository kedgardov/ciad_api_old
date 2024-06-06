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
$sql = "CREATE TABLE IF NOT EXISTS fuentes (
    id_fuente INT AUTO_INCREMENT PRIMARY KEY,
    id_curso INT NOT NULL,
    tipo ENUM('libro','revista','web','diario'),
    titulo VARCHAR(100),
    fecha_publicacion YEAR,
    editorial VARCHAR(50),
    volumen INT,
    numero INT,
    pagina INT,
    doi VARCHAR(32),
    url VARCHAR(60),
    fecha_consulta DATE,
    edicion INT,
    editor VARCHAR(40),
    nombre_web VARCHAR(40),
    nombre_revista VARCHAR(50),
    FOREIGN KEY (id_curso) REFERENCES cursos(id_curso)
   )";

// Execute the query to create the table
if ($conn->query($sql) === TRUE) {
    echo "Table fuentes created successfully\n";
} else {
    die("Error creating table: " . $conn->error);
}

// Close connection
$conn->close();
?>

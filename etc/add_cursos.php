<?php
// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load database configuration
$config = include(__DIR__ . '/../config/db_config.php');

// Create connection
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Connected successfully to the database\n";
}

// SQL to insert dummy entries
$sql = "INSERT INTO cursos (clave, nombre, nombre_ingles, horas_teoricas, horas_practicas, horas_independientes, horas_semana, horas_semestre, vinculo_objetivos_posgrado) VALUES
(101, 'Curso 1', 'Course 1', 10, 20, 5, 35, 140, 'Objetivos del Curso 1'),
(102, 'Curso 2', 'Course 2', 15, 25, 10, 50, 200, 'Objetivos del Curso 2'),
(103, 'Curso 3', 'Course 3', 20, 30, 15, 65, 260, 'Objetivos del Curso 3')";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "Dummy entries added successfully\n";
} else {
    die("Error inserting dummy entries: " . $conn->error);
}

// Close connection
$conn->close();
?>

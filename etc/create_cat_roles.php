<?php
// Load database configuration
$config = include(__DIR__ . '/../config/db_config.php');

// Create connection
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL to create table contacts
$sql = "CREATE TABLE IF NOT EXISTS catalogo_roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    rol VARCHAR(15) NOT NULL
   )";

// Execute the query to create the table
if ($conn->query($sql) === TRUE) {
    echo "Table catalogo_roles created successfully\n";
} else {
    die("Error creating table: " . $conn->error);
}

// Close connection
$conn->close();
?>

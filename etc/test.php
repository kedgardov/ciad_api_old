<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Echo a message before attempting to connect to the database
echo "Starting the database connection process...\n";

// Hardcode database configuration for testing purposes
$config = [
    'servername' => 'localhost',
    'username' => 'root',
    'password' => 'Meta1101!!!',
    'dbname' => 'ciad_test'
];

// Create connection
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Connected successfully to the database\n";
}

// Close connection
$conn->close();
?>

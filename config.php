<?php
// config.php

// Database configuration
$servername = "localhost";  // Use your database server
$username = "root";         // Database username
$password = "root";             // Database password
$dbname = "GymOwnerManager"; // The database name created earlier

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

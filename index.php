<?php
$servername = "db";
$username = "root";
$password = "root";
$dbname = "proyecto_dual";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
echo "<h1>Hola Mundo desde PHP!</h1>";
echo "<p>Conexión exitosa a la base de datos MySQL.</p>";
$conn->close();
?>

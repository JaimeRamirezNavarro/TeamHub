<?php
require_once __DIR__ . '/../motor/db.php';


try {
    $db = Database::getInstance();
    echo "Conexión establecida correctamente desde la carpeta UI";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

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

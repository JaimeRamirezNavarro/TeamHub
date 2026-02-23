<?php
require_once __DIR__ . '/../modelo/consultas.php';

$consultas = new Consultas();
$equipo = $consultas->obtenerEquipo(4); // ID 4 is Diseño UI/UX

echo "<pre>";
if ($equipo) {
    echo "ID: " . $equipo['id'] . "\n";
    echo "Name: " . $equipo['name'] . "\n";
    echo "Hex Name: " . bin2hex($equipo['name']) . "\n";
    echo "Description: " . $equipo['description'] . "\n";
    echo "Hex Desc: " . bin2hex($equipo['description']) . "\n";
} else {
    echo "Team 4 not found";
}
echo "</pre>";

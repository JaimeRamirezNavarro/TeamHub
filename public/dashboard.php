<?php
$title = "Dashboard";

// Variables necesarias para la vista
// (estas deben venir de tu controlador o consulta)
require_once __DIR__ . '/../bootstrap.php'; // si tienes uno

ob_start();
include __DIR__ . '/../app/Views/pages/dashboard.php';
$content = ob_get_clean();

include __DIR__ . '/../app/Views/layouts/main.php';

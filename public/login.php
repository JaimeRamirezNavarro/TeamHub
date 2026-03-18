<?php

// Rutas relativas desde /public
require_once __DIR__ . '/../app/Controllers/AuthController.php';
require_once __DIR__ . '/../app/Models/AuthModel.php';
require_once __DIR__ . '/../app/Models/UserModel.php';

$controller = new AuthController();
$controller->showLogin();
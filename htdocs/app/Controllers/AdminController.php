<?php

require_once __DIR__ . '/../Middleware/Auth.php';
require_once __DIR__ . '/../Models/TeamModel.php';

class AdminController
{
    public function index()
    {
        Auth::requireRole(['admin', 'manager']);

        $usuario = Auth::user();

        $teamModel = new TeamModel();
        $equipos = $teamModel->obtenerEquiposPorUsuario(
            $usuario['id'],
            $usuario['role']
        );

        // Cargar contenido de la vista admin
        ob_start();
        require __DIR__ . '/../Views/pages/admin.php';
        $content = ob_get_clean();

        // Variables necesarias para el layout
        $title = "Panel de Administración";
        $extra_css = null;

        // Cargar layout principal (ahora con $equipos disponible)
        require __DIR__ . '/../Views/layouts/main.php';
    }
}

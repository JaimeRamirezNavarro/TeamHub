<?php
session_start();
require_once __DIR__ . '/../modelo/consultas.php';

// Auth Check
if(!isset($_SESSION['user_id'])){
    header("Location: inicio.php");
    exit;
}

$consultas = new Consultas();

// --- LOGICA DE POST (Teams) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    
    if (isset($_POST['join_team'])) {
        $consultas->unirseEquipo($user_id, $_POST['team_id']);
    } elseif (isset($_POST['leave_team'])) {
        $consultas->salirEquipo($user_id, $_POST['team_id']);
    } elseif (isset($_POST['cambiar_estado'])) {
        $consultas->actualizarEstado($user_id, $_POST['nuevo_estado']);
    } elseif (isset($_POST['logout'])) {
        session_destroy();
        header("Location: inicio.php");
        exit;
    }
    
    header("Location: index.php"); // Evitar reenvío
    exit;
}

// Datos para UI
$equipos = $consultas->obtenerTodosLosEquipos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; background: #121212; color: white; padding: 40px; }
        h1 { display: flex; justify-content: space-between; align-items: center; }
        .project-card { background: #1e1e1e; margin-bottom: 15px; border-radius: 8px; overflow: hidden; border: 1px solid #333; }
        summary { padding: 20px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; list-style: none; }
        summary::-webkit-details-marker { display: none; }
        summary::after { content: '▶'; transition: transform 0.3s; }
        details[open] summary::after { transform: rotate(90deg); }
        .user-list { background: #252525; padding: 15px; border-top: 1px solid #333; }
        .user-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #444; }
        .status-dot { height: 10px; width: 10px; border-radius: 50%; display: inline-block; margin-right: 5px; }
        .Oficina { background-color: #4CAF50; }
        .Teletrabajo { background-color: #2196F3; }
        .btn-logout { background: #ff5252; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-size: 0.8em; }
    </style>
</head>
<body>

    <h1>
        <span>🚀 Dashboard de Proyectos</span>
        <form method="POST" style="margin:0;">
            <button type="submit" name="logout" class="btn-logout">Cerrar Sesión (<?= htmlspecialchars($_SESSION['username']) ?>)</button>
        </form>
    </h1>

    <?php if (empty($equipos)): ?>
        <p>No hay proyectos disponibles.</p>
    <?php else: ?>
        <?php foreach ($equipos as $equipo): ?>
            <div class="project-card">
                <?php 
                    $esMiembro = $consultas->esMiembro($_SESSION['user_id'], $equipo['id']); 
                    $miembros = $consultas->obtenerMiembrosEquipo($equipo['id']);
                ?>
                <details>
                    <summary>
                        <span><strong><?= htmlspecialchars($equipo['name']) ?></strong></span>
                        
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="team_id" value="<?= $equipo['id'] ?>">
                            <?php if ($esMiembro): ?>
                                <span style="font-size: 0.8em; color: #4CAF50; margin-right: 10px;">(Miembro)</span>
                                <button type="submit" name="leave_team" style="background:#ff5252; border:none; color:white; padding:5px 10px; border-radius:4px; cursor:pointer;">Salir</button>
                            <?php else: ?>
                                <button type="submit" name="join_team" style="background:#2196F3; border:none; color:white; padding:5px 10px; border-radius:4px; cursor:pointer;">Unirse</button>
                            <?php endif; ?>
                        </form>
                    </summary>
                    
                    <div class="user-list">
                        <p><em><?= htmlspecialchars($equipo['description']) ?></em></p>
                        
                        <?php if ($esMiembro): ?>
                            <h4>👥 Miembros del equipo:</h4>
                            <?php foreach ($miembros as $m): ?>
                                <div class="user-item">
                                    <span><?= htmlspecialchars($m['username']) ?></span>
                                    <span>
                                        <span class="status-dot <?= $m['status'] ?>"></span>
                                        <?= $m['status'] ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="color: #888;">Únete al equipo para ver a los miembros y colaborar.</p>
                        <?php endif; ?>
                    </div>
                </details>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</body>
</html>
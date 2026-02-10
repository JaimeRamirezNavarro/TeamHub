<?php 
require_once __DIR__ . '/../motor/auth.php';
checkAuth();
require_once __DIR__ . '/../motor/main.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; background: #121212; color: white; padding: 40px; }
        .project-card { background: #1e1e1e; margin-bottom: 15px; border-radius: 8px; overflow: hidden; border: 1px solid #333; }
        summary { padding: 20px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; list-style: none; }
        summary::-webkit-details-marker { display: none; } /* Quita la flecha por defecto */
        summary::after { content: '▶'; transition: transform 0.3s; }
        details[open] summary::after { transform: rotate(90deg); }
        .user-list { background: #252525; padding: 15px; border-top: 1px solid #333; }
        .user-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #444; }
        .status-dot { height: 10px; width: 10px; border-radius: 50%; display: inline-block; margin-right: 5px; }
        .Oficina { background-color: #4CAF50; }
        .Teletrabajo { background-color: #2196F3; }
    </style>
</head>
<body>

    <h1>🚀 Dashboard de Proyectos</h1>

    <?php if (empty($equipos)): ?>
        <p>No hay proyectos disponibles. ¡Crea uno!</p>
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
                        
                        <form action="../motor/main.php" method="POST" style="display:inline;">
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
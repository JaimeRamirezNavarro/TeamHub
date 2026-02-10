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
        body { 
        font-family: sans-serif; 
        background: #121212; 
        color: white; 
        padding: 40px; 
        }

        .project-card { 
        background: #1e1e1e; 
        margin-bottom: 15px; 
        border-radius: 8px; 
        overflow: hidden; 
        border: 1px solid #333; 
        }
        summary { 
        padding: 20px; 
        cursor: pointer; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        list-style: none; 
        }
        summary::-webkit-details-marker { display: none; } /* Quita la flecha por defecto */
        summary::after { content: '▶'; transition: transform 0.3s; }
        details[open] summary::after { transform: rotate(90deg); }
        .user-list { 
        background: #252525; 
        padding: 15px; 
        border-top: 1px solid #333; 
        }
        .user-item { 
        display: flex; 
        justify-content: space-between; 
        padding: 8px 0; 
        border-bottom: 1px solid #444; 
        }
        .status-dot { 
        height: 10px; 
        width: 10px; 
        border-radius: 50%; 
        display: inline-block; 
        margin-right: 5px; 
        }
        .Oficina { background-color: #4CAF50; }
        .Teletrabajo { background-color: #2196F3; }
    </style>
</head>
<body>

    <h1>Dashboard de Proyectos</h1>

    <?php foreach ($proyectos as $p): ?>
        <div class="project-card">
            <details>
                <summary>
                    <span><strong><?= $p['title'] ?></strong> - <?= $p['status'] ?></span>
                </summary>
                
                <div class="user-list">
                    <h4>👥 Equipo asignado:</h4>
                    <?php foreach ($empleados as $e): ?>
                        <div class="user-item">
                            <span><?= $e['username'] ?></span>
                            <span>
                                <span class="status-dot <?= $e['status'] ?>"></span>
                                <?= $e['status'] ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </details>
        </div>
    <?php endforeach; ?>

</body>
</html>
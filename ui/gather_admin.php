<?php
/**
 * Panel de Administración de Gather
 * Permite crear y gestionar espacios de Gather vinculados a proyectos
 */

session_start();
require_once __DIR__ . '/../modelo/consultas.php';
require_once __DIR__ . '/../motor/gather_api.php';

// Auth Check
if(!isset($_SESSION['user_id'])){
    header("Location: inicio.php");
    exit;
}

$consultas = new Consultas();
$gatherAPI = new GatherAPI();
$user_id = $_SESSION['user_id'];
$currentUser = $consultas->obtenerUsuario($user_id);

$message = '';
$error = '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $team_id = $_POST['team_id'] ?? null;
    
    if (isset($_POST['create_gather_space']) && $team_id) {
        // Crear espacio de Gather
        $team = $consultas->obtenerEquipo($team_id);
        
        try {
            $space = $gatherAPI->createTeamSpace($team['name'], $team['description']);
            
            if ($space && isset($space['id'])) {
                $spaceUrl = $gatherAPI->getSpaceUrl($space['id']);
                $consultas->vincularGatherSpace($team_id, $space['id'], $spaceUrl);
                
                // Sincronizar miembros
                $members = $consultas->obtenerMiembrosConEmail($team_id);
                $gatherAPI->syncTeamMembers($space['id'], $members);
                
                $message = "✅ Espacio de Gather creado exitosamente: " . $space['name'];
            } else {
                $error = "❌ Error al crear el espacio de Gather";
            }
        } catch (Exception $e) {
            $error = "❌ Error: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['sync_members']) && $team_id) {
        // Sincronizar miembros
        $gatherInfo = $consultas->obtenerGatherInfo($team_id);
        
        if ($gatherInfo && $gatherInfo['gather_space_id']) {
            try {
                $members = $consultas->obtenerMiembrosConEmail($team_id);
                $gatherAPI->syncTeamMembers($gatherInfo['gather_space_id'], $members);
                $message = "✅ Miembros sincronizados con Gather";
            } catch (Exception $e) {
                $error = "❌ Error al sincronizar: " . $e->getMessage();
            }
        }
    }
    
    if (isset($_POST['toggle_gather']) && $team_id) {
        $action = $_POST['action'];
        if ($action === 'enable') {
            $consultas->activarGather($team_id);
            $message = "✅ Integración de Gather activada";
        } else {
            $consultas->desactivarGather($team_id);
            $message = "ℹ️ Integración de Gather desactivada";
        }
    }
}

// Obtener todos los proyectos
$teams = $consultas->obtenerTodosLosEquipos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>TeamHub | Gather Integration</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #121212;
            --sidebar-bg: #1e1e1e;
            --card-bg: #252525;
            --text-primary: #ffffff;
            --text-secondary: #b0b0b0;
            --accent-color: #2196F3;
            --success-color: #4CAF50;
            --danger-color: #ff5252;
            --border-color: #333;
        }

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            background-color: var(--bg-color);
            color: var(--text-primary);
            padding: 40px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--border-color);
        }

        h1 {
            margin: 0;
            font-size: 2rem;
        }

        .gather-logo {
            font-size: 3rem;
            opacity: 0.8;
        }

        .back-link {
            color: var(--accent-color);
            text-decoration: none;
            padding: 10px 20px;
            border: 1px solid var(--accent-color);
            border-radius: 6px;
            transition: 0.2s;
        }

        .back-link:hover {
            background: var(--accent-color);
            color: white;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: rgba(76, 175, 80, 0.2);
            border: 1px solid #4CAF50;
            color: #81C784;
        }

        .alert-error {
            background: rgba(255, 82, 82, 0.2);
            border: 1px solid #ff5252;
            color: #E57373;
        }

        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }

        .project-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 25px;
            transition: 0.2s;
        }

        .project-card:hover {
            border-color: #555;
        }

        .project-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }

        .project-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin: 0;
        }

        .badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-connected {
            background: rgba(76, 175, 80, 0.2);
            color: #81C784;
        }

        .badge-disconnected {
            background: rgba(158, 158, 158, 0.2);
            color: #9E9E9E;
        }

        .project-description {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .gather-info {
            background: rgba(0,0,0,0.3);
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }

        .gather-url {
            color: var(--accent-color);
            text-decoration: none;
            word-break: break-all;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            transition: 0.2s;
            flex: 1;
            min-width: 120px;
        }

        .btn-primary {
            background: var(--accent-color);
            color: white;
        }

        .btn-primary:hover {
            background: #1976D2;
        }

        .btn-success {
            background: var(--success-color);
            color: white;
        }

        .btn-success:hover {
            background: #388E3C;
        }

        .btn-danger {
            background: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background: #D32F2F;
        }

        .btn-secondary {
            background: #444;
            color: white;
        }

        .btn-secondary:hover {
            background: #555;
        }

        .setup-guide {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
        }

        .setup-guide h2 {
            margin-top: 0;
            color: var(--accent-color);
        }

        .setup-guide ol {
            line-height: 1.8;
        }

        .setup-guide code {
            background: rgba(0,0,0,0.3);
            padding: 2px 8px;
            border-radius: 4px;
            color: #FFB74D;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1><span class="gather-logo">🏢</span> Integración con Gather</h1>
                <p style="color: var(--text-secondary); margin: 5px 0 0 0;">
                    Gestiona espacios virtuales para tus proyectos
                </p>
            </div>
            <a href="index.php" class="back-link">← Volver al Dashboard</a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <!-- Guía de Configuración -->
        <div class="setup-guide">
            <h2>📋 Configuración Inicial</h2>
            <p>Para usar la integración con Gather, necesitas configurar tu API Key:</p>
            <ol>
                <li>Ve a <a href="https://app.gather.town/apiKeys" target="_blank" style="color: var(--accent-color);">https://app.gather.town/apiKeys</a></li>
                <li>Genera una nueva API Key</li>
                <li>Edita el archivo <code>motor/gather_config.php</code></li>
                <li>Reemplaza <code>YOUR_GATHER_API_KEY_HERE</code> con tu API Key</li>
            </ol>
            <p style="color: var(--text-secondary); font-size: 0.9rem;">
                ℹ️ Una vez configurado, podrás crear espacios virtuales de Gather vinculados a tus proyectos.
            </p>
        </div>

        <h2 style="margin-bottom: 20px;">Proyectos</h2>

        <div class="projects-grid">
            <?php foreach ($teams as $team): 
                $gatherInfo = $consultas->obtenerGatherInfo($team['id']);
                $isConnected = $gatherInfo && $gatherInfo['gather_enabled'];
                $hasSpace = $gatherInfo && !empty($gatherInfo['gather_space_id']);
            ?>
                <div class="project-card">
                    <div class="project-header">
                        <h3 class="project-title"><?= htmlspecialchars($team['name']) ?></h3>
                        <span class="badge <?= $isConnected ? 'badge-connected' : 'badge-disconnected' ?>">
                            <?= $isConnected ? '✓ Conectado' : '○ Desconectado' ?>
                        </span>
                    </div>

                    <p class="project-description">
                        <?= htmlspecialchars(substr($team['description'], 0, 100)) ?>
                        <?= strlen($team['description']) > 100 ? '...' : '' ?>
                    </p>

                    <?php if ($hasSpace): ?>
                        <div class="gather-info">
                            <strong>Espacio Gather:</strong><br>
                            <a href="<?= htmlspecialchars($gatherInfo['gather_space_url']) ?>" 
                               target="_blank" 
                               class="gather-url">
                                <?= htmlspecialchars($gatherInfo['gather_space_id']) ?>
                            </a>
                        </div>

                        <div class="actions">
                            <form method="POST" style="flex: 1;">
                                <input type="hidden" name="team_id" value="<?= $team['id'] ?>">
                                <button type="submit" name="sync_members" class="btn btn-secondary">
                                    🔄 Sincronizar
                                </button>
                            </form>

                            <form method="POST" style="flex: 1;">
                                <input type="hidden" name="team_id" value="<?= $team['id'] ?>">
                                <input type="hidden" name="toggle_gather" value="1">
                                <?php if ($isConnected): ?>
                                    <input type="hidden" name="action" value="disable">
                                    <button type="submit" class="btn btn-danger">
                                        ✗ Desactivar
                                    </button>
                                <?php else: ?>
                                    <input type="hidden" name="action" value="enable">
                                    <button type="submit" class="btn btn-success">
                                        ✓ Activar
                                    </button>
                                <?php endif; ?>
                            </form>

                            <a href="<?= htmlspecialchars($gatherInfo['gather_space_url']) ?>" 
                               target="_blank" 
                               class="btn btn-primary" 
                               style="text-align: center; text-decoration: none;">
                                🚀 Abrir
                            </a>
                        </div>
                    <?php else: ?>
                        <form method="POST">
                            <input type="hidden" name="team_id" value="<?= $team['id'] ?>">
                            <button type="submit" name="create_gather_space" class="btn btn-primary" style="width: 100%;">
                                ➕ Crear Espacio Gather
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
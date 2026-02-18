<?php
/**
 * TeamHub - Online Users
 * Shows a full page list of who is online.
 */

session_start();
require_once __DIR__ . '/../modelo/consultas.php';

// Auth Check
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$consultas = new Consultas();
$user_id = $_SESSION['user_id'];
$currentUser = $consultas->obtenerUsuario($user_id);
$username = $currentUser['username'];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>TeamHub | Usuarios Online</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #121212;
            --card-bg: #1e1e1e;
            --text-primary: #ffffff;
            --text-secondary: #b0b0b0;
            --accent-color: #2196F3;
            --border-color: #333;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-primary);
            margin: 0;
            padding: 40px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 40px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 20px;
        }

        .back-btn {
            text-decoration: none;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            font-weight: 500;
            transition: 0.2s;
        }
        .back-btn:hover { color: white; transform: translateX(-5px); }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .user-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            transition: 0.2s;
        }
        
        .user-card:hover {
            transform: translateY(-2px);
            border-color: #555;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }

        .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 15px;
            position: relative;
        }

        .status-badge {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            border: 2px solid var(--card-bg);
        }

        .user-info h3 { margin: 0 0 5px 0; font-size: 1.1rem; }
        .user-info p { margin: 0; color: var(--text-secondary); font-size: 0.9rem; }

        .actions-bar {
            margin-top: 40px;
            padding: 20px;
            background: rgba(33, 150, 243, 0.1);
            border-radius: 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
        }
        
        .btn-gather {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-gather:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(118, 75, 162, 0.4); }

    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>👥 Usuarios Online</h1>
        <a href="dashboard.php" class="back-btn">← Volver al Dashboard</a>
    </div>

    <div id="loading" style="text-align:center; padding:50px; color:#666;">
        Cargando estado actual...
    </div>

    <div id="users-grid" class="grid"></div>


</div>

<script>
    document.addEventListener('DOMContentLoaded', loadPresence);
    setInterval(loadPresence, 10000); // 10s refresh

    function loadPresence() {
        fetch('/endpoints/get_online_users.php')
            .then(res => res.json())
            .then(data => {
                document.getElementById('loading').style.display = 'none';
                renderUsers(data);
                // Gather integration removed, so no action buttons needed
            })
            .catch(err => console.error(err));
    }

    function renderUsers(data) {
        const grid = document.getElementById('users-grid');
        grid.innerHTML = '';

        if (!data.users || data.users.length === 0) {
            grid.innerHTML = '<div style="grid-column:1/-1; text-align:center; padding:30px;">No hay nadie conectado ahora mismo. 😴</div>';
            return;
        }

        data.users.forEach(user => {
            const statusColor = getStatusColor(user.status);
            
            const card = document.createElement('div');
            card.className = 'user-card';
            card.innerHTML = `
                <div class="avatar">
                    ${user.name.charAt(0).toUpperCase()}
                    <div class="status-badge" style="background:${statusColor}"></div>
                </div>
                <div class="user-info">
                    <h3>${escapeHtml(user.name)}</h3>
                    <p>${escapeHtml(user.customStatus || user.status)}</p>
                </div>
            `;
            grid.appendChild(card);
        });
    }

    function getStatusColor(status) {
        return {
            'online': '#4CAF50',
            'active': '#4CAF50',
            'away': '#FFC107',
            'busy': '#ff5252',
            'offline': '#9E9E9E'
        }[status] || '#9E9E9E';
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text.replace(/&/g, "&amp;")
                   .replace(/</g, "&lt;")
                   .replace(/>/g, "&gt;")
                   .replace(/"/g, "&quot;")
                   .replace(/'/g, "&#039;");
    }
</script>

<script src="js/heartbeat.js"></script>
</body>
</html>

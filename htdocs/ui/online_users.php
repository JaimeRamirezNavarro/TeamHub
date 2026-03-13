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
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        base: '#F2F0E9',
                        neon: '#a8dba8',
                        neonSec: '#79c753',
                    },
                    fontFamily: {
                        serif: ['Times New Roman', 'Georgia', 'serif'],
                        mono: ['Courier New', 'Courier', 'monospace'],
                    },
                    boxShadow: {
                        'brutal-lg': '8px 8px 0px 0px rgba(0,0,0,1)',
                        'brutal-md': '4px 4px 0px 0px rgba(0,0,0,1)',
                        'brutal-sm': '1px 1px 0px 0px rgba(0,0,0,1)',
                    }
                }
            }
        }
    </script>
    <style>
        /* Base y Reseteo Brutalista */
        body { 
            background: #F2F0E9;
            color: black;
            min-height: 100vh; 
            margin: 0; 
            background-image: linear-gradient(to right, rgba(0,0,0,0.05) 1px, transparent 1px), linear-gradient(to bottom, rgba(0,0,0,0.05) 1px, transparent 1px);
            background-size: 20px 20px;
        }

        /* Cursores Pixelados Retro (Windows 95) */
        * {
            cursor: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24'%3E%3Cpath fill='white' stroke='black' stroke-width='1.5' d='M0,0 L0,18 L6,12 L9,19.5 L12,18 L9,10.5 L15,10.5 Z'/%3E%3C/svg%3E") 0 0, auto;
        }
        a, button, [role="button"], .cursor-pointer, input[type="submit"], input[type="button"] {
            cursor: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24'%3E%3Cpath fill='white' stroke='black' stroke-width='1.5' d='M12,0 L12,7.5 L16.5,7.5 L16.5,10.5 L12,10.5 L12,18 L9,18 L9,10.5 L4.5,10.5 L4.5,7.5 L9,7.5 L9,0 Z M0,9 L4.5,9 L4.5,12 L0,12 Z M16.5,9 L21,9 L21,12 L16.5,12 Z'/%3E%3C/svg%3E") 12 12, pointer !important;
        }
    </style>
</head>
<body class="font-mono p-8 md:p-12">

<div class="max-w-5xl mx-auto">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-10 border-b-4 border-black pb-6 gap-4">
        <h1 class="font-serif font-black text-4xl uppercase text-black tracking-tighter">ONLINE_USERS.EXE</h1>
        <a href="dashboard.php" class="border-2 border-black bg-white text-black font-bold uppercase py-2 px-4 shadow-brutal-sm hover:bg-neon hover:text-black active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-none text-sm">
            <-- BACK_TO_ROOT
        </a>
    </div>

    <div id="loading" class="text-center p-10 font-bold uppercase text-black animate-pulse bg-white border-2 border-black shadow-brutal-md w-full max-w-sm mx-auto">
        [ LOADING SYSTEM_STATUS... ]
    </div>

    <div id="users-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"></div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', loadPresence);
    setInterval(loadPresence, 10000); // 10s refresh

    function loadPresence() {
        fetch('/endpoints/get_online_users.php')
            .then(res => res.json())
            .then(data => {
                const loader = document.getElementById('loading');
                if (loader) loader.style.display = 'none';
                renderUsers(data);
            })
            .catch(err => console.error(err));
    }

    function renderUsers(data) {
        const grid = document.getElementById('users-grid');
        grid.innerHTML = '';

        if (!data.users || data.users.length === 0) {
            grid.innerHTML = '<div class="col-span-full text-center p-8 bg-white border-2 border-black shadow-brutal-md font-bold uppercase text-black">NO_ACTIVE_CONNECTIONS</div>';
            return;
        }

        data.users.forEach(user => {
            const statusColor = getStatusColor(user.status);
            
            const card = document.createElement('div');
            card.className = 'bg-white border-2 border-black shadow-brutal-md p-4 flex items-center hover:-translate-y-1 hover:shadow-brutal-lg transition-all duration-75 group';
            
            // DiceBear Pixel Art avatar
            const avatarUrl = 'https://api.dicebear.com/7.x/pixel-art/svg?seed=' + encodeURIComponent(user.name);
            
            card.innerHTML = `
                <div class="relative mr-4 flex-shrink-0 w-12 h-12 bg-base border-2 border-black">
                    <img src="${avatarUrl}" alt="Avatar" class="w-full h-full object-cover">
                    <div class="absolute -bottom-1 -right-1 w-4 h-4 border-2 border-black" style="background:${statusColor}"></div>
                </div>
                <div class="overflow-hidden">
                    <h3 class="m-0 text-base font-black uppercase text-black truncate group-hover:text-neonSec">${escapeHtml(user.name)}</h3>
                    <p class="m-0 text-xs font-bold uppercase text-black opacity-60 truncate">${escapeHtml(user.customStatus || user.status)}</p>
                </div>
            `;
            grid.appendChild(card);
        });
    }

    function getStatusColor(status) {
        return {
            'online': '#79c753', // Verde neón para Y2K
            'active': '#79c753',
            'away': '#e0c253', // Amarillo neón para ausente
            'busy': '#ff0000', // Rojo brutal
            'offline': '#000000'
        }[status] || '#000000';
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

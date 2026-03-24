<?php
require_once __DIR__ . '/../../Middleware/Auth.php';
?>

<div class="sidebar">

    <!-- Brand + Theme Toggle -->
    <div class="brand">
        <a href="/" class="brand-logo">TeamHub</a>

        <button id="theme-toggle" class="btn"
            style="margin-left:auto; padding:6px; background:transparent; border:1px solid var(--border-color); color:var(--text-secondary);"
            title="Cambiar Tema">

            <svg id="theme-icon-light" style="display:none;" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="5"></circle>
                <line x1="12" y1="1" x2="12" y2="3"></line>
                <line x1="12" y1="21" x2="12" y2="23"></line>
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                <line x1="1" y1="12" x2="3" y2="12"></line>
                <line x1="21" y1="12" x2="23" y2="12"></line>
                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
            </svg>

            <svg id="theme-icon-dark" style="display:none;" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
            </svg>
        </button>
    </div>

    <!-- User Profile -->
    <div class="user-profile">
        <div class="user-avatar">
            <?= strtoupper(substr($usuario['username'] ?? 'U', 0, 1)) ?>
        </div>

        <div style="flex:1;">
            <div style="font-weight:600">
                <?= htmlspecialchars($usuario['username'] ?? 'Invitado') ?>
            </div>
        </div>
    </div>

    <!-- Navegación principal -->
    <div class="sidebar-section-title">Navegación</div>

    <ul class="nav-list">

        <li><a href="/teams" class="project-link">Mis equipos</a></li>

        <?php if (Auth::hasRole(['admin', 'manager'])): ?>
            <li><a href="/teams/all" class="project-link">Todos los equipos</a></li>
        <?php endif; ?>
    </ul>

    <!-- Lista de proyectos del usuario -->
    <div class="sidebar-section-title" style="margin-top:20px;">Mis proyectos</div>

    <ul class="project-list">
        <?php $equipos = $equipos ?? []; ?>
        <?php foreach ($equipos as $equipo): ?>
            <li class="project-item">
                <a href="?team_id=<?= $equipo['id'] ?>"
                    class="project-link <?= $selected_team_id == $equipo['id'] ? 'active' : '' ?>">
                    <?= htmlspecialchars($equipo['name']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- Herramientas -->
    <?php if (Auth::hasRole(['admin', 'manager'])): ?>
        <div class="sidebar-section-title" style="margin-top:20px;">Herramientas</div>

        <a href="/admin" class="project-link" style="display:block; padding:6px 0;">
            Administrar
        </a>
    <?php endif; ?>

    <!-- Logout -->
    <div style="margin-top:auto; padding-top:20px;">
        <form method="POST" action="/logout">
            <button type="submit" name="logout" class="logout-btn">Cerrar sesión</button>
        </form>
    </div>

</div>
<div class="admin-container">

    <h1 class="admin-title">Panel de Administración</h1>
    <p class="admin-subtitle">Herramientas de gestión del sistema</p>

    <div class="admin-grid">

        <!-- Crear proyecto -->
        <a href="/teams/create" class="admin-card">
            <div class="admin-card-header">Proyectos</div>
            <div class="admin-card-desc">Crear y gestionar equipos o proyectos.</div>
        </a>

        <!-- Gestión de usuarios -->
        <?php if (Auth::hasRole(['admin'])): ?>
            <a href="/users" class="admin-card">
                <div class="admin-card-header">Usuarios</div>
                <div class="admin-card-desc">Administrar roles, accesos y usuarios.</div>
            </a>
        <?php endif; ?>

    </div>

</div>
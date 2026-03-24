<div class="admin-container">
    <h1 class="admin-title">Usuarios</h1>
    <p class="admin-subtitle">Listado de usuarios del sistema.</p>

    <div class="admin-grid">
        <?php foreach ($usuarios as $u): ?>
            <div class="admin-card">
                <div>
                    <div class="admin-card-title"><?= htmlspecialchars($u['username']) ?></div>
                    <div class="admin-card-desc"><?= htmlspecialchars($u['email']) ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<div class="admin-container">
    <h1 class="admin-title">Usuarios</h1>
    <p class="admin-subtitle">Listado de usuarios del sistema.</p>

    <div class="admin-grid">
        <?php foreach ($usuarios as $u): ?>
            <div class="admin-card admin-card-static admin-card-user">

                <div class="admin-card-info">
                    <div class="admin-card-title"><?= htmlspecialchars($u['username']) ?></div>
                    <div class="admin-card-desc"><?= htmlspecialchars($u['email']) ?></div>
                </div>

                <a href="/users/<?= $u['id'] ?>/edit-role" class="btn-action">Cambiar rol</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
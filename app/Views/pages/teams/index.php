<div class="admin-container">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <h1 class="admin-title">Mis Equipos</h1>
            <p class="admin-subtitle">Gestiona y accede rápidamente a tus equipos.</p>
        </div>

        <?php if (in_array($_SESSION['user']['role'], ['admin', 'manager'])): ?>
            <a href="<?= BASE_PATH ?>/teams/create" class="btn-primary" style="text-decoration:none;">
                Crear Proyecto
            </a>
        <?php endif; ?>
    </div>

    <?php if (empty($equipos)): ?>

        <div class="form-card" style="text-align:center;">
            <h3 style="margin-bottom:10px;">Aún no perteneces a ningún equipo</h3>
            <p style="color:var(--text-secondary); margin-bottom:20px;">
                Cuando te unas o crees un equipo, aparecerá aquí.
            </p>

            <?php if (in_array($_SESSION['user']['role'], ['admin', 'manager'])): ?>
                <a href="<?= BASE_PATH ?>/teams/create" class="btn-primary">Crear mi primer equipo</a>
            <?php endif; ?>
        </div>

    <?php else: ?>

        <div class="admin-grid">
            <?php foreach ($equipos as $equipo): ?>
                <a href="<?= BASE_PATH ?>/teams/<?= $equipo['id'] ?>" class="admin-card">
                    <div>
                        <div class="admin-card-title">
                            <?= htmlspecialchars($equipo['name']) ?>
                        </div>

                        <div class="admin-card-desc">
                            <?= htmlspecialchars($equipo['description'] ?: 'Sin descripción') ?>
                        </div>

                        <div style="margin-top:6px; font-size:13px; color:var(--text-secondary);">
                            Estado: <strong><?= htmlspecialchars($equipo['status'] ?? 'En Progreso') ?></strong>
                        </div>
                    </div>

                </a>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</div>
<div class="admin-container">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <h1 class="admin-title">Todos los Equipos</h1>
            <p class="admin-subtitle">Listado completo de equipos registrados en el sistema.</p>
        </div>

        <a href="<?= BASE_PATH ?>/teams/create" class="btn-primary">Crear Equipo</a>
    </div>

    <?php if (empty($equipos)): ?>
        <div class="form-card">
            <h3>No hay equipos registrados</h3>
            <p style="color:var(--text-secondary);">Cuando se creen equipos, aparecerán aquí.</p>
        </div>
    <?php else: ?>
        <div class="admin-grid">
            <?php foreach ($equipos as $equipo): ?>
                <a href="<?= BASE_PATH ?>/teams/<?= $equipo['id'] ?>" class="admin-card">
                    <div class="admin-icon">👥</div>
                    <div>
                        <div class="admin-card-title"><?= htmlspecialchars($equipo['name']) ?></div>
                        <div class="admin-card-desc"><?= htmlspecialchars($equipo['description'] ?: 'Sin descripción') ?></div>
                        <div style="margin-top:6px; font-size:13px; color:var(--text-secondary);">
                            Estado: <strong><?= htmlspecialchars($equipo['status'] ?? 'En Progreso') ?></strong>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
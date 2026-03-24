<div class="admin-container">

    <h1 class="admin-title"><?= htmlspecialchars($team['name']) ?></h1>
    <p class="admin-subtitle"><?= htmlspecialchars($team['description'] ?? 'Sin descripción') ?></p>

    <h2 class="admin-title" style="font-size:20px; margin-top:25px;">Miembros del equipo</h2>

    <div class="admin-grid">
        <?php foreach ($miembros as $m): ?>
            <div class="admin-card">
                <div class="admin-icon">👤</div>

                <div>
                    <div class="admin-card-title"><?= htmlspecialchars($m['username']) ?></div>
                    <div class="admin-card-desc">
                        Rol: <?= htmlspecialchars($m['role']) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>
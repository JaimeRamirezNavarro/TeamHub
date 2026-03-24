<div class="admin-container">

    <h1 class="admin-title"><?= htmlspecialchars($team['name']) ?></h1>
    <p class="admin-subtitle"><?= htmlspecialchars($team['description'] ?? 'Sin descripción') ?></p>

    <h2 class="admin-title" style="font-size:20px; margin-top:25px;">Miembros del equipo</h2>

    <div class="admin-grid">
        <?php foreach ($miembros as $m): ?>
            <div class="admin-card">
                <div>
                    <div class="admin-card-title"><?= htmlspecialchars($m['username']) ?></div>
                    <div class="admin-card-desc">
                        Rol: <?= htmlspecialchars($m['role']) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- ACCIONES DEL EQUIPO -->
    <?php if (Auth::hasRole(['admin', 'manager'])): ?>
        <div style="margin-top:30px;">

            <form action="/teams/delete" method="POST"
                onsubmit="return confirm('¿Seguro que deseas eliminar este equipo? Esta acción no se puede deshacer.');">

                <input type="hidden" name="team_id" value="<?= $team['id'] ?>">

                <button class="btn-danger" style="padding:10px 16px; border-radius:6px; font-size:14px;">
                    Eliminar equipo
                </button>
            </form>

        </div>
    <?php endif; ?>

</div>
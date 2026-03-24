<div class="admin-container">

    <h1 class="admin-title"><?= htmlspecialchars($team['name']) ?></h1>
    <p class="admin-subtitle">Gestión de miembros del equipo.</p>

    <!-- Añadir miembro -->
    <a href="/teams/<?= $team['id'] ?>/add-member"
        class="btn-primary"
        style="margin-bottom:10px; display:inline-block;">
        Añadir miembro
    </a>

    <!-- Eliminar equipo (POST seguro) -->
    <form action="/teams/<?= $team['id'] ?>/delete"
        method="POST"
        style="display:inline;">

        <input type="hidden" name="team_id" value="<?= $team['id'] ?>">

        <button type="submit"
            class="btn-danger"
            style="margin-left:10px;"
            onclick="return confirm('¿Seguro que quieres eliminar este equipo? Esta acción no se puede deshacer.');">
            Eliminar equipo
        </button>
    </form>

    <div class="admin-grid" style="margin-top:20px;">
        <?php foreach ($miembros as $m): ?>
            <div class="admin-card admin-card-static admin-card-user">

                <div class="admin-card-info">
                    <div class="admin-card-title"><?= htmlspecialchars($m['username']) ?></div>
                    <div class="admin-card-desc"><?= htmlspecialchars($m['email']) ?></div>
                </div>

                <!-- Eliminar miembro (POST seguro) -->
                <form action="/teams/<?= $team['id'] ?>/remove-member/<?= $m['id'] ?>"
                    method="POST"
                    style="display:inline;">

                    <button type="submit"
                        class="btn-danger"
                        onclick="return confirm('¿Seguro que quieres eliminar este miembro del equipo?');">
                        Eliminar
                    </button>
                </form>

            </div>
        <?php endforeach; ?>
    </div>

</div>
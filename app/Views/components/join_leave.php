<?php if ($selected_team): ?>

<div class="card">
    <h3 class="card-title">Acceso y Participación</h3>

    <form method="POST">
        <input type="hidden" name="team_id" value="<?= $selected_team['id'] ?>">

        <?php if ($es_miembro): ?>
            <button type="submit" name="leave_team" class="btn btn-danger" style="width:100%">
                Abandonar Proyecto
            </button>
        <?php else: ?>
            <button type="submit" name="join_team" class="btn btn-primary" style="width:100%">
                Unirse al Proyecto
            </button>
        <?php endif; ?>
    </form>
</div>

<?php endif; ?>

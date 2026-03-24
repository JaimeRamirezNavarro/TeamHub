<?php ob_start(); ?>

<div class="admin-container">

    <h1 class="admin-title">Añadir miembros a <?= htmlspecialchars($team['name']) ?></h1>
    <p class="admin-subtitle">Selecciona uno o varios usuarios para añadirlos al equipo.</p>

    <form action="/teams/<?= $team['id'] ?>/add-member/store" method="POST">

        <div class="user-select-list">
            <?php foreach ($users as $u): ?>
                <label class="user-select-item">
                    <input type="checkbox" name="user_ids[]" value="<?= $u['id'] ?>">

                    <div class="user-select-info">
                        <div class="user-select-name"><?= htmlspecialchars($u['username']) ?></div>
                        <div class="user-select-email"><?= htmlspecialchars($u['email']) ?></div>
                    </div>
                </label>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="btn-primary" style="margin-top:20px;">
            Añadir seleccionados
        </button>

        <a href="/teams/<?= $team['id'] ?>" class="btn-danger" style="margin-left:10px;">
            Cancelar
        </a>

    </form>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/main.php';
?>
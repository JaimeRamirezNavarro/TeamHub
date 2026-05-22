<?php if (in_array($_SESSION['user']['role'], ['admin', 'manager'])): ?>

    <hr style="margin:30px 0; border-top:1px solid var(--border-light);">

    <h2 class="settings-title">Token de GitHub</h2>
    <p class="settings-subtitle">
        Este token se usa para leer commits, issues y pull requests del repositorio.
        Solo lectura. Nunca se comparte con otros usuarios.
    </p>

    <form method="POST" action="<?= BASE_PATH ?>/teams/save-github-token" class="settings-form">

        <input type="hidden" name="team_id" value="<?= $selected_team['id'] ?>">

        <label for="github_token">Token personal de GitHub:</label>

        <input type="password"
            id="github_token"
            name="github_token"
            class="settings-input"
            placeholder="Introduce tu token de GitHub">

        <button type="submit" class="settings-btn">
            Guardar Token
        </button>

        <?php if (!empty($selected_team['github_token'])): ?>
            <button type="submit" name="remove_github_token" class="settings-btn danger">
                Quitar Token
            </button>
        <?php endif; ?>

    </form>

<?php endif; ?>
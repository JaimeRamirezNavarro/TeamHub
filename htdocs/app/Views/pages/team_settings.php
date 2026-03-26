<div class="team-settings-container">

    <h1 class="settings-title">Configuración del Equipo</h1>
    <p class="settings-subtitle">Gestiona la integración con GitHub</p>

    <form method="POST" class="settings-form">

        <label for="github_repo">Repositorio GitHub (usuario/repo):</label>

        <input type="text"
               id="github_repo"
               name="github_repo"
               placeholder="ej: laravel/laravel"
               value="<?= htmlspecialchars($selected_team['github_repo'] ?? '') ?>"
               class="settings-input">

        <button type="submit" name="save_github_repo" class="settings-btn">
            Guardar
        </button>

        <?php if (!empty($selected_team['github_repo'])): ?>
            <button type="submit" name="remove_github_repo" class="settings-btn danger">
                Quitar repositorio
            </button>
        <?php endif; ?>

    </form>

</div>

<?php if ($selected_team && $user_role === 'admin'): ?>

    <div class="card">
        <h3 class="card-title">Gestión del Proyecto (Manager)</h3>

        <!-- Cambiar estado del proyecto -->
        <form method="POST" class="status-form">
            <input type="hidden" name="team_id" value="<?= $selected_team['id'] ?>">

            <div style="display:flex; align-items:center; gap:12px; flex-grow:1;">
                <label for="status" class="form-label" style="margin-bottom:0;">Estado:</label>

                <select name="new_status" id="status" class="form-select" style="max-width:200px;">
                    <option value="En Progreso" <?= ($selected_team['status'] ?? '') == 'En Progreso' ? 'selected' : '' ?>>En Progreso</option>
                    <option value="Completado" <?= ($selected_team['status'] ?? '') == 'Completado' ? 'selected' : '' ?>>Completado</option>
                    <option value="Pausado" <?= ($selected_team['status'] ?? '') == 'Pausado' ? 'selected' : '' ?>>Pausado</option>
                    <option value="Cancelado" <?= ($selected_team['status'] ?? '') == 'Cancelado' ? 'selected' : '' ?>>Cancelado</option>
                </select>
            </div>

            <button type="submit" name="update_status" class="btn btn-primary">Actualizar</button>
        </form>

        <hr style="border-top:1px solid var(--border-light); margin:24px 0;">


        <!-- GitHub Repo -->
        <h4 style="margin: 0 0 8px 0; color:var(--text-primary); font-size:0.95rem;">
            Repositorio Externo (GitHub)
        </h4>

        <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:12px;">
            Desarrollo en tiempo real. Formato: <code>usuario/repo</code>
        </p>

        <form method="POST" action="/teams/link-github" class="status-form">
            <input type="hidden" name="team_id" value="<?= $selected_team['id'] ?>">

            <input type="text" name="github_repo" class="form-input"
                placeholder="Ej. facebook/react"
                value="<?= htmlspecialchars($selected_team['github_repo'] ?? '') ?>">

            <button type="submit" name="update_github" class="btn btn-dark">
                Guardar
            </button>

            <?php if (!empty($selected_team['github_repo'])): ?>
                <button type="submit" name="remove_github_repo" class="btn btn-danger" style="margin-left:10px;">
                    Quitar Repositorio
                </button>
            <?php endif; ?>
        </form>


        <hr style="border-top:1px solid var(--border-light); margin:24px 0;">


        <!-- Token GitHub -->
        <h4 style="margin: 0 0 8px 0; color:var(--text-primary); font-size:0.95rem;">
            Token de GitHub (solo lectura)
        </h4>

        <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:12px;">
            Necesario para evitar límites de GitHub y acceder a repos privados.
        </p>

        <form method="POST" action="/teams/save-github-token" class="status-form">
            <input type="hidden" name="team_id" value="<?= $selected_team['id'] ?>">

            <input type="password" name="github_token" class="form-input"
                placeholder="Introduce tu token personal de GitHub">

            <button type="submit" class="btn btn-dark">Guardar Token</button>

            <?php if (!empty($selected_team['github_token'])): ?>
                <button type="submit" name="remove_github_token" class="btn btn-danger" style="margin-left:10px;">
                    Quitar Token
                </button>
            <?php endif; ?>
        </form>

    </div>

<?php endif; ?>
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

        <button type="submit" name="update_status" class="btn btn-primary" style="margin-top: 16px;">Actualizar</button>
    </form>

    <hr style="border-top:1px solid var(--border-light); margin:24px 0;">

    <!-- GitHub Repo -->
    <h4 style="margin: 0 0 8px 0; color:var(--text-primary); font-size:0.95rem;">
        Repositorio Externo (GitHub)
    </h4>

    <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:12px;">
        Desarrollo en tiempo real. Formato: <code>usuario/repo</code>
    </p>

    <form method="POST" class="status-form">
        <input type="hidden" name="team_id" value="<?= $selected_team['id'] ?>">
        <input type="hidden" name="update_github" value="1">

        <input type="text" name="github_repo" class="form-input"
               placeholder="Ej. facebook/react"
               value="<?= htmlspecialchars($selected_team['github_repo'] ?? '') ?>">

        <button type="submit" class="btn btn-dark" style="margin-top: 16px;">Guardar</button>
    </form>
</div>

<?php endif; ?>

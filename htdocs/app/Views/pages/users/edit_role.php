<div class="admin-container">

    <h1 class="admin-title">
        Cambiar rol de <?= htmlspecialchars($usuarioEditar['username']) ?>
    </h1>

    <p class="admin-subtitle">
        Rol actual: <strong><?= htmlspecialchars($usuarioEditar['role']) ?></strong>
    </p>

    <form action="/users/update-role" method="POST" style="margin-top:20px; max-width:320px;">

        <input type="hidden" name="user_id" value="<?= $usuarioEditar['id'] ?>">

        <label for="role">Nuevo rol</label>
        <select name="role" id="role" class="select-input" style="margin-top:6px;">
            <option value="user" <?= $usuarioEditar['role'] === 'user' ? 'selected' : '' ?>>Usuario</option>
            <option value="manager" <?= $usuarioEditar['role'] === 'manager' ? 'selected' : '' ?>>Manager</option>
            <option value="admin" <?= $usuarioEditar['role'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
        </select>

        <button class="btn-primary" style="margin-top:15px;">
            Guardar cambios
        </button>
    </form>

</div>
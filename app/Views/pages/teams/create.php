<div class="admin-container">

    <h1 class="admin-title">Crear nuevo proyecto</h1>
    <p class="admin-subtitle">Completa la información para registrar un nuevo proyecto.</p>

    <div class="form-card">

        <form action="<?= BASE_PATH ?>/teams/store" method="POST">

            <div class="form-group">
                <label for="name">Nombre del proyecto</label>
                <input type="text" name="name" id="name" required>
            </div>

            <div class="form-group">
                <label for="description">Descripción</label>
                <textarea name="description" id="description" rows="4"></textarea>
            </div>

            <button type="submit" class="btn-primary">Crear proyecto</button>

        </form>

    </div>

</div>
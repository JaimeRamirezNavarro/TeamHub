<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TeamHub | Acceso</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/login.css">

</head>

<body>
    <div class="container">
        <div class="logo"><span>TeamHub</span></div>

        <?php if ($error) echo "<div class='msg error'>$error</div>"; ?>
        <?php if ($success) echo "<div class='msg success'>$success</div>"; ?>

        <!-- NORMAL MODE -->
        <div class="tabs">
            <div class="tab <?= $active_tab == 'login' ? 'active' : '' ?>" onclick="switchTab('login')">Iniciar Sesión</div>
            <div class="tab <?= $active_tab == 'register' ? 'active' : '' ?>" onclick="switchTab('register')">Registrarse</div>
        </div>

        <div id="login-form" class="form-section <?= $active_tab == 'login' ? 'active' : '' ?>">
            <form method="POST">
                <input type="text" name="identifier" placeholder="Correo electrónico o Usuario" required>
                <input type="password" name="password" placeholder="Contraseña" required>

                <div style="display: flex; align-items: center; margin: 15px 0; color: #aaa; font-size: 0.9rem;">
                    <input type="checkbox" name="remember_me" id="remember_me" checked style="width: auto; margin-right: 8px;">
                    <label for="remember_me">No cerrar sesión</label>
                </div>

                <button type="submit" name="login" class="btn btn-primary">Entrar</button>
            </form>
        </div>

        <div id="register-form" class="form-section <?= $active_tab == 'register' ? 'active' : '' ?>">
            <form method="POST">
                <input type="text" name="username" placeholder="Nombre de usuario" required>
                <input type="email" name="email" placeholder="Correo electrónico" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <button type="submit" name="register" class="btn btn-primary" style="background: #4CAF50;">Crear Cuenta</button>
            </form>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            event.target.classList.add('active');
            document.querySelectorAll('.form-section').forEach(f => f.classList.remove('active'));
            document.getElementById(tab + '-form').classList.add('active');
        }
    </script>
</body>

</html>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>TeamHub | Login</title>
    <style>
        body { 
        font-family: sans-serif; 
        background: #121212; 
        display: flex; 
        justify-content: center; 
        align-items: center; 
        height: 100vh; 
        margin: 0; 
        color: white; 
        }
        .login-box { 
        background: #1e1e1e; 
        padding: 30px; 
        border-radius: 10px; 
        border: 1px solid #333; 
        width: 300px; 
        }
        input { 
        width: 100%; 
        padding: 10px; 
        margin: 10px 0; 
        background: #222; 
        border: 1px solid #444; 
        color: white; 
        box-sizing: border-box; 
        }
        button { 
        width: 100%; 
        padding: 10px; 
        background: #2196F3; 
        border: none; 
        color: white; 
        cursor: pointer; 
        border-radius: 5px; 
        }
        .error { 
        color: #ff5252; 
        font-size: 0.8em; 
        margin-bottom: 10px; 
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Bienvenido a TeamHub</h2>
        <?php if(isset($_GET['error'])) echo '<p class="error">Correo o contraseña incorrectos</p>'; ?>
        <form action="../motor/auth.php" method="POST">
            <input type="email" name="email" placeholder="Correo electrónico" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit" name="login">Entrar</button>
        </form>
    </div>
</body>
</html>
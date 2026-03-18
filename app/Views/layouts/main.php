<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'TeamHub' ?></title>

    <!-- Fuentes -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS global -->
    <link rel="stylesheet" href="/assets/css/dashboard.css">

    <!-- CSS adicional por página -->
    <?php if (!empty($extra_css)): ?>
        <link rel="stylesheet" href="<?= $extra_css ?>">
    <?php endif; ?>
</head>

<body class="<?= $theme ?? '' ?>">

    <!-- Sidebar global -->
    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <!-- Contenido principal -->
    <div class="main-content">
        <?= $content ?>
    </div>

    <!-- Scripts globales -->
    <script src="/assets/js/heartbeat.js"></script>
    <script src="/assets/js/dashboard.js"></script>
    <script src="/assets/js/github-widget.js"></script>


</body>

</html>

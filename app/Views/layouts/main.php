<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLogged = isset($_SESSION['user']);
$currentRoute = strtok($_SERVER['REQUEST_URI'], '?');
$publicRoutes = ['/login', '/register'];
$isPublic = in_array($currentRoute, $publicRoutes);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'TeamHub' ?></title>

    <script>
        window.TeamHub_BaseUrl = "<?= BASE_PATH ?>";
        document.addEventListener("DOMContentLoaded", () => {
            const savedTheme = localStorage.getItem("teamhub-theme");
            const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;

            if (savedTheme === "dark" || (!savedTheme && prefersDark)) {
                document.documentElement.classList.add("dark-theme");
                document.body.classList.add("dark-theme");
            }
        });
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/dashboard.css">
    <?php
    $shouldLoadAdminCss =
        strpos($currentRoute, '/admin') === 0 ||
        strpos($currentRoute, '/teams') === 0 ||
        strpos($currentRoute, '/users') === 0;
    ?>

    <?php if ($shouldLoadAdminCss): ?>
        <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/admin.css">
    <?php endif; ?>


    <?php if (!empty($extra_css)): ?>
        <link rel="stylesheet" href="<?= BASE_PATH ?><?= $extra_css ?>">
    <?php endif; ?>
</head>

<body>

    <?php if ($isLogged && !$isPublic): ?>
        <?php include __DIR__ . '/../components/sidebar.php'; ?>
    <?php endif; ?>

    <div class="main-content">
        <?= $content ?>
    </div>

    <!-- Scripts globales -->
    <script src="<?= BASE_PATH ?>/assets/js/theme.js"></script>





    <?php if ($isLogged && !$isPublic): ?>
        <script src="<?= BASE_PATH ?>/assets/js/heartbeat.js"></script>
        <script src="<?= BASE_PATH ?>/assets/js/dashboard.js"></script>

        <script src="<?= BASE_PATH ?>/assets/js/github-widget.js"></script>
    <?php endif; ?>

</body>

</html>
<?php
if (!defined('BASE_PATH')) {
    define('BASE_PATH', str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']));
}

spl_autoload_register(function ($class) {

    $base = __DIR__ . '/app';

    $paths = [
        "/Models/$class.php",
        "/Controllers/$class.php",
        "/Database/$class.php",
        "/Services/$class.php"
    ];

    foreach ($paths as $path) {
        $file = $base . $path;
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

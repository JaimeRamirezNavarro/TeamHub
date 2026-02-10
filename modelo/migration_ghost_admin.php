<?php
require_once __DIR__ . '/../motor/db.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. Add 'role' column to users if it doesn't exist
    $check = $db->query("SHOW COLUMNS FROM users LIKE 'role'");
    if ($check->rowCount() == 0) {
        $db->exec("ALTER TABLE users ADD COLUMN role ENUM('admin', 'user') DEFAULT 'user' AFTER email");
        echo "<p>Columna 'role' añadida a 'users'.</p>";
    }

    // 2. Set Admin user to global 'admin' role
    $stmt = $db->prepare("UPDATE users SET role = 'admin' WHERE email = ?");
    $stmt->execute(['admin@teamhub.com']);
    echo "<p>Rol global de Admin asignado a 'admin@teamhub.com'.</p>";

    // 3. Remove Admin from all teams (Ghost Mode)
    // First get Admin ID
    $stmtUser = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmtUser->execute(['admin@teamhub.com']);
    $adminId = $stmtUser->fetchColumn();

    if ($adminId) {
        $stmtDelete = $db->prepare("DELETE FROM team_members WHERE user_id = ?");
        $stmtDelete->execute([$adminId]);
        echo "<p>Admin eliminado de las listas de miembros (Modo Fantasma activado).</p>";
    }

    echo "<h1>Migración Ghost Admin completada.</h1>";

} catch (Exception $e) {
    echo "<h1>Error en migración</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}

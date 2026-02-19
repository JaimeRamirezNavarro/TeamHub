<?php
require_once __DIR__ . '/../modelo/consultas.php';
require_once __DIR__ . '/../motor/db.php';

try {
    $db = Database::getInstance()->getConnection();

    // 1. Set Elena (ID 5) to 'Oficina' (safe value)
    $db->exec("UPDATE users SET status = 'Oficina' WHERE id = 5");
    echo "Set Elena to Oficina.\n";

    // 2. Alter the table to fix the ENUM definition to UTF-8
    $sql = "ALTER TABLE users MODIFY COLUMN status ENUM('Oficina', 'Teletrabajo', 'Ausente', 'Reunión', 'Desconectado', 'En Gather') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Oficina'";
    $db->exec($sql);
    echo "Altered ENUM definition.\n";

    // 3. Set Elena back to 'Reunión'
    $db->exec("UPDATE users SET status = 'Reunión' WHERE id = 5");
    echo "Set Elena to Reunión.\n";

    // Verify
    $stmt = $db->query("SELECT username, status FROM users WHERE id = 5");
    $user = $stmt->fetch();
    echo "Elena status: " . $user['status'] . " (Hex: " . bin2hex($user['status']) . ")\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

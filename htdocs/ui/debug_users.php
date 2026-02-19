<?php
require_once __DIR__ . '/../modelo/consultas.php';
require_once __DIR__ . '/../motor/db.php';

try {
    $db = Database::getInstance()->getConnection();

    // Check Users
    $stmt = $db->query("SELECT id, username, status FROM users");
    $users = $stmt->fetchAll();

    echo "<pre>";
    foreach ($users as $u) {
        echo "User: " . $u['username'] . " | Status: " . $u['status'] . " | Hex: " . bin2hex($u['status']) . "\n";
    }
    echo "</pre>";

    // Try to fix Elena's status specifically if it is broken
    // 'Reunión' in UTF-8 is 5265756e69c3b36e
    // If it's corrupted it might be 5265756e693f3f6e or similar

    $stmt = $db->prepare("UPDATE users SET status = 'Reunión' WHERE id = 5"); // Elena
    $stmt->execute();
    echo "Forced update for Elena's status.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

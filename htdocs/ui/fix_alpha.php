<?php
require_once __DIR__ . '/../modelo/consultas.php';
require_once __DIR__ . '/../motor/db.php';

try {
    $db = Database::getInstance()->getConnection();

    // Fix Proyecto Alpha (ID 1)
    // Current corrupted: "Desarrollo de API REST para integraci??n con sistemas externos"
    // Target: "Desarrollo de API REST para integración con sistemas externos"

    $stmt = $db->prepare("UPDATE teams SET description = ? WHERE id = 1");
    $stmt->execute(['Desarrollo de API REST para integración con sistemas externos']);
    echo "Fixed Proyecto Alpha (ID 1)\n";

    // Verify
    $stmt = $db->prepare("SELECT description FROM teams WHERE id = 1");
    $stmt->execute();
    $desc = $stmt->fetchColumn();
    echo "New Desc: " . $desc . "\n";
    echo "Hex: " . bin2hex($desc) . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

<?php
require_once 'motor/db.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if user exists
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute(['admin@teamhub.com']);
    
    if ($stmt->fetch()) {
        echo "<h1>El usuario ya existe</h1>";
    } else {
        // Insert test user
        $sql = "INSERT INTO users (username, email, password, status) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        // Note: Storing plain text password as per current auth implementation in consultas.php
        $stmt->execute(['Admin', 'admin@teamhub.com', '1234', 'Oficina']);
        
        echo "<h1>Usuario creado correctamente</h1>";
    }
    
    echo "<p>Email: admin@teamhub.com</p>";
    echo "<p>Password: 1234</p>";
    
} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}

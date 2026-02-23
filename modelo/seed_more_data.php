<?php
require_once __DIR__ . '/../motor/db.php';

try {
    $db = Database::getInstance()->getConnection();

    // 1. Create New Users
    $newUsers = [
        ['Laura', 'laura@teamhub.com', '1234', 'Oficina'],
        ['David', 'david@teamhub.com', '1234', 'Teletrabajo'],
        ['Elena', 'elena@teamhub.com', '1234', 'Ausente'],
        ['Sergio', 'sergio@teamhub.com', '1234', 'Oficina'],
        ['Patricia', 'patricia@teamhub.com', '1234', 'Teletrabajo']
    ];

    $passHash = password_hash('1234', PASSWORD_DEFAULT);
    $stmtInsertUser = $db->prepare("INSERT IGNORE INTO users (username, email, password, status) VALUES (?, ?, ?, ?)");

    echo "<h3>Creando Usuarios...</h3>";
    foreach ($newUsers as $u) {
        $stmtInsertUser->execute([$u[0], $u[1], $passHash, $u[3]]);
        echo "Usuario: {$u[0]} ({$u[1]}) - OK<br>";
    }

    // 2. Get All Users (excluding Global Admin) and Teams
    $users = $db->query("SELECT id, username FROM users WHERE role != 'admin' OR role IS NULL")->fetchAll(PDO::FETCH_ASSOC);
    $teams = $db->query("SELECT id, name FROM teams")->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>Asignando Roles...</h3>";
    
    // Clear existing members to start fresh assignment for this demo (optional, but cleaner)
    $db->exec("DELETE FROM team_members"); 
    
    $stmtJoin = $db->prepare("INSERT IGNORE INTO team_members (user_id, team_id, role) VALUES (?, ?, ?)");

    foreach ($teams as $team) {
        echo "<h4>Proyecto: {$team['name']}</h4>";
        
        // Shuffle users to assign random roles per team
        shuffle($users);
        $teamUsers = array_slice($users, 0, rand(3, 5)); // Pick 3 to 5 users for this team

        // First one is the Manager
        $manager = $teamUsers[0];
        $stmtJoin->execute([$manager['id'], $team['id'], 'admin']);
        echo "- 👑 <strong>Jefe:</strong> {$manager['username']}<br>";

        // Rest are Workers
        for ($i = 1; $i < count($teamUsers); $i++) {
            $stmtJoin->execute([$teamUsers[$i]['id'], $team['id'], 'member']);
            echo "- 👷 Trabajador: {$teamUsers[$i]['username']}<br>";
        }
    }

    echo "<h1>Datos poblados correctamente.</h1>";

} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}

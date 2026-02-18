<?php
/**
 * API Endpoint: Get Online Users (TeamHub + Gather Context)
 * Returns a JSON list of users for the Presence Widget.
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

// Error handling to prevent HTML leaking into JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../modelo/consultas.php';

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('No autenticado', 401);
    }
    
    $userId = $_SESSION['user_id'];
    $consultas = new Consultas();
    $db = Database::getInstance()->getConnection();
    
    // 1. Get Current User's Active Project (with Gather info)
    // We prioritize the project the user is most recently interacting with or just the first one found.
    // Optimized query to fetch team details
    $stmt = $db->prepare("
        SELECT t.id, t.name, t.gather_space_id, t.gather_space_url, t.gather_enabled
        FROM teams t
        JOIN team_members tm ON t.id = tm.team_id
        WHERE tm.user_id = ?
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $team = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Default Response Structure
    $response = [
        'success' => true,
        'space_id' => null,
        'space_url' => null,
        'gather_enabled' => false,
        'online_count' => 0,
        'users' => []
    ];
    
    if (!$team) {
        // User not in any team, just show global online users
        $onlineUsers = $consultas->obtenerUsuariosOnline(5); // 5 min window
        
        $response['users'] = formatUsers($onlineUsers);
        $response['online_count'] = count($response['users']);
        echo json_encode($response);
        exit;
    }
    
    // 2. Fetch Team Members who are Online
    // Window: 5 minutes for 'online', up to 60 mins for 'away' maybe?
    // Start with 5 min active
    $stmt = $db->prepare("
        SELECT u.id, u.username, u.email, u.status, u.last_activity,
               TIMESTAMPDIFF(MINUTE, u.last_activity, NOW()) as minutes_idle
        FROM users u
        JOIN team_members tm ON u.id = tm.user_id
        WHERE tm.team_id = ?
        AND u.last_activity >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)
        ORDER BY u.last_activity DESC
    ");
    $stmt->execute([$team['id']]);
    $usersRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 3. Populate Response
    // We explicitly remove Gather info to comply with "remove gather api" request
    $response['space_id'] = null;
    $response['space_url'] = null;
    $response['space_name'] = $team['name'];
    $response['gather_enabled'] = false; // Force false to hide buttons
    
    $response['users'] = formatUsers($usersRaw);
    $response['online_count'] = count($usersRaw); // Count active in last 10 mins
    
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Helper to format user list for the widget
 */
function formatUsers(array $users): array {
    return array_map(function($u) {
        $mins = (int)$u['minutes_idle'];
        $status = 'online';
        $statusText = 'Activo';
        
        // Status determination
        if ($mins > 5) {
            $status = 'away';
            $statusText = "Ausente ($mins min)";
        }
        
        return [
            'name' => $u['username'],
            'email' => $u['email'] ?? '',
            'status' => $status,
            'customStatus' => $statusText,
            'last_activity' => $u['last_activity']
        ];
    }, $users);
}
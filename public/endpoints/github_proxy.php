<?php
//endpoints/github_proxy.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Load TeamModel to get token from DB
require_once __DIR__ . '/../../app/Models/TeamModel.php';

// Get required parameters
$repo = $_GET['repo'] ?? null;
$action = $_GET['action'] ?? null; // 'commits', 'issues', 'pulls', 'branches'
$branch = $_GET['branch'] ?? null;
$team_id = $_GET['team_id'] ?? null;

if (!$repo || !$action || !$team_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan parámetros repo, action o team_id']);
    exit;
}

// Fetch team to get GitHub token
$teamModel = new TeamModel();
$team = $teamModel->obtener($team_id);

if (!$team) {
    http_response_code(400);
    echo json_encode(['error' => 'Equipo no encontrado']);
    exit;
}

$github_token = $team['github_token'] ?? null;


// Allowed actions
$allowed_actions = ['commits', 'issues', 'pulls', 'branches'];
if (!in_array($action, $allowed_actions)) {
    http_response_code(400);
    echo json_encode(['error' => 'Acción no permitida']);
    exit;
}

// Build GitHub API URL
$url = "https://api.github.com/repos/{$repo}/{$action}";
$params = "?per_page=5";

if ($action === 'issues') {
    $params .= "&state=all";
} else if ($action === 'commits' && !empty($branch)) {
    $params .= "&sha=" . urlencode($branch);
}

$url .= $params;

// Prepare cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'TeamHub-App');

$headers = [
    'Accept: application/vnd.github+json'
];

// ⭐ USE TOKEN FROM DATABASE
if (!empty($github_token)) {
    $headers[] = 'Authorization: token ' . $github_token;
}

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(['error' => curl_error($ch)]);
} else {
    http_response_code($httpcode);
    echo $response;
}

curl_close($ch);

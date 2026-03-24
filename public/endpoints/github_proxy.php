<?php
// htdocs/endpoints/github_proxy.php
session_start();
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Get required parameters
$repo = $_GET['repo'] ?? null;
$action = $_GET['action'] ?? null; // 'commits', 'issues', 'pulls'
$branch = $_GET['branch'] ?? null;

if (!$repo || !$action) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan parámetros repo o action']);
    exit;
}

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

// For issues, we want only open ones or maybe all, let's keep it simple
if ($action === 'issues') {
    $params .= "&state=all";
} else if ($action === 'commits' && !empty($branch)) {
    $params .= "&sha=" . urlencode($branch);
}

$url .= $params;

// You can optionally add a personal access token here if the repo is private or you hit rate limits
$github_token = getenv('GITHUB_TOKEN') ?: null;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'TeamHub-App');
// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Enable if local dev has cert issues

$headers = [
    'Accept: application/vnd.github.v3+json'
];

if ($github_token) {
    $headers[] = 'Authorization: token ' . $github_token;
}

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if(curl_errno($ch)){
    http_response_code(500);
    echo json_encode(['error' => curl_error($ch)]);
} else {
    http_response_code($httpcode);
    echo $response;
}

curl_close($ch);
?>

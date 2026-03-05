<?php
// htdocs/endpoints/roadmap_generator.php
ob_start(); // Iniciar buffer para evitar espacios/warnings residuales
error_reporting(0); // Evitar que cualquier warning ensucie el JSON
session_start();
header('Content-Type: application/json');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    ob_clean();
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../modelo/consultas.php';
$consultas = new Consultas();

$team_id = isset($_GET['team_id']) ? (int)$_GET['team_id'] : 0;
$force_refresh = isset($_GET['force_refresh']) ? (bool)$_GET['force_refresh'] : false;
if (!$team_id) {
    http_response_code(400);
    ob_clean();
    echo json_encode(['error' => 'Falta team_id']);
    exit;
}

// Load team data
$team = $consultas->obtenerEquipo($team_id);
if (!$team) {
    http_response_code(404);
    ob_clean();
    echo json_encode(['error' => 'Proyecto no encontrado']);
    exit;
}

// Check if we already have a generated roadmap and we are not forcing a refresh
if (!$force_refresh && !empty($team['ai_roadmap'])) {
    $existing_roadmap = json_decode($team['ai_roadmap'], true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($existing_roadmap) && isset($existing_roadmap['phase1'])) {
        ob_clean();
        echo json_encode([
            'roadmap' => $existing_roadmap, 
            'status' => $team['status'], 
            'github' => null, // Opcional: Podrías guardar las stats también si lo deseas
            'ai_generated' => true,
            'cached' => true
        ]);
        exit;
    }
}

// Collect context for the AI
$has_repo = !empty($team['github_repo']);
$db_status = $team['status'] ?? 'En Progreso';
$team_name = $team['name'];
$team_desc = $team['description'];

$github_stats = null;
$github_context_str = "No hay repositorio de GitHub vinculado.";

// Fetch GitHub data if available to feed the AI
if ($has_repo) {
    $repo = $team['github_repo'];
    $commits = [];
    $pulls = [];
    $branches = [];

    // Helper to fetch directly from GitHub to avoid internal HTTP requests
    function fetch_gh_data($action, $repo) {
        // Fetch up to 100 items to give the AI maximum context
        $url = "https://api.github.com/repos/{$repo}/{$action}?per_page=100";
        if ($action === 'issues') $url .= "&state=all";

        $github_token = getenv('GITHUB_TOKEN') ?: null;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'TeamHub-App');
        
        $headers = ['Accept: application/vnd.github.v3+json'];
        if ($github_token) {
            $headers[] = 'Authorization: token ' . $github_token;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode >= 200 && $httpcode < 300 && $response) {
            return json_decode($response, true) ?: [];
        }
        return [];
    }

    $commits_data = fetch_gh_data('commits', $repo);
    $pulls_data = fetch_gh_data('pulls', $repo);
    $branches_data = fetch_gh_data('branches', $repo);
    $issues_data = fetch_gh_data('issues', $repo);

    foreach ($commits_data as $c) {
        if (isset($c['commit']['message'])) {
            $commits[] = "- " . explode("\n", $c['commit']['message'])[0];
        }
    }
    foreach ($pulls_data as $p) {
        if (isset($p['title'])) {
            $state = $p['state'] === 'open' ? 'ABIERTO' : 'CERRADO/MERGEADO';
            $pulls[] = "- [{$state}] " . $p['title'];
        }
    }
    foreach ($branches_data as $b) {
        if (isset($b['name'])) {
            $branches[] = "- " . $b['name'];
        }
    }
    $issues = [];
    foreach ((array)$issues_data as $i) {
        if (isset($i['title']) && !isset($i['pull_request'])) {
            $state = $i['state'] === 'open' ? 'ABIERTO' : 'CERRADO';
            $issues[] = "- [{$state}] " . $i['title'];
        }
    }

    $github_stats = [
        'active' => count($commits_data) > 0,
        'commits' => count($commits_data),
        'prs_closed' => count(array_filter((array)$pulls_data, function($p) { return $p['state'] !== 'open'; }))
    ];

    $commits_txt = implode("\n", $commits);
    $pulls_txt = implode("\n", $pulls);
    $branches_txt = implode("\n", $branches);
    $issues_txt = implode("\n", $issues);

    $github_context_str = "Commits:\n{$commits_txt}\n\nPull Requests:\n{$pulls_txt}\n\nIssues:\n{$issues_txt}\n\nRamas:\n{$branches_txt}";
}

// Prepare the Prompt for Gemini
$gemini_api_key = getenv('GEMINI_API_KEY');

// Manual fallback for Docker containers that don't inject host env vars
if (!$gemini_api_key && file_exists(__DIR__ . '/../../.env')) {
    $env_lines = file(__DIR__ . '/../../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($env_lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        if (trim($name) === 'GEMINI_API_KEY') {
            $gemini_api_key = trim($value);
            break;
        }
    }
}

// Fallback logic if API key isn't set or there's an error
function generate_fallback_roadmap($db_status, $has_repo, $github_stats) {
    // Legacy naive logic
    $roadmap = [
        'phase1' => ['nombre' => 'Configuración y Creación', 'desc' => 'Inicialización del proyecto y repositorio.', 'avance' => 100, 'completado' => true],
        'phase2' => ['nombre' => 'Desarrollo Activo', 'desc' => 'Implementación de características core. Fallback mode.', 'avance' => 0, 'completado' => false],
        'phase3' => ['nombre' => 'Revisión y QA', 'desc' => 'Pruebas y resolución de bugs.', 'avance' => 0, 'completado' => false],
        'phase4' => ['nombre' => 'Cierre y Despliegue', 'desc' => 'Entrega final del producto.', 'avance' => 0, 'completado' => false]
    ];
    
    if ($db_status === 'Cancelado') {
        foreach ($roadmap as &$p) { $p['completado'] = false; $p['avance'] = 0; }
        $roadmap['phase1']['desc'] = 'Proyecto cancelado.';
    } elseif ($db_status === 'Completado') {
        foreach ($roadmap as &$p) { $p['completado'] = true; $p['avance'] = 100; }
    } elseif ($has_repo) {
        $c = $github_stats['commits'];
        $p = $github_stats['prs_closed'];
        if ($c > 0) {
            $roadmap['phase2']['avance'] = min(100, $c * 20);
            if ($roadmap['phase2']['avance'] == 100) $roadmap['phase2']['completado'] = true;
        }
        if ($p > 0) {
            $roadmap['phase2']['avance'] = 100; $roadmap['phase2']['completado'] = true;
            $roadmap['phase3']['avance'] = min(100, $p * 33);
            if ($roadmap['phase3']['avance'] >= 99) $roadmap['phase3']['completado'] = true;
        }
    } else {
        $roadmap['phase2']['avance'] = 50; 
    }
    
    return $roadmap;
}

if (!$gemini_api_key) {
    // Fallback if no key is configured
    $roadmap = generate_fallback_roadmap($db_status, $has_repo, $github_stats);
    echo json_encode(['roadmap' => $roadmap, 'status' => $db_status, 'github' => $github_stats, 'ai_generated' => false]);
    exit;
}

// System Instructions and Prompt
$prompt = <<<EOT
Actúa como un Senior Technical Project Manager sumamente estricto y profesional. Analiza el siguiente proyecto y devuelve EXCLUSIVAMENTE un objeto JSON que contenga una hoja de ruta ("roadmap") técnica y orientada a negocio. NO incluyas formato Markdown rodeando el JSON, solo devuelve el objeto crudo.
La hoja de ruta debe tener exactamente 4 fases ("phase1", "phase2", "phase3", "phase4").
Para cada fase, debes incluir:
- "nombre": Un título formal, técnico y orientado a resultados corporativos.
- "desc": Una descripción sobria, seria y detallada (2 líneas máximo) de los objetivos arquitectónicos o de negocio de esta fase. Usa terminología formal de ingeniería de software. Rechaza cualquier tono coloquial, genérico o lúdico.
- "avance": Un número entero del 0 al 100 indicando el porcentaje de progreso validado.
- "completado": Booleano (true/false) indicando si la fase ha sido formalmente entregada al 100%.

Reglas de rigor analítico:
1. Analiza el estado oficial del proyecto ("$db_status"). Si está completado, todas las fases deben estar al 100%. Si está cancelado, avance 0.
2. Si hay actividad de repositorios (commits, PRs, issues), úsala como única verdad para calcular avances. Si hay arquitecturas avanzadas desplegándose, la fase 2 debe reflejar ese nivel de madurez técnica. 
3. Nombre oficial del proyecto: "$team_name". Resumen ejecutivo: "$team_desc".
4. Evidencia técnica recuperada de GitHub:
$github_context_str

Devuelve única y exclusivamente el JSON con este esquema:
{
  "phase1": { "nombre": "...", "desc": "...", "avance": 100, "completado": true },
  "phase2": { "nombre": "...", "desc": "...", "avance": 40, "completado": false },
  "phase3": { "nombre": "...", "desc": "...", "avance": 0, "completado": false },
  "phase4": { "nombre": "...", "desc": "...", "avance": 0, "completado": false }
}
EOT;

$data = [
    "contents" => [
        [
            "parts" => [
                ["text" => $prompt]
            ]
        ]
    ],
    "generationConfig" => [
        "temperature" => 0.2,
        "responseMimeType" => "application/json"
    ]
];

$ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $gemini_api_key);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$ai_roadmap = null;
$raw_error = '';

if ($httpcode == 200 && $response) {
    $res_data = json_decode($response, true);
    if (isset($res_data['candidates'][0]['content']['parts'][0]['text'])) {
        $json_text = $res_data['candidates'][0]['content']['parts'][0]['text'];
        // Remove rogue markdown blocks
        $json_text = preg_replace('/```json\s*/i', '', $json_text);
        $json_text = preg_replace('/```\s*/', '', $json_text);
        $ai_roadmap = json_decode(trim($json_text), true);
    } else {
        $raw_error = "Gemini API returned malformed response: " . substr($response, 0, 100);
    }
} else {
    $raw_error = "HTTP $httpcode : " . substr($response, 0, 100);
}

ob_clean(); // Limpiar basurita blanca / warnings antes de imprimir el JSON final
if ($ai_roadmap && isset($ai_roadmap['phase1']) && isset($ai_roadmap['phase4'])) {
    
    // Save to DB so we don't regenerate it every time (unless forced)
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE teams SET ai_roadmap = ? WHERE id = ?");
        $stmt->execute([json_encode($ai_roadmap, JSON_UNESCAPED_UNICODE), $team_id]);
    } catch (Exception $e) {
        // Ignorar errores de BD para no romper el response
    }

    echo json_encode(['roadmap' => $ai_roadmap, 'status' => $db_status, 'github' => $github_stats, 'ai_generated' => true, 'raw_response' => $response]);
} else {
    // Fallback if AI fails parsing or empty
    $roadmap = generate_fallback_roadmap($db_status, $has_repo, $github_stats);
    echo json_encode(['roadmap' => $roadmap, 'status' => $db_status, 'github' => $github_stats, 'ai_generated' => false, 'ai_error' => $raw_error]);
}

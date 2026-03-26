<?php
// tests/load_test_roadmap.php

$url_login = "http://localhost/endpoints/login_action.php"; // Update if your action path differs
$url_roadmap = "http://localhost/endpoints/roadmap_generator.php?team_id=1";
$url_roadmap_force = "http://localhost/endpoints/roadmap_generator.php?team_id=1&force_refresh=true";

$concurrent_requests = 50;

echo "1. Getting Session Cookie...\n";
// First, log in to get a cookie
$chInit = curl_init("http://localhost/ui/login.php");
curl_setopt($chInit, CURLOPT_POST, 1);
curl_setopt($chInit, CURLOPT_POSTFIELDS, http_build_query([
    'identifier' => 'admin@teamhub.com',
    'password' => 'password',
    'login' => '1'
]));
// Usually login forms post to themselves or an action. Here we assume login.php handles it or sets session.
curl_setopt($chInit, CURLOPT_RETURNTRANSFER, true);
curl_setopt($chInit, CURLOPT_HEADER, true);
$response = curl_exec($chInit);
preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $response, $matches);
$cookies = [];
foreach($matches[1] as $item) {
    parse_str($item, $cookie);
    $cookies = array_merge($cookies, $cookie);
}
curl_close($chInit);

$cookie_string = "";
foreach($cookies as $k => $v) {
    $cookie_string .= "$k=$v; ";
}

if (empty($cookie_string) || strpos($cookie_string, 'PHPSESSID') === false) {
    // If login failed or no cookie, let's try writing directly to PHPSESSID if we know we can just hit it.
    echo "Warning: No PHPSESSID found from login. We will try hitting an unprotected route if any, or it might fail with 401.\n";
} else {
    echo "Got Session Cookie: $cookie_string\n";
}

// Function to run concurrent requests
function run_concurrent($url_mode, $cookie_string, $concurrent) {
    $mh = curl_multi_init();
    $curl_array = [];
    
    echo "2. Starting $concurrent concurrent requests to $url_mode...\n";
    $start_time = microtime(true);

    for ($i = 0; $i < $concurrent; $i++) {
        $curl_array[$i] = curl_init($url_mode);
        curl_setopt($curl_array[$i], CURLOPT_RETURNTRANSFER, true);
        if ($cookie_string) curl_setopt($curl_array[$i], CURLOPT_COOKIE, $cookie_string);
        curl_multi_add_handle($mh, $curl_array[$i]);
    }

    $running = null;
    do {
        curl_multi_exec($mh, $running);
        curl_multi_select($mh);
    } while ($running > 0);

    $end_time = microtime(true);
    
    $successes = 0;
    $failures = 0;
    $errors = [];
    $total_time = $end_time - $start_time;

    for ($i = 0; $i < $concurrent; $i++) {
        $httpcode = curl_getinfo($curl_array[$i], CURLINFO_HTTP_CODE);
        $res = curl_multi_getcontent($curl_array[$i]);
        if ($httpcode == 200) {
            $successes++;
        } else {
            $failures++;
            if (!in_array($httpcode, $errors)) $errors[] = $httpcode;
        }
        curl_multi_remove_handle($mh, $curl_array[$i]);
    }
    curl_multi_close($mh);

    echo "--- RESULTS ---\n";
    echo "Total Time: " . number_format($total_time, 2) . " seconds\n";
    echo "Requests/Second: " . number_format($concurrent / $total_time, 2) . "\n";
    echo "Successes (200 OK): $successes\n";
    echo "Failures: $failures\n";
    if ($failures > 0) {
        echo "Failure HTTP Codes: " . implode(", ", $errors) . "\n";
        echo "Example Failure Body snippet:\n" . substr($res, 0, 100) . "\n";
    }
    echo "---------------\n\n";
}

echo "\n=== FINDING MAXIMUM CONCURRENT USERS (Persistent DB mode) ===\n";
$concurrency = 50;
$step = 50;
$max_concurrency_tested = 500;
$breaking_point = null;

while ($concurrency <= $max_concurrency_tested) {
    echo "Testing $concurrency concurrent users...\n";
    
    $mh = curl_multi_init();
    $curl_array = [];
    
    for ($i = 0; $i < $concurrency; $i++) {
        $curl_array[$i] = curl_init($url_roadmap);
        curl_setopt($curl_array[$i], CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_array[$i], CURLOPT_TIMEOUT, 10); // 10 seconds timeout
        if ($cookie_string) curl_setopt($curl_array[$i], CURLOPT_COOKIE, $cookie_string);
        curl_multi_add_handle($mh, $curl_array[$i]);
    }

    $running = null;
    do {
        curl_multi_exec($mh, $running);
        curl_multi_select($mh);
    } while ($running > 0);

    $successes = 0;
    $failures = 0;

    for ($i = 0; $i < $concurrency; $i++) {
        $httpcode = curl_getinfo($curl_array[$i], CURLINFO_HTTP_CODE);
        if ($httpcode == 200) {
            $successes++;
        } else {
            $failures++;
        }
        curl_multi_remove_handle($mh, $curl_array[$i]);
    }
    curl_multi_close($mh);
    
    echo "Result: $successes successes, $failures failures.\n\n";

    if ($failures > 0 || $successes < $concurrency) {
        $breaking_point = $concurrency;
        echo ">>> THE SERVER BROKE AT APPROXIMATELY $concurrency CONCURRENT USERS <<<\n";
        break;
    }
    
    $concurrency += $step;
}

if (!$breaking_point) {
    echo ">>> THE SERVER SURVIVED $max_concurrency_tested CONCURRENT USERS WITHOUT BREAKING. <<<\n";
}

?>

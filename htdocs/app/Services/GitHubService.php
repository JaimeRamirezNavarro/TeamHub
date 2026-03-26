<?php

class GitHubService
{
    private function fetch($repo, $action)
    {
        $url = "https://api.github.com/repos/{$repo}/{$action}?per_page=100";
        if ($action === 'issues') $url .= "&state=all";

        $token = getenv('GITHUB_TOKEN');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'TeamHub-App');

        $headers = ['Accept: application/vnd.github.v3+json'];
        if ($token) $headers[] = "Authorization: token {$token}";

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($code >= 200 && $code < 300) ? json_decode($response, true) : [];
    }

    public function getStats($repo)
    {
        $commits  = $this->fetch($repo, 'commits');
        $pulls    = $this->fetch($repo, 'pulls');
        $issues   = $this->fetch($repo, 'issues');

        return [
            'commits'     => count($commits),
            'prs_closed'  => count(array_filter($pulls, fn($p) => $p['state'] !== 'open')),
            'active'      => count($commits) > 0,
            'raw'         => compact('commits', 'pulls', 'issues')
        ];
    }

    public function buildContextString($stats)
    {
        $c = $stats['raw']['commits'];
        $p = $stats['raw']['pulls'];
        $i = $stats['raw']['issues'];

        $commits = array_map(fn($x) => "- " . $x['commit']['message'], $c);
        $pulls   = array_map(fn($x) => "- [{$x['state']}] {$x['title']}", $p);
        $issues  = array_map(fn($x) => "- [{$x['state']}] {$x['title']}", $i);

        return "Commits:\n" . implode("\n", $commits)
            . "\n\nPull Requests:\n" . implode("\n", $pulls)
            . "\n\nIssues:\n" . implode("\n", $issues);
    }
}

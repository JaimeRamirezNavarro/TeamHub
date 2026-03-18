<?php

require_once __DIR__ . '/../Models/TeamModel.php';
require_once __DIR__ . '/GitHubService.php';
require_once __DIR__ . '/GeminiService.php';

class RoadmapService
{
    private $teamModel;
    private $github;
    private $gemini;

    public function __construct()
    {
        $this->teamModel = new TeamModel();
        $this->github = new GitHubService();
        $this->gemini = new GeminiService();
    }

    public function generateRoadmap($team_id, $force_refresh = false)
    {
        if (!$team_id) {
            throw new Exception("Falta team_id");
        }

        $team = $this->teamModel->getTeam($team_id);
        if (!$team) {
            throw new Exception("Proyecto no encontrado");
        }

        // Si ya existe roadmap y no se fuerza refresco → devolverlo
        if (!$force_refresh && !empty($team['ai_roadmap'])) {
            $decoded = json_decode($team['ai_roadmap'], true);
            if (json_last_error() === JSON_ERROR_NONE && isset($decoded['phase1'])) {
                return [
                    'roadmap' => $decoded,
                    'status'  => $team['status'],
                    'github'  => null,
                    'cached'  => true
                ];
            }
        }

        // Obtener datos de GitHub
        $github_stats = null;
        $github_context = "No hay repositorio de GitHub vinculado.";

        if (!empty($team['github_repo'])) {
            $github_stats = $this->github->getStats($team['github_repo']);
            $github_context = $this->github->buildContextString($github_stats);
        }

        // Generar roadmap con Gemini
        $ai_roadmap = $this->gemini->generateRoadmap(
            $team['status'],
            $team['name'],
            $team['description'],
            $github_context
        );

        // Si Gemini falla → fallback
        if (!$ai_roadmap) {
            $ai_roadmap = $this->fallbackRoadmap($team['status'], $github_stats);
        }

        // Guardar en BD
        $this->teamModel->saveRoadmap($team_id, $ai_roadmap);

        return [
            'roadmap' => $ai_roadmap,
            'status'  => $team['status'],
            'github'  => $github_stats,
            'ai_generated' => true
        ];
    }

    private function fallbackRoadmap($status, $github)
    {
        $roadmap = [
            'phase1' => ['nombre' => 'Inicio', 'desc' => 'Configuración inicial.', 'avance' => 100, 'completado' => true],
            'phase2' => ['nombre' => 'Desarrollo', 'desc' => 'Implementación principal.', 'avance' => 0, 'completado' => false],
            'phase3' => ['nombre' => 'QA', 'desc' => 'Pruebas y validación.', 'avance' => 0, 'completado' => false],
            'phase4' => ['nombre' => 'Entrega', 'desc' => 'Despliegue final.', 'avance' => 0, 'completado' => false],
        ];

        if ($status === 'Completado') {
            foreach ($roadmap as &$p) {
                $p['avance'] = 100;
                $p['completado'] = true;
            }
        }

        return $roadmap;
    }
}

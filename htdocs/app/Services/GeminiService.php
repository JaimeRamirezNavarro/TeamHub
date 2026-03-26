<?php

class GeminiService
{
    public function generateRoadmap($status, $name, $desc, $github_context)
    {
        $key = getenv('GEMINI_API_KEY');
        if (!$key) return null;

        $prompt = <<<EOT
Actúa como un Senior Technical Project Manager y experto QA en arquitecturas de software moderno.
Tu tarea es generar una hoja de ruta (roadmap) altamente profesional y creíble de exactamente 4 fases para un proyecto de software basándote exhaustivamente en:
- Estado general: $status
- Nombre del proyecto: $name
- Descripción: $desc
- Actividad en el repositorio (GitHub): $github_context

Reglas inquebrantables:
1. Responde ÚNICAMENTE con un objeto JSON válido.
2. El JSON debe tener exactamente 4 claves: "phase1", "phase2", "phase3", "phase4".
3. Cada fase debe contener: "nombre" (ej. "Arquitectura Base", "MVP Funcional"), "desc" (explicación concisa y técnica), "avance" (número entero de 0 a 100) y "completado" (booleano).
4. El avance debe ser escrupulosamente realista. Analiza estrictamente los commits e issues proporcionados en el contexto de GitHub. Si hay commits recientes relacionados con bases de datos, asume que la fase inicial avanza. Si hay PRs cerrados, avanza las métricas acordemente.
5. Usa lenguaje técnico profesional (evita descripciones genéricas como "implementación principal", busca cosas como "Despliegue de Endpoints y Migraciones DB").

Ejemplos de avance lógicos:
- Si el estado es "Completado", TODAS las fases deben tener avance: 100 y completado: true.
- Si el estado es "En Progreso", deduce el progreso de las fases tempranas evaluando si hay código real subido. ¡Hazlo creíble!

Formato JSON esperado:
{
  "phase1": { "nombre": "...", "desc": "...", "avance": 100, "completado": true },
  "phase2": { "nombre": "...", "desc": "...", "avance": 40, "completado": false },
  ...
}
EOT;

        $payload = [
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

        $ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$key}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200) return null;

        $data = json_decode($response, true);
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$text) return null;

        $text = preg_replace('/```json|```/i', '', $text);
        return json_decode(trim($text), true);
    }
}

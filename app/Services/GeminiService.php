<?php

class GeminiService
{
    public function generateRoadmap($status, $name, $desc, $github_context)
    {
        $key = getenv('GEMINI_API_KEY');
        if (!$key) return null;

        $prompt = <<<EOT
Actúa como un Senior Technical Project Manager y experto en desarrollo de software.
Tu tarea es generar una hoja de ruta (roadmap) de 4 fases para un proyecto de software basado en su estado actual ($status), nombre ($name), descripción ($desc) y actividad reciente de GitHub ($github_context).

Reglas:
1. Responde ÚNICAMENTE con un objeto JSON válido.
2. El JSON debe tener exactamente 4 claves: "phase1", "phase2", "phase3", "phase4".
3. Cada fase debe tener: "nombre", "desc" (una frase corta), "avance" (número del 0 al 100) y "completado" (booleano).
4. El avance debe ser realista basado en el estado y GitHub.

Ejemplos de avance:
- Si el estado es "Completado", todas las fases deben tener avance: 100 y completado: true.
- Si el estado es "En Progreso", estima el avance según el contexto de GitHub.

Formato JSON esperado:
{
  "phase1": { "nombre": "...", "desc": "...", "avance": 0, "completado": false },
  "phase2": { ... },
  "phase3": { ... },
  "phase4": { ... }
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

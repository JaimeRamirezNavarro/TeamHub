<?php

class GeminiService
{
    public function generateRoadmap($status, $name, $desc, $github_context)
    {
        $key = getenv('GEMINI_API_KEY');
        if (!$key) return null;

        $prompt = <<<EOT
Actúa como un Senior Technical Project Manager...
(usa aquí tu prompt completo)
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

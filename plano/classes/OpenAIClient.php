<?php
require_once '../includes/auth.php'; // Access auth to get key from DB
require_once '../config/db.php';     // Need DB to fetch key

class OpenAIClient
{
    public static function call($systemPrompt, $userPrompt)
    {
        global $pdo; // Use the global PDO connection created in db.php

        // Fetch API Key from DB
        $apiKey = getSystemApiKey($pdo);

        if (empty($apiKey)) {
            return ['error' => 'Chave da API OpenAI não configurada pelo Administrador.'];
        }

        $url = 'https://api.openai.com/v1/chat/completions';

        $data = [
            'model' => 'gpt-4o',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'temperature' => 0.5,
            'response_format' => ['type' => 'json_object']
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            return ['error' => 'Erro Curl: ' . curl_error($ch)];
        }

        curl_close($ch);

        if ($httpCode !== 200) {
            return ['error' => "Erro API ($httpCode): " . $response];
        }

        $decoded = json_decode($response, true);
        if (isset($decoded['choices'][0]['message']['content'])) {
            return json_decode($decoded['choices'][0]['message']['content'], true);
        }

        return ['error' => 'Resposta inválida da API'];
    }
}
?>
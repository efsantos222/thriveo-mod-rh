<?php
// includes/AIHelper.php

class AIHelper
{
    private $api_key;
    private $model;
    private $temperature;
    private $pdo;
    private $id_empresa;

    public function __construct($pdo, $id_empresa)
    {
        $this->pdo = $pdo;
        $this->id_empresa = $id_empresa;
        $this->loadSettings();
    }

    private function loadSettings()
    {
        $stmt = $this->pdo->prepare("SELECT api_key_openai, modelo_preferido, temperatura FROM configuracoes_ia WHERE id_empresa = ?");
        $stmt->execute([$this->id_empresa]);
        $config = $stmt->fetch();

        if ($config) {
            $this->api_key = $config['api_key_openai'];
            $this->model = $config['modelo_preferido'] ?? 'gpt-4o';
            $this->temperature = (float) ($config['temperatura'] ?? 0.7);
        }
    }

    public function generateResponse($system_prompt, $user_prompt, $v2mom_id = null, $interaction_type = 'generic')
    {
        if (empty($this->api_key)) {
            return ['error' => 'API Key não configurada para esta empresa.'];
        }

        $messages = [
            ['role' => 'system', 'content' => $system_prompt],
            ['role' => 'user', 'content' => $user_prompt]
        ];

        $data = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => $this->temperature
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->api_key
        ]);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            return ['error' => 'Erro na conexão com OpenAI: ' . curl_error($ch)];
        }

        curl_close($ch);

        $response = json_decode($result, true);

        if (isset($response['error'])) {
            return ['error' => 'Erro da API OpenAI: ' . $response['error']['message']];
        }

        $content = $response['choices'][0]['message']['content'] ?? '';
        $tokens = $response['usage']['total_tokens'] ?? 0;

        // Log interaction
        $this->logInteraction($v2mom_id, $interaction_type, $user_prompt, $content, $tokens);

        return ['content' => $content, 'tokens' => $tokens];
    }

    private function logInteraction($v2mom_id, $type, $prompt, $response, $tokens)
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO interacoes_ia (id_v2mom, tipo_interacao, prompt, resposta, tokens_usados) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$v2mom_id, $type, $prompt, $response, $tokens]);
        } catch (Exception $e) {
            // Silently fail logging if error, don't stop flow
            error_log("Failed to log AI interaction: " . $e->getMessage());
        }
    }
}
?>
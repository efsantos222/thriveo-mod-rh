<?php
class OpenAIHelper
{
    private $pdo;
    private $apiKey;
    private $model;
    private $temperature;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->loadSettings();
    }

    private function loadSettings()
    {
        $stmt = $this->pdo->query("SELECT * FROM system_settings");
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        $this->apiKey = $settings['openai_api_key'] ?? '';
        $this->model = $settings['openai_model'] ?? 'gpt-4o';
        $this->temperature = (float) ($settings['openai_temperature'] ?? 0.5);
    }

    public function generate($prompt)
    {
        if (empty($this->apiKey)) {
            return "Erro: Chave de API não configurada. Contate o administrador.";
        }

        $url = 'https://api.openai.com/v1/chat/completions';

        $data = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => 'Você é um assistente pedagógico especializado em educação corporativa. Ajude a criar conteúdos didáticos, planos de aula e avaliações.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => $this->temperature
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: ' . "Bearer " . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            return 'Erro na requisição: ' . curl_error($ch);
        }

        curl_close($ch);

        $decoded = json_decode($response, true);

        if (isset($decoded['choices'][0]['message']['content'])) {
            return $decoded['choices'][0]['message']['content'];
        } else if (isset($decoded['error']['message'])) {
            return "Erro da API: " . $decoded['error']['message'];
        } else {
            return "Erro desconhecido ao processar resposta da IA.";
        }
    }
}
?>
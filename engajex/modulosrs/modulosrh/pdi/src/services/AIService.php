<?php
namespace PDI\services;

use PDI\models\SystemSetting;

class AIService
{
    private $settings;

    public function __construct(SystemSetting $settings)
    {
        $this->settings = $settings;
    }

    public function generatePDI($userData, $companyData)
    {
        $apiKey = $this->settings->get('openai_api_key');
        if (!$apiKey) {
            throw new \Exception("OpenAI API Key not configured.");
        }

        $prompt = $this->buildPrompt($userData, $companyData);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.openai.com/v1/chat/completions",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => 'Você é um consultor especialista em RH focado em Planos de Desenvolvimento Individual (PDI). Responda sempre em Português do Brasil.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.7
            ]),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $apiKey",
                "Content-Type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            throw new \Exception("cURL Error: " . $err);
        }

        $result = json_decode($response, true);

        if (isset($result['error'])) {
            throw new \Exception("OpenAI Error: " . $result['error']['message']);
        }

        return $result['choices'][0]['message']['content'] ?? '';
    }

    private function buildPrompt($userData, $companyData)
    {
        $prompt = "Crie um PDI estruturado para o seguinte colaborador:\n";
        $prompt .= "Nome: " . ($userData['name'] ?? 'Não informado') . "\n";
        $prompt .= "Cargo: " . ($userData['job_title'] ?? 'Não informado') . "\n";

        if ($companyData) {
            $prompt .= "Missão da Empresa: " . ($companyData['identity_mission'] ?? 'Não informada') . "\n";
            $prompt .= "Valores da Empresa: " . ($companyData['identity_values'] ?? 'Não informados') . "\n";
        }

        $prompt .= "Avaliação de Competências: " . ($userData['competency_eval'] ?? 'Não informada') . "\n";
        $prompt .= "Recomendações do Gestor: " . ($userData['manager_recommendations'] ?? 'Não informada') . "\n";
        $prompt .= "Demandas de Melhoria: " . ($userData['improvement_demands'] ?? 'Não informada') . "\n";

        $prompt .= "\nPor favor, forneça um plano de desenvolvimento prático, com ações de curto, médio e longo prazo, focando nas competências a desenvolver e alinhado aos valores da empresa.";

        return $prompt;
    }
}

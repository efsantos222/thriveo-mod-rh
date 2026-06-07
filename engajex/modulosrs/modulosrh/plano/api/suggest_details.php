<?php
require_once '../classes/OpenAIClient.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$serviceName = $input['service_name'] ?? '';

if (empty($serviceName)) {
    echo json_encode(['error' => 'Nome do serviço é obrigatório']);
    exit;
}

$systemPrompt = "Você é um consultor estrategista de TI sênior. Sua tarefa é auxiliar no preenchimento de detalhes de um serviço de TI para uma matriz de portfólio.
Responda APENAS em formato JSON com os campos: description, revenue_model, target_audience, key_resources.
Use Português do Brasil. Seja conciso e profissional.";

$userPrompt = "Sugira detalhes para o serviço de TI chamado: '{$serviceName}'.
Gere:
1. Uma descrição curta e estratégica.
2. Um modelo de receita típico para este serviço.
3. O público-alvo principal.
4. Os recursos principais (humanos e tecnológicos) necessários.";

$result = OpenAIClient::call($systemPrompt, $userPrompt);
echo json_encode($result);
?>
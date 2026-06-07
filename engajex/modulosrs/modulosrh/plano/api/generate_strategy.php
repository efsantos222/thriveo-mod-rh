<?php
require_once '../config/db.php';
require_once '../classes/OpenAIClient.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? null;

if (!$id) {
    echo json_encode(['error' => 'ID do serviço não fornecido.']);
    exit;
}

// 1. Fetch Service Details
$stmt = $pdo->prepare("SELECT * FROM portfolio_services WHERE id = ?");
$stmt->execute([$id]);
$service = $stmt->fetch();

if (!$service) {
    echo json_encode(['error' => 'Serviço não encontrado.']);
    exit;
}

// 2. Determine Context
$maturity = floatval($service['maturity_avg']);
$synergy = floatval($service['synergy_avg']);
$currentQuadrant = '';

if ($synergy >= 5 && $maturity >= 5)
    $currentQuadrant = "Estrela (Alta Sinergia, Alta Maturidade)";
elseif ($synergy < 5 && $maturity >= 5)
    $currentQuadrant = "Gerador de Caixa (Baixa Sinergia, Alta Maturidade)";
elseif ($synergy >= 5 && $maturity < 5)
    $currentQuadrant = "Motor (Alta Sinergia, Baixa Maturidade)";
else
    $currentQuadrant = "Explorador (Baixa Sinergia, Baixa Maturidade)";

// 3. Helper for scoring details
$scores = "
Maturidade (Média: $maturity): Padronização({$service['score_standardization']}), Escalabilidade({$service['score_scalability']}), Margem({$service['score_margin']}), Automação({$service['score_automation']}).
Sinergia (Média: $synergy): Leads({$service['score_lead_gen']}), Relacionamento({$service['score_relationship']}), Cross-sell({$service['score_cross_sell']}), Marca({$service['score_brand_value']}).
";

$systemPrompt = "Você é um Chief Strategy Officer (CSO) especializado em crescimento de empresas de TI.
Seu objetivo é criar um PLANO DE AÇÃO ESTRATÉGICO para transformar um serviço específico.
O foco final é SEMPRE levar o serviço para o quadrante 'Estrela' (Crescimento e Liderança) ou 'Gerador de Caixa' (Alta Rentabilidade), ampliando mercados, clientes e receitas.
IMPORTANTE: Sua resposta deve ser um OBJETO JSON contendo apenas a chave 'html_content'.
O valor de 'html_content' deve ser o texto do plano em HTML formatado (sem tags <html> ou <body>, apenas o conteúdo de uma div). Use <h3> para títulos e <ul>/<li> para listas.";

$userPrompt = "
Serviço: {$service['name']}
Descrição: {$service['description']}
Público Alvo: {$service['target_audience']}
Status Atual: $currentQuadrant
Detalhamento dos Scores: $scores

Gere 3 Objetivos Estratégicos Claves para alavancar este serviço.
Para cada objetivo, sugira 2 ações táticas práticas.
Foque explicitamente em:
1. Como aumentar a Maturidade Operacional (se estiver baixa).
2. Como aumentar a Sinergia Estratégica (se estiver baixa).
3. Como ampliar mercado e receitas imediatamente.

Responda exclusivamente em JSON.";

// 4. Call AI
$aiResult = OpenAIClient::call($systemPrompt, $userPrompt);

$planHtml = $aiResult['html_content'] ?? '<p>Não foi possível gerar o plano. Tente novamente.</p>';

// If pure text was returned (fallback), try to wrap it
if (!isset($aiResult['html_content']) && isset($aiResult['content'])) {
    $planHtml = $aiResult['content'];
}

// 5. Save to Database
$update = $pdo->prepare("UPDATE portfolio_services SET strategic_plan = ? WHERE id = ?");
$update->execute([$planHtml, $id]);

echo json_encode(['success' => true, 'plan' => $planHtml]);
?>
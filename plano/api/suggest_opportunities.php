<?php
require_once '../config/db.php';
require_once '../classes/OpenAIClient.php';

header('Content-Type: application/json');

// Buscar serviços existentes para contexto
try {
    $stmt = $pdo->query("SELECT name, description FROM portfolio_services");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $services = [];
}

$servicesContext = "Serviços Atuais no Portfólio:\n";
foreach ($services as $s) {
    $servicesContext .= "- {$s['name']}: {$s['description']}\n";
}

$systemPrompt = "Você é um consultor de desenvolvimento de negócios e inovação em TI. Analise o portfólio atual e sugira NOVAS oportunidades de serviços para ampliar mercado, clientes e receitas.
Responda APENAS em formato JSON contendo um array 'suggestions' onde cada objeto tem: 'name', 'description', 'rationale' (porque essa oportunidade é boa).
Gere 3 sugestões estratégicas. Use Português do Brasil.";

$userPrompt = $servicesContext . "\n\nCom base no portfólio acima (se houver), sugira 3 novos serviços complementares ou inovadores que façam sentido comercialmente.";

$result = OpenAIClient::call($systemPrompt, $userPrompt);
echo json_encode($result);
?>
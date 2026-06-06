<?php
// modules/v2mom_ai.php

function getAIConfig($pdo, $companyId)
{
    $stmt = $pdo->prepare("SELECT api_key, model, temperature FROM ai_config WHERE company_id = ?");
    $stmt->execute([$companyId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function callOpenAI($pdo, $companyId, $systemPrompt, $userPrompt, $v2momId = null)
{
    $config = getAIConfig($pdo, $companyId);

    if (!$config || empty($config['api_key'])) {
        return ['error' => 'Chave da API OpenAI não configurada. Vá na aba Configurações.'];
    }

    $apiKey = $config['api_key'];
    $model = $config['model'] ?? 'gpt-4o';
    $temp = (float) ($config['temperature'] ?? 0.7);

    $messages = [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user', 'content' => $userPrompt]
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'model' => $model,
        'messages' => $messages,
        'temperature' => $temp
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);

    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        return ['error' => 'Erro Conexão: ' . curl_error($ch)];
    }
    curl_close($ch);

    $response = json_decode($result, true);

    if (isset($response['error'])) {
        return ['error' => 'OpenAI Error: ' . $response['error']['message']];
    }

    $content = $response['choices'][0]['message']['content'] ?? '';

    // Log
    try {
        $stmt = $pdo->prepare("INSERT INTO v2mom_ai_log (v2mom_id, prompt, response, tokens) VALUES (?, ?, ?, ?)");
        $stmt->execute([$v2momId, $userPrompt, $content, $response['usage']['total_tokens'] ?? 0]);
    } catch (Exception $e) {
    }

    return ['content' => $content];
}

// Prompts Factory
function getPrompts($type, $context = '')
{
    $prompts = [
        'vision' => "Você é um especialista em Planejamento Estratégico (V2MOM). Ajude a criar uma VISÃO inspiradora, concisa e orientada ao futuro para a empresa. Retorne apenas o texto da visão sugerida.",
        'values' => "Você é um especialista em Cultura Organizacional. Sugira 3 a 5 VALORES corporativos com breves descrições, baseados no contexto da empresa. Retorne em formato de lista HTML (<ul><li>...)",
        'methods' => "Com base na Visão e Valores, sugira 3 MÉTODOS (Planos de Ação) estratégicos. Para cada método, sugira um prazo realista. Retorne como JSON array: [{'desc': '...', 'deadline': '...'}]",
        'obstacles' => "Analise os Métodos propostos e identifique 3 OBSTÁCULOS (Riscos) e planos de mitigação. Retorne como JSON array: [{'desc': '...', 'mitigation': '...'}]",
        'measures' => "Defina KPIs (Métricas) para acompanhar os Métodos. Retorne como JSON array: [{'desc': '...', 'unit': '...', 'target': '...'}]"
    ];
    return $prompts[$type] ?? '';
}
?>
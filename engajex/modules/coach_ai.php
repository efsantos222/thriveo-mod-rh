<?php
// modules/coach_ai.php

function getCoachAIConfig($pdo, $companyId)
{
    // Reuse the existing AI config table
    $stmt = $pdo->prepare("SELECT api_key, model, temperature FROM ai_config WHERE company_id = ?");
    $stmt->execute([$companyId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function analyzeAssessment($pdo, $companyId, $type, $results, $userId)
{
    $config = getCoachAIConfig($pdo, $companyId);

    if (!$config || empty($config['api_key'])) {
        return "Erro: Chave de IA não configurada.";
    }

    $apiKey = $config['api_key'];
    $model = $config['model'] ?? 'gpt-4o';

    if ($type === 'DISC') {
        $prompt = "Analise o seguinte resultado de teste DISC de um colaborador: " . json_encode($results) . ". 
        Forneça um feedback estruturado sobre: 
        1. Pontos Fortes
        2. Áreas de Desenvolvimento
        3. Estilo de Comunicação
        4. Motivação. 
        Seja direto e profissional.";
    } else {
        $prompt = "Analise o seguinte resultado MBTI: " . json_encode($results) . ". 
        Forneça um resumo do tipo de personalidade, como essa pessoa trabalha em equipe e dicas de liderança para ela.";
    }

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => 'Você é um Master Coach especialista em análise comportamental (DISC/MBTI).'],
            ['role' => 'user', 'content' => $prompt]
        ],
        'temperature' => 0.7
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);

    $result = curl_exec($ch);
    if (curl_errno($ch))
        return "Erro de conexão: " . curl_error($ch);
    curl_close($ch);

    $response = json_decode($result, true);
    $analysis = $response['choices'][0]['message']['content'] ?? 'Erro na análise.';

    // Update assessment with analysis
    try {
        $stmt = $pdo->prepare("UPDATE coach_assessments SET ai_analysis = ? WHERE user_id = ? AND type = ? ORDER BY completed_at DESC LIMIT 1");
        $stmt->execute([$analysis, $userId, $type]);
    } catch (Exception $e) {
    }

    return $analysis;
}
?>
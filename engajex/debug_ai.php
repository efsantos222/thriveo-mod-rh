<?php
// debug_ai.php
require_once 'config.php';

echo "<h1>Debug Relatórios IA</h1>";

// 1. Check Company API Key
$companyId = $_SESSION['company_id'] ?? null;
if (!$companyId) {
    echo "<p style='color:red'>❌ ERRO: Sessão sem company_id. Faça login no sistema principal primeiro.</p>";
} else {
    $stmt = $pdo->prepare("SELECT name, openai_api_key FROM companies WHERE id = ?");
    $stmt->execute([$companyId]);
    $company = $stmt->fetch();
    
    echo "<h3>Empresa: " . htmlspecialchars($company['name']) . "</h3>";
    
    if (!$company['openai_api_key']) {
        echo "<p style='color:red'>❌ ERRO: Chave da OpenAI não encontrada para esta empresa.</p>";
    } else {
        echo "<p style='color:green'>✅ Chave da Empresa encontrada: " . substr($company['openai_api_key'], 0, 8) . "...</p>";
        $apiKey = $company['openai_api_key'];
    }
}

// 2. Check for Answers on Assignment 1 (from user screenshot)
$assignmentId = 1;
$stmt = $pdo->prepare("SELECT count(*) FROM ss_answers WHERE assignment_id = ?");
$stmt->execute([$assignmentId]);
$count = $stmt->fetchColumn();

if ($count == 0) {
    echo "<p style='color:orange'>⚠️ AVISO: Nenhuma resposta encontrada no banco para o teste ID: $assignmentId. A IA não pode gerar relatório sem respostas.</p>";
} else {
    echo "<p style='color:green'>✅ Encontradas $count respostas para o teste ID: $assignmentId.</p>";
}

// 3. Simple Test Call to OpenAI
if ($apiKey) {
    echo "<h3>Testando conexão com OpenAI...</h3>";
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'model' => 'gpt-3.5-turbo',
        'messages' => [['role' => 'user', 'content' => 'responda ok']],
        'max_tokens' => 5
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        echo "<p style='color:green'>✅ Conexão com OpenAI OK!</p>";
    } else {
        echo "<p style='color:red'>❌ Falha na conexão OpenAI. Código HTTP: $httpCode</p>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
    }
}
?>

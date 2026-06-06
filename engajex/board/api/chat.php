<?php
require_once '../config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$message = $input['message'] ?? '';
$context = $input['context'] ?? 'onboarding'; // onboarding, offboarding, knowledge
$empresa_id = $_SESSION['empresa_id'];
$user_id = $_SESSION['user_id'];

if (!$message) {
    echo json_encode(['error' => 'Empty message']);
    exit;
}

// 1. Get API Key
$stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'openai_api_key'");
$apiKey = $stmt->fetchColumn();

if (!$apiKey) {
    echo json_encode(['error' => 'API Key not configured']);
    exit;
}

// 2. Get Company Info for Context
$stmt = $pdo->prepare("SELECT * FROM company_intelelct WHERE empresa_id = ?");
$stmt->execute([$empresa_id]);
$companyInfo = $stmt->fetch();

$companyContextStr = "";
if ($companyInfo) {
    $companyContextStr = "Empresa Contexto:\n";
    $companyContextStr .= "Missão: " . $companyInfo['missao'] . "\n";
    $companyContextStr .= "Visão: " . $companyInfo['visao'] . "\n";
    $companyContextStr .= "Valores: " . $companyInfo['valores'] . "\n";
}

// 2a. Fetch Knowledge Base
$kbStmt = $pdo->prepare("SELECT title, content FROM company_knowledge_base WHERE empresa_id = ? AND (category = 'general' OR category = ?)");
$kbStmt->execute([$empresa_id, $context]);
$kbItems = $kbStmt->fetchAll();

$knowledgeStr = "";
if (!empty($kbItems)) {
    $knowledgeStr = "\n\nBase de Conhecimento e Regras da Empresa:\n";
    foreach ($kbItems as $item) {
        $knowledgeStr .= "- [" . $item['title'] . "]: " . $item['content'] . "\n";
    }
}

// 3. Define System Prompt based on Context
$systemPrompt = "";
switch ($context) {
    case 'offboarding':
        $systemPrompt = "Você é um assistente de RH empático realizando uma entrevista de desligamento. " .
            "Seu objetivo é entender os motivos da saída do colaborador de forma gentil e profissional. " .
            "Faça perguntas sobre o ambiente de trabalho, gestão e motivos da saída. " .
            "Não seja intrusivo. Use o contexto da empresa se relevante.\n" . $companyContextStr . $knowledgeStr;
        break;
    case 'knowledge':
        $systemPrompt = "Você é um especialista em Gestão do Conhecimento. " .
            "Seu objetivo é entrevistar um colaborador que está saindo para capturar processos críticos, contatos e conhecimentos tácitos. " .
            "Faça perguntas técnicas e sobre fluxo de trabalho. Organize as informações.\n" . $companyContextStr . $knowledgeStr;
        break;
    case 'onboarding':
    default:
        $systemPrompt = "Você é o 'Buddy' (Padrinho/Assistente) de Onboarding de um novo colaborador. " .
            "Ajude-o a se integrar nos primeiros 90 dias. Responda dúvidas sobre a cultura, missão e valores da empresa. " .
            "Utilize a Base de Conhecimento abaixo para responder perguntas sobre regras e processos. " .
            "Seja acolhedor, animado e útil.\n" . $companyContextStr . $knowledgeStr;
        break;
}

// 4. Manage Session (Simplified: One active session per context per user)
$stmt = $pdo->prepare("SELECT id FROM chat_sessions WHERE user_id = ? AND type = ? AND status = 'active' ORDER BY id DESC LIMIT 1");
$stmt->execute([$user_id, $context]);
$sessionId = $stmt->fetchColumn();

if (!$sessionId) {
    $stmt = $pdo->prepare("INSERT INTO chat_sessions (user_id, type) VALUES (?, ?)");
    $stmt->execute([$user_id, $context]);
    $sessionId = $pdo->lastInsertId();
}

// Save User Message
$stmt = $pdo->prepare("INSERT INTO chat_messages (session_id, sender, message) VALUES (?, 'user', ?)");
$stmt->execute([$sessionId, $message]);

// 5. Get recent history (last 10 messages) to maintain context
$stmt = $pdo->prepare("SELECT sender, message FROM chat_messages WHERE session_id = ? ORDER BY id ASC LIMIT 10");
$stmt->execute([$sessionId]);
$history = $stmt->fetchAll();

$messages = [['role' => 'system', 'content' => $systemPrompt]];
foreach ($history as $msg) {
    // Determine role: 'user' or 'ai' -> 'assistant'
    $role = ($msg['sender'] == 'user') ? 'user' : 'assistant';
    $messages[] = ['role' => $role, 'content' => $msg['message']];
}

// 6. Call OpenAI
$data = [
    'model' => 'gpt-3.5-turbo', // Or gpt-4
    'messages' => $messages,
    'temperature' => 0.7
];

$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
$aiMessage = $result['choices'][0]['message']['content'] ?? 'Desculpe, tive um problema ao conectar com a IA.';

// 7. Save AI Response
$stmt = $pdo->prepare("INSERT INTO chat_messages (session_id, sender, message) VALUES (?, 'ai', ?)");
$stmt->execute([$sessionId, $aiMessage]);

echo json_encode(['reply' => $aiMessage]);
?>
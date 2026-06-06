<?php
// ─── api.php ──────────────────────────────────────────────────────────────────
// Proxy seguro: recebe requisições do browser, adiciona a chave API no servidor
// e encaminha para a API da Anthropic. Nunca expõe a chave ao frontend.

require_once 'config.php';

// Apenas POST permitido
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

// Lê o body da requisição
$input = file_get_contents('php://input');
$data  = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'JSON inválido']);
    exit;
}

// Monta o payload para a API Anthropic
$payload = [
    'model'      => CLAUDE_MODEL,
    'max_tokens' => MAX_TOKENS,
    'system'     => $data['system']   ?? '',
    'messages'   => $data['messages'] ?? [],
];

// ⚠️ MCP servers são REMOVIDOS intencionalmente.
// Eles só funcionam dentro da plataforma Claude.ai (claude.ai/chat).
// Neste deploy PHP standalone, os agentes operam com seu conhecimento
// próprio + web search. Para integração M365/ClickUp real, use a versão
// hospedada no claude.ai com os conectores configurados.

// Tools (web_search — funciona normalmente via API)
if (!empty($data['tools'])) {
    $payload['tools'] = $data['tools'];
}

// Headers da requisição
$headers = [
    'Content-Type: application/json',
    'x-api-key: ' . ANTHROPIC_API_KEY,
    'anthropic-version: 2023-06-01',
];

// PDF support
if (!empty($data['beta_pdf'])) {
    $headers[] = 'anthropic-beta: pdfs-2024-08-06';
}

// Faz a chamada cURL para a Anthropic
$ch = curl_init('https://api.anthropic.com/v1/messages');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_HTTPHEADER     => $headers,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 120,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

header('Content-Type: application/json');

if ($curlErr) {
    http_response_code(500);
    echo json_encode(['error' => ['message' => 'Erro de conexão: ' . $curlErr]]);
    exit;
}

http_response_code($httpCode);
echo $response;

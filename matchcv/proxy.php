<?php
/**
 * talent.ai — Proxy seguro para API OpenAI
 * Pasta: /public_html/matchcv/proxy.php
 */

require_once 'config.php';

// CORS — aceita do seu domínio e subdomínios
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
$allowed = ['https://thriveo.com.br', 'https://www.thriveo.com.br', 'http://localhost', 'http://127.0.0.1'];

if (in_array($origin, $allowed) || empty($origin)) {
    header("Access-Control-Allow-Origin: " . (empty($origin) ? '*' : $origin));
} else {
    header("Access-Control-Allow-Origin: https://thriveo.com.br");
}

header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Só POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

// Valida body
$body = file_get_contents('php://input');
$data = json_decode($body, true);

if (!$data || !isset($data['messages'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Requisição inválida']);
    exit;
}

// Força modelo e limita tokens (OpenAI)
$data['model'] = 'gpt-4o'; // Usando o modelo mais recente gpt-4o
$data['max_tokens'] = min((int) ($data['max_tokens'] ?? 4096), 4096);

// Verifica se curl está disponível
if (!function_exists('curl_init')) {
    http_response_code(500);
    echo json_encode(['error' => 'curl não disponível no servidor']);
    exit;
}

// Chama a API OpenAI
$ch = curl_init(OPENAI_API_URL);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . OPENAI_API_KEY,
    ],
    CURLOPT_TIMEOUT => 120,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    http_response_code(502);
    echo json_encode(['error' => 'Erro curl: ' . $curlError]);
    exit;
}

http_response_code($httpCode);
echo $response;


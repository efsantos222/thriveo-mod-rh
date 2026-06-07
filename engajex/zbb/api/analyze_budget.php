require_once '../../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
echo json_encode(['success' => false, 'message' => 'Não autorizado']);
exit;
}

if (!in_array($_SESSION['role'], ['responsible', 'company_admin', 'manager', 'admin'])) {
echo json_encode(['success' => false, 'message' => 'Acesso Negado (Role)']);
exit;
}

$action = $_REQUEST['action'] ?? '';

if ($action === 'get_ai_analysis') {
// 1. Fetch AI Analysis
$budgetId = $_POST['budget_id'] ?? 0;
$context = $_POST['context'] ?? ''; // Optional user context

// Fetch budget details
$stmt = $pdo->prepare("
SELECT b.*, cc.name as cost_center, c.name as category, c.type as nature
FROM budgets b
JOIN cost_centers cc ON b.cost_center_id = cc.id
JOIN categories c ON b.category_id = c.id
WHERE b.id = ?
");
$stmt->execute([$budgetId]);
$budget = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$budget) {
echo json_encode(['success' => false, 'message' => 'Orçamento não encontrado']);
exit;
}

// Prepare Prompt for OpenAI
$ref = number_format($budget['amount_budgeted'], 2, ',', '.');
$current = number_format($budget['amount_realized'], 2, ',', '.');
$diff = $budget['amount_realized'] - $budget['amount_budgeted'];
$diffPercent = ($budget['amount_budgeted'] > 0) ? ($diff / $budget['amount_budgeted']) * 100 : 0;
$diffStr = number_format($diffPercent, 1, ',', '.') . '%';

$prompt = "Você é um assistente financeiro especialista em Orçamento Base Zero (ZBB).
Analise a seguinte despesa e sugira uma justificativa técnica e estratégica para defendê-la ou questioná-la.

Dados:
- Centro de Custo: {$budget['cost_center']}
- Categoria: {$budget['category']}
- Natureza: {$budget['nature']}
- Valor Referência (Ano Anterior/Orcado): R$ {$ref}
- Valor Atual (Realizado/Solicitado): R$ {$current}
- Variação: {$diffStr}

Contexto adicional do usuário: '$context'

Gere um texto curto, direto e profissional (máximo 3 parágrafos) focando em eficiência e necessidade.";

// Call OpenAI
// Get API Key from DB
$stmtKey = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'openai_api_key'");
$apiKey = $stmtKey->fetchColumn();

if (!$apiKey) {
echo json_encode(['success' => false, 'message' => 'Chave de API não configurada']);
exit;
}

$data = [
'model' => 'gpt-4o', // or gpt-3.5-turbo if preferred for speed/cost
'messages' => [
['role' => 'system', 'content' => 'Você é um analista financeiro sênior.'],
['role' => 'user', 'content' => $prompt]
],
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
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
echo json_encode(['success' => false, 'message' => 'Erro na API da IA', 'details' => $response]);
exit;
}

$result = json_decode($response, true);
$aiText = $result['choices'][0]['message']['content'] ?? 'Sem resposta da IA.';

echo json_encode(['success' => true, 'analysis' => $aiText]);

} elseif ($action === 'update_status') {
// 2. Update Status and Justification
$budgetId = $_POST['budget_id'] ?? 0;
$status = $_POST['status'] ?? 'pending';
$justification = $_POST['justification'] ?? '';

$stmt = $pdo->prepare("UPDATE budgets SET status = ?, justification = ? WHERE id = ?");
if ($stmt->execute([$status, $justification, $budgetId])) {
echo json_encode(['success' => true, 'message' => 'Atualizado com sucesso']);
} else {
echo json_encode(['success' => false, 'message' => 'Erro ao atualizar']);
}

} elseif ($action === 'get_details') {
// 3. Get Details for Modal
$budgetId = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("
SELECT b.*, cc.name as cost_center, c.name as category, c.type as nature
FROM budgets b
JOIN cost_centers cc ON b.cost_center_id = cc.id
JOIN categories c ON b.category_id = c.id
WHERE b.id = ?
");
$stmt->execute([$budgetId]);
$budget = $stmt->fetch(PDO::FETCH_ASSOC);

if ($budget) {
echo json_encode(['success' => true, 'data' => $budget]);
} else {
echo json_encode(['success' => false, 'message' => 'Não encontrado']);
}
} else {
echo json_encode(['success' => false, 'message' => 'Ação inválida']);
}
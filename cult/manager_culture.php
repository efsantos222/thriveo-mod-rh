require_once 'config/db.php';
requireAuth();
require_once 'includes/header.php';

if ($_SESSION['role'] !== 'manager') {
header("Location: index.php");
exit;
}

$companyId = $_SESSION['company_id'];
$msg = '';
$loading = false;

// Function to call OpenAI
function generateCultureReport($pdo, $companyId)
{
// 1. Get API Key
$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'openai_key'");
$stmt->execute();
$apiKey = $stmt->fetchColumn();

if (!$apiKey)
return "Erro: Chave API não configurada pelo Superadmin.";

// 2. Get Identity
$stmt = $pdo->prepare("SELECT * FROM company_identity WHERE company_id = ?");
$stmt->execute([$companyId]);
$identity = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$identity)
return "Erro: Identidade Organizacional não preenchida.";

$identityStr = "Propósito: {$identity['purpose']}\nMissão: {$identity['mission']}\nVisão:
{$identity['vision']}\nPrincípios: {$identity['principles']}\nValores: {$identity['values_text']}";

// 3. Get Survey Data
// Fetch all questions and responses
$stmt = $pdo->prepare("
SELECT q.id as q_id, q.question_text, q.question_type, r.answers
FROM responses r
JOIN surveys s ON r.survey_id = s.id
JOIN questions q ON q.survey_id = s.id
WHERE s.company_id = ?
");
$stmt->execute([$companyId]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($rows))
return "Erro: Nenhuma resposta de pesquisa encontrada para analisar.";

// Process data for prompt
$surveyData = [];
foreach ($rows as $row) {
$answers = json_decode($row['answers'], true);
if (isset($answers[$row['q_id']])) {
$surveyData[$row['question_text']][] = $answers[$row['q_id']];
}
}

$surveySummary = "";
foreach ($surveyData as $q => $ansList) {
$surveySummary .= "Pergunta: $q\nRespostas: " . implode(", ", array_slice($ansList, 0, 50)) . "\n---\n";
}

// 4. API Call
$prompt = "Você é um especialista em Cultura Organizacional e RH. Analise os seguintes dados:\n\n" .
"IDENTIDADE DA EMPRESA (O que ela diz ser):\n$identityStr\n\n" .
"RESULTADOS DAS PESQUISAS (O que os colaboradores dizem):\n$surveySummary\n\n" .
"TAREFA:\n" .
"1. Descreva a Cultura Organizacional Percebida com base nas respostas.\n" .
"2. Identifique alinhamentos e desalinhamentos (Gaps) entre a Identidade declarada e a percepção real.\n" .
"3. Sugira 3 direcionamentos práticos e correções para fortalecer a cultura desejada.\n" .
"4. Formate a resposta em HTML limpo (use <h3>, <p>,
    <ul>, <li>), sem markdown ```html tags.";

            $data = [
            'model' => 'gpt-4o', // Or gpt-3.5-turbo if prefer cheaper
            'messages' => [
            ['role' => 'system', 'content' => 'Você é um consultor organizacional sênior.'],
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

            $result = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error)
            return "Erro na conexão API: $error";

            $response = json_decode($result, true);
            if (isset($response['error']))
            return "Erro da API: " . $response['error']['message'];

            return $response['choices'][0]['message']['content'] ?? "Erro ao gerar resposta.";
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $report = generateCultureReport($pdo, $companyId);

            if (strpos($report, 'Erro:') === 0) {
            $msg = $report;
            } else {
            // Save report
            $pdo->prepare("INSERT INTO reports (company_id, content) VALUES (?, ?)")->execute([$companyId, $report]);
            $msg = "Relatório gerado com sucesso!";
            }
            }

            // Get latest report
            $latestReport = $pdo->prepare("SELECT * FROM reports WHERE company_id = ? ORDER BY created_at DESC LIMIT
            1");
            $latestReport->execute([$companyId]);
            $reportParams = $latestReport->fetch();
            ?>

            <div class="container">
                <div class="flex-between mb-4">
                    <h2>Relatório de Cultura Organizacional</h2>
                    <form method="POST"
                        onsubmit="this.querySelector('button').innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Gerando...'; this.querySelector('button').disabled=true;">
                        <button type="submit" class="btn btn-primary"
                            style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border:none;">
                            <i class="fas fa-magic"></i> Gerar Nova Análise com IA
                        </button>
                    </form>
                </div>

                <?php if ($msg): ?>
                    <div
                        style="background: rgba(<?= strpos($msg, 'Erro') !== false ? '239, 68, 68' : '16, 185, 129' ?>, 0.2); color: white; padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem;">
                        <?= $msg ?>
                    </div>
                <?php endif; ?>

                <?php if ($reportParams): ?>
                    <div class="card animate-fade">
                        <div
                            style="border-bottom: 1px solid var(--border); padding-bottom: 1rem; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
                            <span class="badg badge-admin" style="background: rgba(255, 255, 255, 0.1);">Gerado em:
                                <?= date('d/m/Y H:i', strtotime($reportParams['created_at'])) ?></span>
                        </div>

                        <div class="report-content" style="line-height: 1.8;">
                            <?= $reportParams['content'] ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center"
                        style="padding: 4rem; color: var(--text-muted); background: rgba(255,255,255,0.02); border-radius: 1rem;">
                        <i class="fas fa-chart-pie" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <p>Nenhum relatório gerado ainda.</p>
                        <p>Certifique-se de ter preenchido a Identidade Organizacional e coletado respostas nas pesquisas.
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <style>
                .report-content h3 {
                    color: var(--primary);
                    margin-top: 2rem;
                    font-size: 1.5rem;
                }

                .report-content ul {
                    padding-left: 1.5rem;
                    margin-bottom: 1.5rem;
                }

                .report-content li {
                    margin-bottom: 0.5rem;
                }

                .report-content p {
                    margin-bottom: 1rem;
                    text-align: justify;
                }
            </style>

            <?php require_once 'includes/footer.php'; ?>
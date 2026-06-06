<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../config.php';
require_once 'coach_ai.php'; // Reuse config getter

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$companyId = $_SESSION['company_id'] ?? 1;
$role = $_SESSION['role'];
if (!in_array($role, ['admin', 'manager', 'responsible', 'company_admin'])) {
    die("Acesso negado.");
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'generate_report') {
    $iStmt = $pdo->prepare("SELECT * FROM culture_identity WHERE company_id = ?");
    $iStmt->execute([$companyId]);
    $identity = $iStmt->fetch();

    if (!$identity) {
        $message = "Erro: Identidade Organizacional não definida. Preencha primeiro.";
    } else {
        $identityStr = "Propósito: {$identity['purpose']}\nMissão: {$identity['mission']}\nVisão: {$identity['vision']}\nValores: {$identity['values_text']}\nPrincípios: {$identity['principles']}";

        $rStmt = $pdo->prepare("
            SELECT s.title, s.questions_json, r.answers_json 
            FROM culture_responses r 
            JOIN culture_surveys s ON r.survey_id = s.id 
            WHERE s.company_id = ? 
            ORDER BY r.submitted_at DESC LIMIT 50
        ");
        $rStmt->execute([$companyId]);
        $rows = $rStmt->fetchAll();

        if (empty($rows)) {
            $message = "Erro: Nenhuma resposta de pesquisa encontrada.";
        } else {
            $surveySummary = "";
            foreach ($rows as $r) {
                $qs = json_decode($r['questions_json'], true);
                $ans = json_decode($r['answers_json'], true);
                $surveySummary .= "Pesquisa: {$r['title']}\n";
                foreach ($ans as $idx => $a) {
                    $qText = $qs[$idx] ?? "Questão $idx";
                    $surveySummary .= "- P: $qText | R: $a\n";
                }
                $surveySummary .= "---\n";
            }

            $prompt = "Analise a Cultura Organizacional.\n\nIDENTIDADE DECLARADA:\n$identityStr\n\nPERCEPÇÃO DOS COLABORADORES (Respostas):\n$surveySummary\n\nTAREFA:\n1. Descreva a Cultura Real.\n2. Identifique Gaps entre o declarado e o percebido.\n3. Sugira 3 ações de correção.\nRetorne em HTML formatado (h3, p, ul, li).";

            $config = getCoachAIConfig($pdo, $companyId);
            if ($config && !empty($config['api_key'])) {
                $ch = curl_init('https://api.openai.com/v1/chat/completions');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                    'model' => 'gpt-4o',
                    'messages' => [
                        ['role' => 'system', 'content' => 'Consultor Expert em Cultura.'],
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'temperature' => 0.7
                ]));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $config['api_key']
                ]);
                $result = curl_exec($ch);
                curl_close($ch);
                $resp = json_decode($result, true);
                $reportContent = $resp['choices'][0]['message']['content'] ?? 'Erro na IA.';

                $pdo->prepare("INSERT INTO culture_reports (company_id, content) VALUES (?, ?)")
                    ->execute([$companyId, $reportContent]);
                $message = "Relatório atualizado!";
            } else {
                $message = "Erro: API Key não configurada.";
            }
        }
    }
}

$lastReport = $pdo->prepare("SELECT * FROM culture_reports WHERE company_id = ? ORDER BY created_at DESC LIMIT 1");
$lastReport->execute([$companyId]);
$lr = $lastReport->fetch();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Análise de Cultura AI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .report-box {
            background: rgba(255, 255, 255, 0.05);
            padding: 2rem;
            border-radius: 1rem;
            line-height: 1.6;
        }

        .report-box h3 {
            color: var(--primary-color);
            margin-top: 1.5rem;
        }

        .report-box ul {
            margin-left: 1.5rem;
        }

        .report-box li {
            margin-bottom: 0.5rem;
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h1>Análise de Cultura (AI)</h1>
                <a href="board_culture.php" class="btn btn-outline">
                    < Voltar</a>
            </div>

            <?php if ($message): ?>
                <div style="margin:1rem 0; color:#34d399;"><?php echo $message; ?></div><?php endif; ?>

            <div style="margin-bottom:2rem;">
                <p style="color:var(--text-muted); margin-bottom:1rem;">A IA irá comparar a Identidade declarada com as
                    respostas das Pesquisas Pulse mais recentes.</p>
                <form method="POST">
                    <input type="hidden" name="action" value="generate_report">
                    <button class="btn btn-primary"
                        style="background: linear-gradient(135deg, #a855f7, #ec4899); border:none;">✨ Gerar Relatório de
                        Cultura</button>
                </form>
            </div>

            <?php if ($lr): ?>
                <div class="report-box">
                    <div style="font-size:0.8rem; color:var(--text-muted); margin-bottom:1rem;">Gerado em:
                        <?php echo date('d/m/Y H:i', strtotime($lr['created_at'])); ?></div>
                    <?php echo $lr['content']; ?>
                </div>
            <?php else: ?>
                <p style="color:var(--text-muted);">Nenhum relatório gerado ainda.</p>
            <?php endif; ?>

        </main>
    </div>
</body>

</html>
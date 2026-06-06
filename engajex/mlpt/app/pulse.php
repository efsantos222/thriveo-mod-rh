<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkAuth('responsible');

$companyId = $_SESSION['company_id'];
$message = '';

// Create Survey
if (isset($_POST['create_survey'])) {
    $stmt = $pdo->prepare("INSERT INTO pulse_surveys (company_id, title) VALUES (?, 'Pesquisa de Clima - " . date('M/Y') . "')");
    $stmt->execute([$companyId]);
    $message = "Nova pesquisa criada!";
}

// Analyze Sentiment
if (isset($_POST['analyze_sentiment'])) {
    $surveyId = $_POST['survey_id'];

    // Get comments
    $stmt = $pdo->prepare("SELECT comment FROM pulse_responses WHERE survey_id = ? AND comment != ''");
    $stmt->execute([$surveyId]);
    $comments = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($comments) > 0) {
        // Fetch API Key
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'openai_api_key'");
        $stmt->execute();
        $apiKey = $stmt->fetchColumn();

        if ($apiKey) {
            $text = implode("\n", $comments);
            $prompt = "Analise os seguintes comentários de funcionários. Classifique a contagem de Positivos, Neutros e Negativos. Forneça um resumo executivo das tendências. Formato JSON: {pos: int, neu: int, neg: int, summary: 'text'}. Comentários:\n" . substr($text, 0, 3000);

            $data = [
                'model' => 'gpt-3.5-turbo',
                'messages' => [['role' => 'user', 'content' => $prompt]]
            ];

            $ch = curl_init('https://api.openai.com/v1/chat/completions');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey]);

            $res = curl_exec($ch);
            curl_close($ch);
            $json = json_decode($res, true);

            if (isset($json['choices'][0]['message']['content'])) {
                $content = $json['choices'][0]['message']['content'];
                // Try to parse JSON from content if it's wrapped in text
                preg_match('/\{.*\}/s', $content, $matches);
                if (isset($matches[0])) {
                    $result = json_decode($matches[0], true);

                    $stmt = $pdo->prepare("INSERT INTO sentiment_analysis (survey_id, positive_count, neutral_count, negative_count, summary) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$surveyId, $result['pos'] ?? 0, $result['neu'] ?? 0, $result['neg'] ?? 0, $result['summary'] ?? $content]);
                    $message = "Análise concluída!";
                }
            }
        } else {
            $message = "Erro: API Key não configurada.";
        }
    } else {
        $message = "Sem comentários para analisar.";
    }
}

// List Surveys
$surveys = $pdo->prepare("
    SELECT s.*, 
    (SELECT COUNT(*) FROM pulse_responses r WHERE r.survey_id = s.id) as response_count,
    (SELECT AVG(rating) FROM pulse_responses r WHERE r.survey_id = s.id) as avg_rating,
    sa.summary, sa.positive_count, sa.neutral_count, sa.negative_count
    FROM pulse_surveys s
    LEFT JOIN sentiment_analysis sa ON sa.survey_id = s.id
    WHERE s.company_id = ?
    ORDER BY s.created_at DESC
");
$surveys->execute([$companyId]);
$list = $surveys->fetchAll();

$baseUrl = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . dirname(dirname($_SERVER['REQUEST_URI'])) . "/pulse_vote.php?s=";
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Mini-Pesquisas - MLPT</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .app-layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: #1e293b;
            padding: 20px;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }

        .content {
            flex: 1;
            padding: 40px;
        }

        .sidebar .logo {
            margin-bottom: 40px;
            text-align: center;
        }

        .menu-item {
            display: block;
            padding: 12px 16px;
            color: var(--text-dim);
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 5px;
        }

        .menu-item:hover,
        .menu-item.active {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
        }

        .menu-item i {
            margin-right: 10px;
            width: 20px;
        }

        .survey-card {
            background: var(--card-bg);
            padding: 24px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            margin-bottom: 20px;
        }

        .survey-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .stat-badge {
            background: rgba(0, 0, 0, 0.3);
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 0.9rem;
            margin-right: 10px;
        }

        .sentiment-bar {
            height: 10px;
            border-radius: 5px;
            overflow: hidden;
            display: flex;
            margin-bottom: 10px;
        }

        .sb-pos {
            background: #10b981;
        }

        .sb-neu {
            background: #94a3b8;
        }

        .sb-neg {
            background: #ef4444;
        }

        .copy-link {
            background: rgba(0, 0, 0, 0.2);
            padding: 8px;
            border-radius: 6px;
            font-family: monospace;
            cursor: pointer;
            display: inline-block;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <div class="sidebar">
            <div class="logo">MLPT System</div>
            <a href="index.php" class="menu-item"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
            <a href="pulse.php" class="menu-item active"><i class="fa-solid fa-heart-pulse"></i> Pulso & Clima</a>
            <a href="maturity.php" class="menu-item"><i class="fa-solid fa-sliders"></i> Avaliação Maturidade</a>
            <a href="documents.php" class="menu-item"><i class="fa-solid fa-file-contract"></i> Análise Documental</a>
            <a href="trust_index.php" class="menu-item"><i class="fa-solid fa-flask"></i> Simulador Trust Index</a>
            <a href="action_plan.php" class="menu-item"><i class="fa-solid fa-list-check"></i> Planos de Ação</a>
            <a href="../logout.php" class="menu-item"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
        </div>
        <div class="content">
            <div class="header-flex" style="display:flex; justify-content:space-between; margin-bottom: 30px;">
                <h2>Pulso Mensal</h2>
                <form method="POST">
                    <button type="submit" name="create_survey" class="btn btn-primary"><i class="fa-solid fa-plus"></i>
                        Novo Pulso</button>
                </form>
            </div>

            <?php if ($message): ?>
                <div style="color: #10b981; margin-bottom: 20px;"><?php echo $message; ?></div> <?php endif; ?>

            <?php foreach ($list as $s): ?>
                <div class="survey-card">
                    <div class="survey-header">
                        <div>
                            <h3><?php echo htmlspecialchars($s['title']); ?></h3>
                            <small class="text-dim">Criado em
                                <?php echo date('d/m/Y', strtotime($s['created_at'])); ?></small>
                        </div>
                        <div>
                            <span class="stat-badge"><i class="fa-solid fa-users"></i> <?php echo $s['response_count']; ?>
                                respostas</span>
                            <span class="stat-badge"><i class="fa-solid fa-star"></i>
                                <?php echo number_format($s['avg_rating'], 1); ?>/10</span>
                        </div>
                    </div>

                    <div class="share-section">
                        <small>Link para envio:</small><br>
                        <div class="copy-link"><?php echo $baseUrl . $s['id']; ?></div>
                    </div>

                    <?php if ($s['response_count'] > 0): ?>
                        <div style="margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 20px;">
                            <h4>Análise de Sentimento (IA)</h4>
                            <?php if ($s['summary']): ?>
                                <?php
                                $total = $s['positive_count'] + $s['neutral_count'] + $s['negative_count'];
                                $p_pos = $total ? ($s['positive_count'] / $total) * 100 : 0;
                                $p_neu = $total ? ($s['neutral_count'] / $total) * 100 : 0;
                                $p_neg = $total ? ($s['negative_count'] / $total) * 100 : 0;
                                ?>
                                <div class="sentiment-bar" style="margin-top: 10px;">
                                    <div class="sb-pos" style="width: <?php echo $p_pos; ?>%"></div>
                                    <div class="sb-neu" style="width: <?php echo $p_neu; ?>%"></div>
                                    <div class="sb-neg" style="width: <?php echo $p_neg; ?>%"></div>
                                </div>
                                <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 10px;">
                                    <span>Positivos: <?php echo $s['positive_count']; ?></span>
                                    <span>Neutros: <?php echo $s['neutral_count']; ?></span>
                                    <span>Negativos: <?php echo $s['negative_count']; ?></span>
                                </div>
                                <p style="color: var(--text-dim); font-size: 0.9rem;"><?php echo $s['summary']; ?></p>
                            <?php else: ?>
                                <form method="POST">
                                    <input type="hidden" name="survey_id" value="<?php echo $s['id']; ?>">
                                    <button type="submit" name="analyze_sentiment" class="btn btn-secondary"
                                        style="font-size: 0.8rem; padding: 5px 10px;">Processar IA</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>

</html>
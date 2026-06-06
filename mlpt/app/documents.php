<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkAuth('responsible');

$companyId = $_SESSION['company_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content']; // Text content

    if ($title && $content) {
        // Fetch API Key
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'openai_api_key'");
        $stmt->execute();
        $apiKey = $stmt->fetchColumn();

        if (!$apiKey) {
            $message = "Erro: Chave de API não configurada pelo administrador.";
        } else {
            // Call OpenAI API
            $prompt = "Analise o seguinte documento de RH com base nos critérios do 'Melhor Lugar Para Trabalhar' (GPTW). Identifique gaps e sugira melhorias. Documento: " . substr($content, 0, 3000);

            $data = [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => 'Você é um especialista em RH e cultura organizacional.'],
                    ['role' => 'user', 'content' => $prompt]
                ]
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
            $analysis = $result['choices'][0]['message']['content'] ?? 'Erro na análise da IA.';

            // Save
            $stmt = $pdo->prepare("INSERT INTO documents (company_id, title, file_path, analysis_result) VALUES (?, ?, 'text_input', ?)");
            $stmt->execute([$companyId, $title, $analysis]);

            $message = "Documento analisado com sucesso!";
        }
    }
}

$docs = $pdo->prepare("SELECT * FROM documents WHERE company_id = ? ORDER BY uploaded_at DESC");
$docs->execute([$companyId]);
$documents = $docs->fetchAll();

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Análise Documental - MLPT</title>
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

        .form-card {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 40px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .form-control {
            width: 100%;
            padding: 10px;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: white;
            margin-bottom: 15px;
        }

        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }

        .doc-card {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .analysis-box {
            background: rgba(0, 0, 0, 0.3);
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            color: var(--text-dim);
            white-space: pre-wrap;
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <div class="sidebar">
            <div class="logo">MLPT System</div>
            <a href="index.php" class="menu-item"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
            <a href="maturity.php" class="menu-item"><i class="fa-solid fa-sliders"></i> Avaliação Maturidade</a>
            <a href="documents.php" class="menu-item active"><i class="fa-solid fa-file-contract"></i> Análise
                Documental</a>
            <a href="trust_index.php" class="menu-item"><i class="fa-solid fa-flask"></i> Simulador Trust Index</a>
            <a href="action_plan.php" class="menu-item"><i class="fa-solid fa-list-check"></i> Planos de Ação</a>
            <a href="../logout.php" class="menu-item"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
        </div>
        <div class="content">
            <h2>Análise Documental com IA</h2>

            <?php if ($message): ?>
                <div style="color: #10b981; margin-bottom: 20px;"><?php echo $message; ?></div> <?php endif; ?>

            <div class="form-card">
                <h3>Nova Análise</h3>
                <form method="POST">
                    <input type="text" name="title" class="form-control"
                        placeholder="Título do Documento (ex: Código de Conduta)" required>
                    <textarea name="content" class="form-control" placeholder="Cole o texto do documento aqui..."
                        required></textarea>
                    <button type="submit" class="btn btn-primary">Analisar com IA</button>
                    <p style="font-size: 0.8rem; color: var(--text-dim); margin-top: 10px;">*Requer configuração de
                        chave API pelo administrador.</p>
                </form>
            </div>

            <div class="history">
                <h3 style="margin-bottom: 20px;">Histórico de Análises</h3>
                <?php foreach ($documents as $doc): ?>
                    <div class="doc-card">
                        <h4><?php echo htmlspecialchars($doc['title']); ?></h4>
                        <small><?php echo date('d/m/Y H:i', strtotime($doc['uploaded_at'])); ?></small>
                        <div class="analysis-box">
                            <strong>Análise IA:</strong><br>
                            <?php echo nl2br(htmlspecialchars($doc['analysis_result'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>

</html>
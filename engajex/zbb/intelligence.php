<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$page = 'intelligence';
$result = null;
$error = null;
$loading = false;

// Function to call OpenAI API
function getMarketAnalysis($sector, $country, $apiKey)
{
    $url = 'https://api.openai.com/v1/chat/completions';

    $prompt = "Atue como um especialista em Inteligência de Mercado sênior. Faça uma análise competitiva detalhada para o setor de '{$sector}' no país '{$country}'. 
    Estruture a resposta nos seguintes tópicos em formato HTML (use <h3> para títulos e <ul>/<li> para listas):
    1. Tamanho do Mercado e Tendências Atuais
    2. Principais Concorrentes e Market Share estimado
    3. Análise SWOT (Forças, Fraquezas, Oportunidades, Ameaças)
    4. Sugestões Estratégicas para novos entrantes";

    $data = [
        'model' => "gpt-4o", // Or gpt-4o-mini if preferred for cost
        'messages' => [
            ['role' => 'system', 'content' => 'Você é um assistente corporativo especializado em análise estratégica de negócios.'],
            ['role' => 'user', 'content' => $prompt]
        ],
        'temperature' => 0.5
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        return ['error' => 'Erro na conexão com API: ' . curl_error($ch)];
    }

    curl_close($ch);

    $responseData = json_decode($response, true);

    if (isset($responseData['error'])) {
        return ['error' => 'Erro da API OpenAI: ' . $responseData['error']['message']];
    }

    return ['content' => $responseData['choices'][0]['message']['content'] ?? 'Sem resposta da IA.'];
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sector = filter_input(INPUT_POST, 'sector', FILTER_SANITIZE_SPECIAL_CHARS);
    $country = filter_input(INPUT_POST, 'country', FILTER_SANITIZE_SPECIAL_CHARS);

    if ($sector && $country) {
        // Fetch API Key
        $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'openai_api_key'");
        $stmt->execute();
        $apiKey = $stmt->fetchColumn();

        if ($apiKey) {
            $analysis = getMarketAnalysis($sector, $country, $apiKey);
            if (isset($analysis['error'])) {
                $error = $analysis['error'];
            } else {
                $result = $analysis['content'];

                // Optional: Save to history (not implemented fully in schema yet, but good practice concept)
            }
        } else {
            $error = "Chave da API OpenAI não configurada. Contate o administrador.";
        }
    } else {
        $error = "Por favor, preencha todos os campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Inteligência Competitiva - Thriveo ZBB</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #e2e8f0;
            border-top: 5px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
    <script>
        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        }
    </script>
</head>

<body style="background-color: var(--background-color);">

    <!-- Loading Screen -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="spinner mb-4"></div>
        <h3 style="color: var(--primary-color);">Analisando o Mercado...</h3>
        <p class="text-secondary">Isso pode levar alguns segundos. Consultando GPT-4o.</p>
    </div>

    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        <main class="main-content">
            <h1 class="mb-4">Inteligência Competitiva de Mercado</h1>

            <div class="card mb-4">
                <h3 class="mb-4">Nova Análise</h3>
                <form method="POST" action="" onsubmit="showLoading()"
                    style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 1rem; align-items: end;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Setor / Indústria</label>
                        <input type="text" name="sector" class="form-control"
                            placeholder="Ex: Varejo de Moda, Fintechs, Agronegócio..." required>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">País / Região</label>
                        <input type="text" name="country" class="form-control" value="Brasil" required>
                    </div>

                    <button type="submit" class="btn btn-primary" style="height: 48px;">
                        Gerar Análise 🧠
                    </button>
                </form>
            </div>

            <?php if ($error): ?>
                <div
                    style="background-color: #fee2e2; color: #b91c1c; padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem;">
                    <strong>Erro:</strong> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($result): ?>
                <div class="card">
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 1rem;">
                        <h2 style="font-size: 1.5rem; margin: 0;">Relatório de Inteligência</h2>
                        <span class="lgpd-badge" style="margin: 0;">Gerado via GPT-4o</span>
                    </div>

                    <div style="line-height: 1.8; color: var(--text-primary);">
                        <?= $result // Content is safe HTML generated by AI, but in prod use a purifier ?>
                    </div>

                    <div style="margin-top: 2rem; text-align: right;">
                        <button class="btn btn-secondary" onclick="window.print()">🖨️ Imprimir / Salvar PDF</button>
                    </div>
                </div>
            <?php else: ?>
                <?php if (!$error): ?>
                    <div class="card text-center" style="padding: 4rem 2rem;">
                        <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;">📊</div>
                        <h3 style="color: var(--text-secondary);">Nenhuma análise gerada ainda</h3>
                        <p style="color: var(--text-secondary);">Preencha o formulário acima para receber insights estratégicos
                            sobre qualquer mercado.</p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

        </main>
    </div>
</body>

</html>
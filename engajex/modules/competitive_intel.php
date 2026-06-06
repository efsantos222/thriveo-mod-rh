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
// Only 'responsible' (and admin)
if (!in_array($role, ['admin', 'responsible', 'company_admin'])) {
    die("<div style='padding:2rem; color:white;'>Acesso restrito a Responsáveis e Admins.</div>");
}

$message = '';

// Get Sector
$cStmt = $pdo->prepare("SELECT sector FROM companies WHERE id = ?");
$cStmt->execute([$companyId]);
$sector = $cStmt->fetchColumn();

// --- SAVE SECTOR ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'save_sector') {
    $newSector = $_POST['sector'];
    $pdo->prepare("UPDATE companies SET sector = ? WHERE id = ?")->execute([$newSector, $companyId]);
    $sector = $newSector;
    $message = "Setor atualizado.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'save_apikey') {
    $key = $_POST['api_key'];
    // Check if exists
    $check = $pdo->prepare("SELECT id FROM ai_config WHERE company_id = ?");
    $check->execute([$companyId]);
    if ($check->fetch()) {
        $pdo->prepare("UPDATE ai_config SET api_key = ? WHERE company_id = ?")->execute([$key, $companyId]);
    } else {
        $pdo->prepare("INSERT INTO ai_config (company_id, api_key) VALUES (?, ?)")->execute([$companyId, $key]);
    }
    $message = "Chave API salva!";
}

// --- GENERATE ANALYSIS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'generate_analysis') {
    $category = $_POST['category'];
    $topic = $_POST['topic'];
    $context = $_POST['context'];

    if (!$sector) {
        $message = "Erro: Defina o Setor de Atuação primeiro.";
    } else {

        // Define Specific Question based on Category
        $specificQ = "";
        switch ($category) {
            case 'technology':
                $specificQ = "O que está acontecendo de novidades em IA no mundo?";
                break;
            case 'legislation':
                $specificQ = "Quais leis e projetos de lei foram aprovados recentemente e/ou estão sendo desenvolvidos no Brasil para a área de Tecnologia da Informação?";
                break;
            case 'market':
                $specificQ = "Como está o mercado no brasil e no mundo para: desenvolvimento de sistemas, alocação de profissionais de TI, venda de licenças, projetos de TI, consultoria de TI e Inteligência artificial?";
                break;
            case 'competitors':
                $specificQ = "Que ações as empresas (cite as empresas) de TI no brasil e no mundo estão adotando para: desenvolvimento de sistemas, alocação de profissionais de TI, venda de licenças, projetos de TI, consultoria de TI e Inteligência artificial?";
                break;
            case 'patents':
                $specificQ = "Patentes publicadas/registradas por empresas brasileiras e internacionais no Google Patents nas áreas de Inteligência Artificial.";
                break;
            case 'articles': // articles
                $specificQ = "Artigos científicos publicadas/registradas por empresas brasileiras no Google Scholar nas áreas de Inteligência Artificial e Segurança Cibernética.";
                break;
            default:
                $specificQ = "Analise o tópico '$topic' com foco em '$sector'.";
        }

        $prompt = $specificQ;
        if (!empty($topic) && stripos($specificQ, 'tópico') !== false) {
            // Only if specificQ is generic, otherwise just append as context
        } else {
            $prompt .= "\n\n(Considere também o tópico: '$topic')";
        }

        if (!empty($context)) {
            $prompt .= "\nContexto: $context";
        }

        $prompt .= "\n\nResponda em Português do Brasil. Formate com HTML simples (bold, bullet points) para leitura.";

        $config = getCoachAIConfig($pdo, $companyId);
        if ($config && !empty($config['api_key'])) {
            $ch = curl_init('https://api.openai.com/v1/chat/completions');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful assistant. Output in HTML.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.5
            ]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $config['api_key']
            ]);
            $result = curl_exec($ch);

            if (curl_errno($ch)) {
                $message = "Erro Curl: " . curl_error($ch);
            } else {
                $resp = json_decode($result, true);
                if (isset($resp['choices'][0]['message']['content'])) {
                    $content = $resp['choices'][0]['message']['content'];
                    $level = 'info';

                    // Reconnect DB if timed out (MySQL server has gone away)
                    try {
                        $pdo->query("SELECT 1");
                    } catch (PDOException $e) {
                        try {
                            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
                            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        } catch (PDOException $ex) {
                            $message = "Erro fatal DB: " . $ex->getMessage();
                        }
                    }

                    if (isset($pdo)) {
                        $pdo->prepare("INSERT INTO market_intelligence (company_id, category, topic, content, action_level) VALUES (?, ?, ?, ?, ?)")
                            ->execute([$companyId, $category, $topic, $content, $level]);
                        $message = "Análise gerada com sucesso!";
                    }
                } else {
                    $message = "Erro na resposta da IA: " . ($resp['error']['message'] ?? 'Desconhecido');
                }
            }
            curl_close($ch);
        } else {
            $message = "Erro: API Key não configurada (Módulo Coach).";
        }
    }
}

// Fetch Reports
$reports = $pdo->prepare("SELECT * FROM market_intelligence WHERE company_id = ? ORDER BY created_at DESC");
$reports->execute([$companyId]);
$allReports = $reports->fetchAll();

// Colors
$levelColors = [
    'critical' => '#ef4444',
    'high' => '#f97316',
    'medium' => '#eab308',
    'low' => '#3b82f6',
    'info' => '#94a3b8'
];

$catLabels = [
    'legislation' => '⚖️ Legislação',
    'technology' => '🤖 Tecnologia',
    'market' => '📈 Mercado',
    'competitors' => '⚔️ Concorrentes',
    'patents' => '💡 Patentes',
    'articles' => '🔬 Artigos Científicos'
];
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Inteligência Competitiva - Engaja</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .intel-grid {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 2rem;
        }

        .form-card {
            background: rgba(255, 255, 255, 0.05);
            padding: 1.5rem;
            border-radius: 1rem;
            border: 1px solid var(--glass-border);
            height: fit-content;
        }

        .report-card {
            background: rgba(30, 41, 59, 0.5);
            padding: 1.5rem;
            border-radius: 1rem;
            border: 1px solid var(--glass-border);
            margin-bottom: 1.5rem;
        }

        .level-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 1rem;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
            color: white;
            display: inline-block;
            margin-bottom: 0.5rem;
        }

        @media (max-width: 900px) {
            .intel-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem;">
                <h1>👁️ Inteligência Competitiva</h1>
                <?php if (!$sector): ?>
                    <span style="color:#f87171;">⚠️ Setor não definido!</span>
                <?php else: ?>
                    <span class="badge badge-admin" style="background:#3b82f6;">Setor:
                        <?php echo htmlspecialchars($sector); ?>
                    </span>
                <?php endif; ?>
            </div>

            <?php if ($message): ?>
                <div
                    style="margin-bottom:1.5rem; color:#34d399; background:rgba(16,185,129,0.1); padding:1rem; border-radius:0.5rem;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php
            $aiConfig = getCoachAIConfig($pdo, $companyId);
            if (!$aiConfig || empty($aiConfig['api_key'])):
                ?>
                <div class="form-card" style="max-width:500px; margin:0 auto; margin-bottom:2rem; border-color:#f87171;">
                    <h3 style="color:#f87171;">⚠️ Configuração Necessária</h3>
                    <p style="color:var(--text-muted); margin-bottom:1rem;">Para usar a IA, configure sua chave da OpenAI.
                    </p>
                    <form method="POST">
                        <input type="hidden" name="action" value="save_apikey">
                        <input type="password" name="api_key" required placeholder="sk-..."
                            style="width:100%; padding:0.8rem; background:#1e293b; border:1px solid var(--glass-border); color:white; border-radius:0.5rem; margin-bottom:1rem;">
                        <button class="btn btn-primary" style="width:100%;">Salvar Chave</button>
                    </form>
                </div>
            <?php endif; ?>

            <?php if (!$sector): ?>
                <div class="form-card" style="max-width:500px; margin:0 auto;">
                    <h3>Definir Setor de Atuação</h3>
                    <p style="color:var(--text-muted); margin-bottom:1rem;">Para que a IA monitore corretamente, informe o
                        setor da empresa.</p>
                    <form method="POST">
                        <input type="hidden" name="action" value="save_sector">
                        <input type="text" name="sector" required placeholder="Ex: Fintech, Varejo de Moda, Agronegócio..."
                            style="width:100%; padding:0.8rem; background:#1e293b; border:1px solid var(--glass-border); color:white; border-radius:0.5rem; margin-bottom:1rem;">
                        <button class="btn btn-primary" style="width:100%;">Salvar Definir</button>
                    </form>
                </div>
            <?php else: ?>

                <div class="intel-grid">
                    <!-- FORM -->
                    <div class="form-card">
                        <h3>Nova Análise</h3>
                        <p style="color:var(--text-muted); font-size:0.9rem;">Monitore tópicos específicos.</p>
                        <form method="POST">
                            <input type="hidden" name="action" value="generate_analysis">

                            <label style="display:block; margin-top:1rem;">Categoria</label>
                            <select name="category" required
                                style="width:100%; padding:0.8rem; background:#1e293b; border:1px solid var(--glass-border); color:white; border-radius:0.5rem;">
                                <?php foreach ($catLabels as $k => $v): ?>
                                    <option value="<?php echo $k; ?>">
                                        <?php echo $v; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <label style="display:block; margin-top:1rem;">Tópico / Palavra-chave</label>
                            <input type="text" name="topic" required
                                placeholder="Ex: Nova Lei de IA, Blockchain, Startup X..."
                                style="width:100%; padding:0.8rem; background:#1e293b; border:1px solid var(--glass-border); color:white; border-radius:0.5rem;">

                            <label style="display:block; margin-top:1rem;">Contexto (Opcional)</label>
                            <textarea name="context" rows="3"
                                placeholder="Cole aqui uma notícia recente ou detalhe específico para análise..."
                                style="width:100%; padding:0.8rem; background:#1e293b; border:1px solid var(--glass-border); color:white; border-radius:0.5rem;"></textarea>

                            <button class="btn btn-primary"
                                style="margin-top:1.5rem; width:100%; background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                                🔮 Gerar Análise AI
                            </button>
                        </form>

                        <div
                            style="margin-top:2rem; padding:1rem; background:rgba(255,255,255,0.03); border-radius:0.5rem;">
                            <small style="color:var(--text-muted);">A IA analisará o tópico cruzando com o setor <strong>
                                    <?php echo htmlspecialchars($sector); ?>
                                </strong> para identificar riscos e ações.</small>
                        </div>
                    </div>

                    <!-- REPORTS LIST -->
                    <div>
                        <h3>Monitoramento & Relatórios</h3>
                        <?php if (empty($allReports)): ?>
                            <p style="color:var(--text-muted);">Nenhuma análise gerada ainda.</p>
                        <?php else: ?>
                            <?php foreach ($allReports as $r): ?>
                                <div class="report-card animate-fade">
                                    <div style="display:flex; justify-content:space-between; align-items:start;">
                                        <div>
                                            <span class="level-badge"
                                                style="background:<?php echo $levelColors[$r['action_level']] ?? '#94a3b8'; ?>">
                                                <?php echo strtoupper($r['action_level'] == 'info' ? 'Informativo' : ($r['action_level'] == 'critical' ? 'Crítico' : $r['action_level'])); ?>
                                            </span>
                                            <h4 style="margin:0; font-size:1.1rem;">
                                                <?php echo htmlspecialchars($r['topic']); ?>
                                            </h4>
                                            <div style="font-size:0.85rem; color:var(--text-muted); margin-top:0.3rem;">
                                                <?php echo $catLabels[$r['category']] ?? $r['category']; ?> •
                                                <?php echo date('d/m/Y H:i', strtotime($r['created_at'])); ?>
                                            </div>
                                        </div>
                                        <form method="POST" onsubmit="return confirm('Excluir analise?');">
                                            <!-- Delete logic not implemented but visual only -->
                                            <button disabled
                                                style="background:none; border:none; color:var(--text-muted); cursor:not-allowed;">🗑️</button>
                                        </form>
                                    </div>
                                    <hr style="border-color:var(--glass-border); margin:1rem 0;">
                                    <div style="line-height:1.6; color:#e2e8f0;">
                                        <?php echo $r['content']; // HTML content from AI ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            <?php endif; ?>
        </main>
    </div>
</body>

</html>
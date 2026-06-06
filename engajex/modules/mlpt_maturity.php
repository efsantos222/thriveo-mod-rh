<?php
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE);

require_once '../config.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}
$role = $_SESSION['role'] ?? 'employee';
$isAdmin = in_array($role, ['admin', 'manager', 'responsible']);
$companyId = $_SESSION['company_id'] ?? 1;

// View Routing
$view = $_GET['view'] ?? 'dashboard';

// --- ACTIONS HANDLER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin) {
    if ($_POST['action'] === 'save_assessment') {
        $c = (int) $_POST['credibility'];
        $r = (int) $_POST['respect'];
        $i = (int) $_POST['impartiality'];
        $p = (int) $_POST['pride'];
        $cam = (int) $_POST['camaraderie'];
        $avg = ($c + $r + $i + $p + $cam) / 5;

        $pdo->prepare("INSERT INTO mlpt_maturity (company_id, credibility_score, respect_score, impartiality_score, pride_score, camaraderie_score, overall_score) VALUES (?, ?, ?, ?, ?, ?, ?)")
            ->execute([$companyId, $c, $r, $i, $p, $cam, $avg]);
        header("Location: mlpt_maturity.php?view=dashboard");
        exit;
    }

    if ($_POST['action'] === 'new_pulse') {
        $title = $_POST['title'] ? $_POST['title'] : "Pesquisa de Clima - " . date("M/Y");
        $pdo->prepare("INSERT INTO mlpt_pulse_surveys (company_id, title, status) VALUES (?, ?, 'active')")
            ->execute([$companyId, $title]);
        header("Location: mlpt_maturity.php?view=pulse");
        exit;
    }

    if ($_POST['action'] === 'analyze_pulse') {
        try {
            $sid = $_POST['survey_id'];

            // Fetch comments
            $stmt = $pdo->prepare("SELECT comment, rating FROM mlpt_pulse_responses WHERE survey_id = ? AND comment != ''");
            $stmt->execute([$sid]);
            $comments = $stmt->fetchAll();

            if (count($comments) > 0) {
                // Mock AI Analysis (Simulating logic based on keywords/ratings)
                $positive = 0;
                $negative = 0;
                $themes = [];
                foreach ($comments as $c) {
                    if ($c['rating'] >= 9)
                        $positive++;
                    if ($c['rating'] <= 6)
                        $negative++;
                    // Simple keyword extraction
                    $words = explode(' ', strtolower($c['comment']));
                    foreach ($words as $w) {
                        if (strlen($w) > 4)
                            $themes[$w] = ($themes[$w] ?? 0) + 1;
                    }
                }
                arsort($themes);
                $topTheme = array_key_first($themes) ? array_key_first($themes) : 'geral';

                $summary = "<strong>Análise de Sentimento (IA):</strong><br>";
                $summary .= "Baseado em " . count($comments) . " comentários analisados.<br>";
                $summary .= "• Sentimento: " . ($positive > $negative ? "<span style='color:#4ade80'>Predominantemente Positivo</span>" : "<span style='color:#facc15'>Misto/Atenção</span>") . "<br>";
                $summary .= "• Destaque Positivo: Engajamento alto dos promotores.<br>";
                $summary .= "• Ponto de Atenção: Menções frequentes sobre '{$topTheme}'.";


                // FORCE DEBUG REMOVED - Normal Execution
                $pdo->prepare("UPDATE mlpt_pulse_surveys SET ai_summary = ? WHERE id = ?")->execute([$summary, $sid]);
            } else {
                // No comments
                $pdo->prepare("UPDATE mlpt_pulse_surveys SET ai_summary = 'Sem comentários suficientes para análise qualitativa.' WHERE id = ?")->execute([$sid]);
            }

        } catch (Exception $e) {
            // SHOW ERROR
            die("ERRO AO PROCESSAR IA: " . $e->getMessage());
        }

        header("Location: mlpt_maturity.php?view=pulse");
        exit;
    }
    if ($_POST['action'] === 'analyze_doc') {
        $title = $_POST['title'];
        $content = $_POST['content'];
        // Mock AI Result
        $analysis = "<strong>Análise IA:</strong> O texto demonstra forte alinhamento com práticas de transparência. Pontos fortes: Clareza nas normas. Pontos de atenção: Falta detalhamento sobre diversidade.";

        $pdo->prepare("INSERT INTO mlpt_documents (company_id, title, content, analysis_result) VALUES (?, ?, ?, ?)")
            ->execute([$companyId, $title, $content, $analysis]);
        header("Location: mlpt_maturity.php?view=docs");
        exit;
    }

    if ($_POST['action'] === 'delete_pulse') {
        $sid = $_POST['survey_id'];
        $pdo->prepare("DELETE FROM mlpt_pulse_surveys WHERE id = ?")->execute([$sid]);
        header("Location: mlpt_maturity.php?view=pulse");
        exit;
    }
}

// --- DATA FETCHING ---
// 1. Dashboard Data
$stmt = $pdo->prepare("SELECT * FROM mlpt_maturity WHERE company_id = ? ORDER BY assessment_date DESC LIMIT 1");
$stmt->execute([$companyId]);
$lastMaturity = $stmt->fetch();

// Defaults for Charts if no data
$chartData = [
    'cred' => $lastMaturity['credibility_score'] ?? 0,
    'resp' => $lastMaturity['respect_score'] ?? 0,
    'imp' => $lastMaturity['impartiality_score'] ?? 0,
    'pride' => $lastMaturity['pride_score'] ?? 0,
    'cam' => $lastMaturity['camaraderie_score'] ?? 0,
    'overall' => $lastMaturity['overall_score'] ?? 0
];

// 2. Pulse Data
$stmt = $pdo->prepare("
    SELECT s.id, s.title, s.status, s.created_at, s.company_id, s.ai_summary,
           COUNT(r.id) as response_count, 
           AVG(r.rating) as avg_rating 
    FROM mlpt_pulse_surveys s 
    LEFT JOIN mlpt_pulse_responses r ON s.id = r.survey_id 
    WHERE s.company_id = ? 
    GROUP BY s.id 
    ORDER BY s.created_at DESC
");
$stmt->execute([$companyId]);
$pulses = $stmt->fetchAll();

// 3. Docs Data
// ... existing docs query ...
$stmt = $pdo->prepare("SELECT * FROM mlpt_documents WHERE company_id = ? ORDER BY created_at DESC");
$stmt->execute([$companyId]);
$docs = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>MLPT System</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* MLPT Specific Styles — Light Theme */
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f4f6fa;
            color: #0f172a;
        }

        .mlpt-layout {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* Internal Sidebar */
        .mlpt-sidebar {
            width: 260px;
            background: #ffffff;
            border-right: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            padding: 1.5rem;
            box-shadow: 1px 0 3px rgba(0,0,0,0.04);
        }

        .mlpt-logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .mlpt-nav-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: #64748b;
            text-decoration: none;
            border-radius: 0.5rem;
            margin-bottom: 0.25rem;
            transition: all 0.2s;
            font-size: 0.95rem;
        }

        .mlpt-nav-item:hover,
        .mlpt-nav-item.active {
            background: #eef2ff;
            color: #4f46e5;
        }

        .mlpt-nav-item i {
            width: 24px;
            margin-right: 0.5rem;
        }

        /* Main Area */
        .mlpt-main {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
            background: #f4f6fa;
        }

        .mlpt-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .mlpt-title h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .mlpt-title p {
            color: #64748b;
            font-size: 0.9rem;
        }

        .page-header-action {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* Cards */
        .mlpt-card {
            background: #ffffff;
            border-radius: 1rem;
            border: 1px solid #e2e8f0;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }

        .mlpt-card h3 {
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            color: #0f172a;
        }

        /* Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        /* Forms */
        .mlpt-form-group {
            margin-bottom: 1.5rem;
        }

        .mlpt-label {
            display: block;
            margin-bottom: 0.5rem;
            color: #475569;
            font-size: 0.9rem;
        }

        .mlpt-input {
            width: 100%;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 0.75rem;
            border-radius: 0.5rem;
            color: #0f172a;
            font-family: inherit;
        }

        .mlpt-range-slider {
            width: 100%;
            accent-color: #4f46e5;
            cursor: pointer;
        }

        /* Others */
        .pulse-item {
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .copy-box {
            background: #f1f5f9;
            color: #334155;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-family: monospace;
            font-size: 0.85rem;
            display: inline-block;
        }

        .badge-sector {
            background: #e2e8f0;
            color: #334155;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
        }
    </style>
</head>

<body>

    <div class="mlpt-layout">
        <!-- Sidebar Interna MLPT -->
        <aside class="mlpt-sidebar">
            <div class="mlpt-logo">
                <span>MLPT System</span>
            </div>

            <nav>
                <a href="?view=dashboard" class="mlpt-nav-item <?php echo $view == 'dashboard' ? 'active' : ''; ?>">
                    <i>📊</i> Dashboard
                </a>
                <a href="?view=pulse" class="mlpt-nav-item <?php echo $view == 'pulse' ? 'active' : ''; ?>">
                    <i>💓</i> Pulso & Clima
                </a>
                <a href="?view=assessment" class="mlpt-nav-item <?php echo $view == 'assessment' ? 'active' : ''; ?>">
                    <i>⚡</i> Avaliação Maturidade
                </a>
                <a href="?view=docs" class="mlpt-nav-item <?php echo $view == 'docs' ? 'active' : ''; ?>">
                    <i>📄</i> Análise Documental
                </a>
                <a href="?view=trust" class="mlpt-nav-item <?php echo $view == 'trust' ? 'active' : ''; ?>">
                    <i>🧪</i> Simulador Trust Index
                </a>
                <div style="margin-top: 2rem; border-top: 1px solid #e2e8f0; padding-top: 1rem;">
                    <a href="../dashboard.php" class="mlpt-nav-item">
                        <i>🔙</i> Voltar ao Engaja
                    </a>
                </div>
            </nav>
        </aside>

        <main class="mlpt-main">

            <?php if ($view === 'dashboard'): ?>
                <div class="mlpt-header">
                    <div class="mlpt-title">
                        <h1>Olá, <?php echo htmlspecialchars($_SESSION['name']); ?></h1>
                        <p>Maturidade e Clima Organizacional.</p>
                    </div>
                    <div class="badge-sector">Setor: Tecnologia</div>
                </div>

                <div class="dashboard-grid">
                    <!-- Radar Chart -->
                    <div class="mlpt-card">
                        <h3>Maturidade vs Setor</h3>
                        <div style="height: 300px;">
                            <canvas id="radarChart"></canvas>
                        </div>
                    </div>

                    <!-- Bar Chart -->
                    <div class="mlpt-card">
                        <h3>Benchmarking Setorial</h3>
                        <div style="height: 300px;">
                            <canvas id="barChart"></canvas>
                        </div>
                    </div>
                </div>

                <script>
                    // Data from PHP
                    const scores = <?php echo json_encode(array_values($chartData)); ?>;
                    // Remove Overall from Radar (last item)
                    const radarScores = scores.slice(0, 5);
                    // Set default sector data for benchmarking
                    const sectorScores = [65, 60, 55, 70, 60];

                    // Radar Chart
                    new Chart(document.getElementById('radarChart'), {
                        type: 'radar',
                        data: {
                            labels: ['Credibilidade', 'Respeito', 'Imparcialidade', 'Orgulho', 'Camaradagem'],
                            datasets: [{
                                label: 'Sua Empresa',
                                data: radarScores,
                                backgroundColor: 'rgba(96, 165, 250, 0.2)',
                                borderColor: '#60a5fa',
                                pointBackgroundColor: '#60a5fa'
                            }, {
                                label: 'Média de Tecnologia',
                                data: sectorScores,
                                backgroundColor: 'rgba(148, 163, 184, 0.1)',
                                borderColor: '#94a3b8',
                                borderDash: [5, 5],
                                pointBackgroundColor: '#94a3b8'
                            }]
                        },
                        options: {
                            scales: { r: { min: 0, max: 100, ticks: { display: false }, grid: { color: 'rgba(0,0,0,0.08)' }, angleLines: { color: 'rgba(0,0,0,0.08)' } } },
                            plugins: { legend: { labels: { color: '#334155' } } }
                        }
                    });

                    // Bar Chart
                    new Chart(document.getElementById('barChart'), {
                        type: 'bar',
                        data: {
                            labels: ['Geral', 'Cred.', 'Resp.', 'Imp.', 'Org.', 'Cam.'],
                            datasets: [{
                                label: 'Sua Empresa',
                                data: scores, // Includes overall
                                backgroundColor: '#6366f1'
                            }, {
                                label: 'Setor (Tecnologia)',
                                data: [62, 65, 60, 55, 70, 60],
                                backgroundColor: '#94a3b8'
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: { y: { beginAtZero: true, max: 100, grid: { color: 'rgba(0,0,0,0.06)' } }, x: { grid: { display: false } } },
                            plugins: { legend: { labels: { color: '#334155' } } }
                        }
                    });
                </script>

            <?php elseif ($view === 'pulse'): ?>
                <div class="mlpt-header">
                    <div class="mlpt-title">
                        <h1>Pulso Mensal</h1>
                        <p>Pesquisas rápidas de engajamento.</p>
                    </div>
                    <?php if ($isAdmin): ?>
                        <button class="btn btn-primary"
                            onclick="document.getElementById('newPulseModal').style.display='flex'">+ Novo Pulso</button>
                    <?php endif; ?>
                </div>

                <?php foreach ($pulses as $pulse): ?>
                    <div class="mlpt-card">
                        <div style="display:flex; justify-content:space-between; margin-bottom: 1rem;">
                            <h3 style="margin:0;"><?php echo htmlspecialchars($pulse['title']); ?></h3>
                            <div style="display:flex; gap: 0.5rem; align-items:center;">
                                <div class="badge-sector"><?php echo ucfirst($pulse['status']); ?></div>
                                <?php if ($isAdmin): ?>
                                    <form method="POST"
                                        onsubmit="return confirm('Tem certeza que deseja excluir esta pesquisa e todas as respostas?');"
                                        style="margin:0;">
                                        <input type="hidden" name="action" value="delete_pulse">
                                        <input type="hidden" name="survey_id" value="<?php echo $pulse['id']; ?>">
                                        <button class="btn-icon"
                                            style="background:none; border:none; cursor:pointer; font-size:1.1rem;"
                                            title="Excluir">🗑️</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p class="mlpt-label">Criado em <?php echo date("d/m/Y", strtotime($pulse['created_at'])); ?></p>

                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;">
                            <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 0.5rem;">Link para envio:</p>
                            <div class="copy-box">
                                https://thriveo.com.br/engajex/mlpt_pulse_vote.php?s=<?php echo $pulse['id']; ?></div>
                        </div>

                        <div>
                            <?php if (!empty($pulse['ai_summary'])): ?>
                                <div
                                    style="background: rgba(99, 102, 241, 0.1); border-left: 3px solid #6366f1; padding: 1rem; border-radius: 0.25rem; margin-top: 1rem; font-size: 0.9rem;">
                                    <?php echo $pulse['ai_summary']; ?>
                                </div>
                            <?php else: ?>
                                <?php if ($pulse['response_count'] > 0 && $isAdmin): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="analyze_pulse">
                                        <input type="hidden" name="survey_id" value="<?php echo $pulse['id']; ?>">
                                        <button class="btn btn-outline" style="font-size: 0.85rem;">✨ Processar IA de
                                            Sentimento</button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-outline" style="font-size: 0.85rem; opacity: 0.5; cursor: not-allowed;"
                                        disabled>Aguardando respostas para IA</button>
                                <?php endif; ?>
                            <?php endif; ?>

                            <span style="font-size: 0.85rem; margin-left: 1rem; color: #d97706;">
                                ★ <?php echo number_format($pulse['avg_rating'], 1); ?>/10
                                (<?php echo $pulse['response_count']; ?> respostas)
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>

            <?php elseif ($view === 'assessment'): ?>
                <div class="mlpt-header">
                    <div class="mlpt-title">
                        <h1>Nova Avaliação de Maturidade</h1>
                    </div>
                </div>

                <div class="mlpt-card" style="max-width: 800px; margin: 0 auto;">
                    <p style="margin-bottom: 2rem; color: #64748b;">Auto-avalie a empresa de 0 a 100 em cada um dos pilares.
                    </p>
                    <form method="POST">
                        <input type="hidden" name="action" value="save_assessment">

                        <?php
                        $dims = [
                            'credibility' => 'Credibilidade',
                            'respect' => 'Respeito',
                            'impartiality' => 'Imparcialidade',
                            'pride' => 'Orgulho',
                            'camaraderie' => 'Camaradagem'
                        ];
                        foreach ($dims as $key => $label):
                            ?>
                            <div class="mlpt-form-group">
                                <div style="display:flex; justify-content:space-between;">
                                    <label class="mlpt-label"><?php echo $label; ?></label>
                                    <span id="val_<?php echo $key; ?>" style="color: #60a5fa;">50</span>
                                </div>
                                <input type="range" name="<?php echo $key; ?>" class="mlpt-range-slider" min="0" max="100"
                                    value="50"
                                    oninput="document.getElementById('val_<?php echo $key; ?>').innerText = this.value">
                            </div>
                        <?php endforeach; ?>

                        <button class="btn btn-primary" style="width: 100%;">Salvar Avaliação</button>
                    </form>
                </div>

            <?php elseif ($view === 'docs'): ?>
                <div class="mlpt-header">
                    <div class="mlpt-title">
                        <h1>Análise Documental com IA</h1>
                    </div>
                </div>

                <?php if ($isAdmin): ?>
                    <div class="mlpt-card">
                        <h3>Nova Análise</h3>
                        <form method="POST">
                            <input type="hidden" name="action" value="analyze_doc">
                            <div class="mlpt-form-group">
                                <input type="text" name="title" class="mlpt-input"
                                    placeholder="Título do Documento (ex: Código de Conduta)" required>
                            </div>
                            <div class="mlpt-form-group">
                                <textarea name="content" class="mlpt-input" rows="6"
                                    placeholder="Cole o texto do documento aqui..." required></textarea>
                            </div>
                            <button class="btn btn-primary">Analisar com IA</button>
                            <p style="font-size: 0.75rem; color: #64748b; margin-top: 0.5rem;">*Requer configuração de chave API
                                pelo administrador.</p>
                        </form>
                    </div>
                <?php endif; ?>

                <h3 style="margin-bottom: 1.5rem; color: #334155;">Histórico de Análises</h3>
                <?php foreach ($docs as $doc): ?>
                    <div class="mlpt-card">
                        <div style="margin-bottom: 1rem;">
                            <span
                                style="font-weight: 700; font-size: 1.1rem; color: #0f172a;"><?php echo htmlspecialchars($doc['title']); ?></span>
                            <span
                                style="float: right; font-size: 0.8rem; color: #64748b;"><?php echo date("d/m/Y", strtotime($doc['created_at'])); ?></span>
                        </div>
                        <div
                            style="background: rgba(99, 102, 241, 0.1); border-left: 3px solid #6366f1; padding: 1rem; border-radius: 0.25rem;">
                            <?php echo $doc['analysis_result']; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

            <?php elseif ($view === 'trust'): ?>
                <div class="mlpt-header">
                    <div class="mlpt-title">
                        <h1>Simulador Trust Index™</h1>
                        <p>Simule cenários e veja como atingir a certificação.</p>
                    </div>
                </div>

                <div class="dashboard-grid">
                    <!-- Controles -->
                    <div class="mlpt-card">
                        <h3>Ajuste os Pilares</h3>
                        <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 2rem;">
                            Arraste os sliders para simular a percepção dos colaboradores em cada dimensão.
                        </p>

                        <?php
                        $pillars = [
                            'credibility' => ['label' => 'Credibilidade', 'desc' => 'Comunicação, competência e integridade.'],
                            'respect' => ['label' => 'Respeito', 'desc' => 'Suporte, colaboração e cuidado.'],
                            'impartiality' => ['label' => 'Imparcialidade', 'desc' => 'Justiça, equidade e isenção.'],
                            'pride' => ['label' => 'Orgulho', 'desc' => 'Do trabalho, da equipe e da empresa.'],
                            'camaraderie' => ['label' => 'Camaradagem', 'desc' => 'Intimidade, hospitalidade e comunidade.']
                        ];
                        foreach ($pillars as $key => $p):
                            ?>
                            <div class="mlpt-form-group">
                                <div style="display:flex; justify-content:space-between; margin-bottom: 0.5rem;">
                                    <div>
                                        <label class="mlpt-label" style="margin-bottom:0; color:#0f172a;">
                                            <?php echo $p['label']; ?>
                                        </label>
                                        <span style="font-size: 0.75rem; color:#64748b;">
                                            <?php echo $p['desc']; ?>
                                        </span>
                                    </div>
                                    <span id="val_sim_<?php echo $key; ?>" style="color: #4f46e5; font-weight: 700;">70</span>
                                </div>
                                <input type="range" id="sim_<?php echo $key; ?>" class="mlpt-range-slider" min="0" max="100"
                                    value="70" oninput="updateSimulator()">
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Resultado -->
                    <div class="mlpt-card"
                        style="text-align: center; display: flex; flex-direction: column; justify-content: center;">
                        <h3 style="margin-bottom: 0.5rem;">Trust Index Projetado</h3>

                        <div
                            style="position: relative; height: 250px; width: 100%; display: flex; align-items: center; justify-content: center;">
                            <canvas id="trustGauge"></canvas>
                            <div
                                style="position: absolute; top: 65%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                                <div id="finalScore" style="font-size: 3.5rem; font-weight: 800; color: #0f172a;">70</div>
                                <div id="finalLabel" style="font-size: 1rem; color: #64748b;">Na Média de Mercado</div>
                            </div>
                        </div>

                        <div
                            style="margin-top: 2rem; text-align: left; background: #f8fafc; border: 1px solid #e2e8f0; padding: 1.5rem; border-radius: 0.5rem;">
                            <h4 style="margin:0 0 0.5rem 0; color: #d97706;">💡 Insight da IA</h4>
                            <p id="aiInsight" style="color: #334155; font-size: 0.9rem; margin:0;">
                                Ajuste os valores para ver a análise.
                            </p>
                        </div>
                    </div>
                </div>

                <script>
                    let gaugeChart;

                    function initGauge() {
                        const ctx = document.getElementById('trustGauge').getContext('2d');
                        gaugeChart = new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: ['Score', 'Restante'],
                                datasets: [{
                                    data: [70, 30],
                                    backgroundColor: ['#6366f1', 'rgba(0, 0, 0, 0.06)'],
                                    borderWidth: 0,
                                    circumference: 180,
                                    rotation: 270,
                                    cutout: '80%'
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false }, tooltip: { enabled: false } }
                            }
                        });
                        updateSimulator();
                    }

                    function updateSimulator() {
                        const p1 = parseInt(document.getElementById('sim_credibility').value);
                        const p2 = parseInt(document.getElementById('sim_respect').value);
                        const p3 = parseInt(document.getElementById('sim_impartiality').value);
                        const p4 = parseInt(document.getElementById('sim_pride').value);
                        const p5 = parseInt(document.getElementById('sim_camaraderie').value);

                        // Update Labels
                        document.getElementById('val_sim_credibility').innerText = p1;
                        document.getElementById('val_sim_respect').innerText = p2;
                        document.getElementById('val_sim_impartiality').innerText = p3;
                        document.getElementById('val_sim_pride').innerText = p4;
                        document.getElementById('val_sim_camaraderie').innerText = p5;

                        // Calc Average
                        const avg = Math.round((p1 + p2 + p3 + p4 + p5) / 5);

                        // Update Chart
                        gaugeChart.data.datasets[0].data = [avg, 100 - avg];

                        // Color Logic
                        let color = '#ef4444'; // Red
                        let label = 'Crítico';
                        let insight = 'A cultura precisa de atenção urgente. Foque em construir a base da confiança (Credibilidade).';

                        if (avg >= 50) { color = '#f59e0b'; label = 'Em Desenvolvimento'; insight = 'Há pontos positivos, mas a consistência é necessária.'; }
                        if (avg >= 70) { color = '#3b82f6'; label = 'Bom Lugar para Trabalhar'; insight = 'Bom caminho! Para atingir a excelência, refine a Imparcialidade e o Orgulho.'; }
                        if (avg >= 85) { color = '#10b981'; label = 'Excelente (Zona de Certificação)'; insight = 'Parabéns! Níveis de excelência. O foco agora é manutenção e inovação cultural.'; }

                        gaugeChart.data.datasets[0].backgroundColor[0] = color;
                        gaugeChart.update();

                        // Update Text
                        const scoreEl = document.getElementById('finalScore');
                        scoreEl.innerText = avg;
                        scoreEl.style.color = color;
                        document.getElementById('finalLabel').innerText = label;
                        document.getElementById('finalLabel').style.color = color;
                        document.getElementById('aiInsight').innerText = insight;
                    }

                    // Init on Load if tab is active
                    if (document.getElementById('trustGauge')) {
                        setTimeout(initGauge, 100);
                    }
                </script>
            <?php endif; ?>

        </main>
    </div>

    <!-- Pulse Modal -->
    <div id="newPulseModal" class="modal"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:100; align-items:center; justify-content:center;">
        <div style="background: #ffffff; border: 1px solid #e2e8f0; padding: 2rem; border-radius: 1rem; width: 90%; max-width: 400px;">
            <h2 style="margin-bottom: 1rem;">Nova Pesquisa de Pulso</h2>
            <form method="POST">
                <input type="hidden" name="action" value="new_pulse">
                <div class="mlpt-form-group">
                    <label class="mlpt-label">Título da Pesquisa</label>
                    <input type="text" name="title" class="mlpt-input" placeholder="Ex: Clima Janeiro 2026" required>
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button class="btn btn-primary" style="flex: 1;">Criar</button>
                    <button type="button" class="btn btn-outline" style="flex: 1;"
                        onclick="document.getElementById('newPulseModal').style.display='none'">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

</body>

</html>
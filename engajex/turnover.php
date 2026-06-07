<?php
require_once 'config.php';

// Permissions
if (!isLoggedIn() || !in_array($_SESSION['role'], ['responsible', 'company_admin', 'admin'])) {
    header("Location: login.php");
    exit;
}

$companyId = $_SESSION['company_id'] ?? null;
if (in_array($_SESSION['role'], ['responsible', 'company_admin']) && !$companyId) {
    $stmt = $pdo->prepare("SELECT company_id FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $companyId = $stmt->fetchColumn();
}

$message = '';
$aiAnalysis = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Add Exit Record
    if (isset($_POST['action']) && $_POST['action'] === 'add_exit') {
        $date = $_POST['date'];
        $type = $_POST['type'];
        $reason = $_POST['reason'];

        try {
            $stmt = $pdo->prepare("INSERT INTO turnover_exits (company_id, exit_date, type, reason) VALUES (?, ?, ?, ?)");
            $stmt->execute([$companyId, $date, $type, $reason]);
            $message = "Desligamento registrado com sucesso.";
        } catch (Exception $e) {
            $message = "Erro ao registrar.";
        }
    }

    // 2. Update Headcount
    if (isset($_POST['action']) && $_POST['action'] === 'update_headcount') {
        $month = $_POST['month'];
        $year = $_POST['year'];
        $count = $_POST['count'];

        try {
            $stmt = $pdo->prepare("REPLACE INTO monthly_headcount (company_id, month, year, count) VALUES (?, ?, ?, ?)");
            $stmt->execute([$companyId, $month, $year, $count]);
            $message = "Efetivo atualizado.";
        } catch (Exception $e) {
            $message = "Erro ao atualizar efetivo.";
        }
    }

    // 3. AI Analysis Request
    if (isset($_POST['action']) && $_POST['action'] === 'analyze') {
        // Fetch data based on the filtering year (passed as hidden or we use the recently submitted context, but usually analysis is on the viewed data)
        // Let's grab the year for analysis from the GET param if available, or POST, or default
        $analyzeYear = $_GET['filter_year'] ?? date('Y');

        $stmt = $pdo->prepare("SELECT * FROM turnover_exits WHERE company_id = ? AND YEAR(exit_date) = ? ORDER BY exit_date DESC");
        $stmt->execute([$companyId, $analyzeYear]);
        $exits = $stmt->fetchAll();

        $exitData = "";
        foreach ($exits as $ex) {
            $exitData .= "- Data: {$ex['exit_date']}, Tipo: {$ex['type']}, Motivo: {$ex['reason']}\n";
        }

        // Get API Key
        $stmtKey = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'openai_api_key'");
        $apiKey = $stmtKey->fetchColumn();

        if ($apiKey && $exitData) {
            $prompt = "Analise os dados de turnover do ano de $analyzeYear e sugira melhorias:\n\n" . $exitData;

            $data = [
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'system', 'content' => 'Você é um especialista em RH.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.5
            ];

            $ch = curl_init('https://api.openai.com/v1/chat/completions');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            $response = curl_exec($ch);
            curl_close($ch);

            $resData = json_decode($response, true);
            $aiAnalysis = $resData['choices'][0]['message']['content'] ?? "Erro ao analisar.";
        } else {
            $aiAnalysis = "Sem dados suficientes para análise neste ano.";
        }
    }
}

// Year Selection Logic
$availableYears = [date('Y')];

// Fetch years from Exits
$stmt = $pdo->prepare("SELECT DISTINCT YEAR(exit_date) as y FROM turnover_exits WHERE company_id = ?");
$stmt->execute([$companyId]);
while ($row = $stmt->fetch()) {
    $availableYears[] = $row['y'];
}

// Fetch years from Headcount
$stmt = $pdo->prepare("SELECT DISTINCT year as y FROM monthly_headcount WHERE company_id = ?");
$stmt->execute([$companyId]);
while ($row = $stmt->fetch()) {
    $availableYears[] = $row['y'];
}

$availableYears = array_unique($availableYears);
rsort($availableYears);

$selectedYear = $_GET['filter_year'] ?? date('Y');

// Data Fetching for Dashboard (Filtered by Year)
// 1. Headcount Data
$headcounts = [];
$stmt = $pdo->prepare("SELECT * FROM monthly_headcount WHERE company_id = ? AND year = ? ORDER BY month ASC");
$stmt->execute([$companyId, $selectedYear]);
$hcData = $stmt->fetchAll();
foreach ($hcData as $row) {
    $headcounts[$row['month']] = $row['count'];
}

// 2. Exits Data
$stmt = $pdo->prepare("SELECT * FROM turnover_exits WHERE company_id = ? AND YEAR(exit_date) = ? ORDER BY exit_date DESC");
$stmt->execute([$companyId, $selectedYear]);
$exitsData = $stmt->fetchAll();

// Calculate Totals
$totalDismissals = 0;
$employeeInitiated = 0;

foreach ($exitsData as $ex) {
    $totalDismissals++;
    if ($ex['type'] === 'colaborador')
        $employeeInitiated++;
}

// Calculate Average Headcount
$monthsWithData = count($headcounts);
$avgHeadcount = 0;
if ($monthsWithData > 0) {
    $avgHeadcount = array_sum($headcounts) / $monthsWithData;
}

// Turnover Rates
$turnoverTotal = ($avgHeadcount > 0) ? ($totalDismissals / $avgHeadcount) * 100 : 0;
$turnoverEffective = ($avgHeadcount > 0) ? ($employeeInitiated / $avgHeadcount) * 100 : 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Turnover - TestProf Engaja</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .app-layout {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }


        .main-content {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
            background: var(--bg-color);
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .kpi-card {
            background: var(--card-bg);
            padding: 1.5rem;
            border-radius: 1rem;
            border: 1px solid var(--glass-border);
            text-align: center;
        }

        .kpi-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            margin: 0.5rem 0;
        }

        .kpi-label {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        .data-panel {
            background: var(--card-bg);
            border-radius: 1rem;
            border: 1px solid var(--glass-border);
            padding: 1.5rem;
        }

        .list-item {
            padding: 0.75rem;
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            justify-content: space-between;
            gap: 1rem;
        }

        .ai-box {
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(16, 185, 129, 0.1));
            border: 1px solid #6366f1;
            padding: 1.5rem;
            border-radius: 1rem;
            margin-top: 2rem;
            white-space: pre-wrap;
        }

        .year-selector {
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .year-btn {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-muted);
            text-decoration: none;
            border: 1px solid transparent;
            transition: all 0.2s;
        }

        .year-btn:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .year-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h1 style="margin-bottom: 1rem;">Turnover</h1>

                <div class="year-selector">
                    <?php foreach ($availableYears as $y): ?>
                        <a href="?filter_year=<?php echo $y; ?>"
                            class="year-btn <?php echo ($selectedYear == $y) ? 'active' : ''; ?>">
                            <?php echo $y; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if ($message): ?>
                <div
                    style="background: rgba(16, 185, 129, 0.2); color: #34d399; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- KPIs -->
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-label">Turnover Total (<?php echo $selectedYear; ?>)</div>
                    <div class="kpi-value" style="color: #f87171;"><?php echo number_format($turnoverTotal, 1); ?>%
                    </div>
                    <div class="kpi-label">Geral</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Turnover Efetivo</div>
                    <div class="kpi-value" style="color: #fbbf24;"><?php echo number_format($turnoverEffective, 1); ?>%
                    </div>
                    <div class="kpi-label">Voluntário</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Média Efetivo</div>
                    <div class="kpi-value" style="color: #38bdf8;"><?php echo number_format($avgHeadcount, 0); ?></div>
                    <div class="kpi-label">Ativos no ano</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Desligamentos</div>
                    <div class="kpi-value"><?php echo $totalDismissals; ?></div>
                    <div class="kpi-label">Total no ano</div>
                </div>
            </div>

            <div class="content-grid">
                <!-- Left: Forms & History -->
                <div style="display: flex; flex-direction: column; gap: 2rem;">

                    <!-- Forms (Inputs default to selected year for convenience) -->
                    <div class="data-panel">
                        <h3>Registrar Dados</h3>
                        <div style="display: grid; grid-template-columns: 1fr; gap: 2rem; margin-top: 1rem;">

                            <!-- Form: Exit -->
                            <form method="POST"
                                style="display: grid; grid-template-columns: 1fr 1fr 2fr auto; gap: 0.5rem; align-items: end; background: rgba(0,0,0,0.2); padding: 1rem; border-radius: 0.5rem;">
                                <input type="hidden" name="action" value="add_exit">
                                <strong style="grid-column: span 4; margin-bottom: 0.5rem; color: #fca5a5;">Novo
                                    Desligamento</strong>

                                <div class="form-group" style="margin:0;">
                                    <input type="date" name="date" class="form-control"
                                        value="<?php echo $selectedYear; ?>-<?php echo date('m-d'); ?>" required>
                                </div>
                                <div class="form-group" style="margin:0;">
                                    <select name="type" class="form-control">
                                        <option value="empresa">Empresa</option>
                                        <option value="colaborador">Colaborador</option>
                                    </select>
                                </div>
                                <div class="form-group" style="margin:0;">
                                    <input type="text" name="reason" class="form-control" placeholder="Motivo..."
                                        required>
                                </div>
                                <button type="submit" class="btn btn-primary">+</button>
                            </form>

                            <!-- Form: Headcount -->
                            <form method="POST"
                                style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 0.5rem; align-items: end; background: rgba(0,0,0,0.2); padding: 1rem; border-radius: 0.5rem;">
                                <input type="hidden" name="action" value="update_headcount">
                                <strong style="grid-column: span 4; margin-bottom: 0.5rem; color: #38bdf8;">Atualizar
                                    Efetivo Mensal</strong>

                                <div class="form-group" style="margin:0;">
                                    <input type="number" name="year" value="<?php echo $selectedYear; ?>"
                                        class="form-control" required>
                                </div>
                                <div class="form-group" style="margin:0;">
                                    <select name="month" class="form-control">
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                            <option value="<?php echo $m; ?>" <?php echo (date('n') == $m) ? 'selected' : ''; ?>><?php echo $m; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="form-group" style="margin:0;">
                                    <input type="number" name="count" class="form-control" placeholder="Qtd" required>
                                </div>
                                <button type="submit" class="btn btn-outline">Salvar</button>
                            </form>
                        </div>

                        <!-- Efetivo History Lite -->
                        <div style="margin-top: 1.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            <span
                                style="width: 100%; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.5rem;">Histórico
                                de Efetivo (<?php echo $selectedYear; ?>):</span>
                            <?php if (empty($headcounts))
                                echo '<span style="color:var(--text-muted);">Sem dados.</span>'; ?>
                            <?php foreach ($headcounts as $m => $c): ?>
                                <span
                                    style="background: rgba(56, 189, 248, 0.2); color: #38bdf8; padding: 0.2rem 0.6rem; border-radius: 4px; font-size: 0.8rem;">
                                    Mês <?php echo $m; ?>: <strong><?php echo $c; ?></strong>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Recent Exits List -->
                    <div class="data-panel">
                        <h3>Desligamentos em <?php echo $selectedYear; ?></h3>
                        <div style="margin-top: 1rem; max-height: 300px; overflow-y: auto;">
                            <?php if (empty($exitsData))
                                echo '<p style="color:var(--text-muted);">Nenhum registro encontrado neste ano.</p>'; ?>
                            <?php foreach ($exitsData as $ex): ?>
                                <div class="list-item">
                                    <span
                                        style="color: var(--text-muted);"><?php echo date('d/m', strtotime($ex['exit_date'])); ?></span>
                                    <span
                                        style="<?php echo $ex['type'] == 'colaborador' ? 'color:#fbbf24;' : 'color:#f87171;'; ?>">
                                        <?php echo ucfirst($ex['type']); ?>
                                    </span>
                                    <span><?php echo htmlspecialchars($ex['reason']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Right: Analysis -->
                <div>
                    <div class="data-panel">
                        <h3>Análise Inteligente</h3>
                        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1rem;">
                            Análise focada nos dados de <strong><?php echo $selectedYear; ?></strong>.
                        </p>
                        <form id="analysis-form" method="POST" action="?filter_year=<?php echo $selectedYear; ?>">
                            <input type="hidden" name="action" value="analyze">
                            <button type="submit" class="btn btn-primary"
                                style="width: 100%; display: flex; justify-content: center; align-items: center; gap: 0.5rem;">
                                <span>✨</span> Analisar <?php echo $selectedYear; ?>
                            </button>
                        </form>
                    </div>

                    <?php if ($aiAnalysis): ?>
                        <div class="ai-box">
                            <h4 style="margin-bottom: 1rem;">Insights (<?php echo $selectedYear; ?>):</h4>
                            <?php echo nl2br(htmlspecialchars($aiAnalysis)); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </main>
    </div>

    <!-- Loading Overlay -->
    <div id="loading-overlay"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; justify-content: center; align-items: center; flex-direction: column;">
        <div
            style="border: 4px solid rgba(255,255,255,0.3); border-top: 4px solid var(--primary-color); border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite;">
        </div>
        <p style="color: white; margin-top: 1rem; font-weight: 500;">Processando análise com IA...</p>
        <p style="color: var(--text-muted); font-size: 0.8rem;">Isso pode levar alguns segundos.</p>
    </div>

    <script>
        document.getElementById('analysis-form').addEventListener('submit', function () {
            document.getElementById('loading-overlay').style.display = 'flex';
        });
    </script>

    <style>
        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</body>

</html>
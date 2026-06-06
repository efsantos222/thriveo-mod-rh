<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../config.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$companyId = $_SESSION['company_id'] ?? 1;

// Helper to fetch data by year
function fetchData($pdo, $table, $cid, $year)
{
    // Ensure we filter by Year AND order correctly
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE company_id = ? AND ano = ? ORDER BY mes_n ASC");
    $stmt->execute([$cid, $year]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get available years (from contratacoes as reference)
$stmtYears = $pdo->prepare("SELECT DISTINCT ano FROM rh_contratacoes WHERE company_id = ? ORDER BY ano DESC");
$stmtYears->execute([$companyId]);
$availableYears = $stmtYears->fetchAll(PDO::FETCH_COLUMN);

// Default to latest year with data, or current year if none
$latestYear = !empty($availableYears) ? $availableYears[0] : date('Y');
if (empty($availableYears))
    $availableYears = [$latestYear];

// Get Year from URL or default to latest available
$selectedYear = $_GET['ano'] ?? $latestYear;

// Fetch all datasets
$contratacoes = fetchData($pdo, 'rh_contratacoes', $companyId, $selectedYear);
$demissoes = fetchData($pdo, 'rh_demissoes', $companyId, $selectedYear);
$efetivo = fetchData($pdo, 'rh_efetivo', $companyId, $selectedYear);
$folha = fetchData($pdo, 'rh_folhapag', $companyId, $selectedYear);
$humor = fetchData($pdo, 'rh_humor', $companyId, $selectedYear);
$feedback = fetchData($pdo, 'rh_feedback', $companyId, $selectedYear);
$horasext = fetchData($pdo, 'rh_horasext', $companyId, $selectedYear);
$rstempo = fetchData($pdo, 'rh_rstempo', $companyId, $selectedYear);
$rsvagas = fetchData($pdo, 'rh_rsvagas', $companyId, $selectedYear);
$rsdesc = fetchData($pdo, 'rh_rsdesc', $companyId, $selectedYear);
$realoc = fetchData($pdo, 'rh_realoc', $companyId, $selectedYear);
$capacita = fetchData($pdo, 'rh_capacita', $companyId, $selectedYear);

// Compute Labels (Months) from Contratacoes
$labels = [];
if (count($contratacoes) > 0) {
    foreach ($contratacoes as $c) {
        $labels[] = $c['mes'] . '/' . $c['ano'];
    }
} else {
    // Fallback
    $labels = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
}

// Prepare Data Arrays
$dataContrata = array_column($contratacoes, 'contratacao');

$dataDemissao = [];
foreach ($demissoes as $d) {
    $dataDemissao[] = ($d['demissao_p'] ?? 0) + ($d['demissao_e'] ?? 0);
}

$dataCLT = array_column($efetivo, 'clt');
$dataPJ = array_column($efetivo, 'pj');
$dataCOOP = array_column($efetivo, 'coop');
$dataSCP = array_column($efetivo, 'scp');
$dataEstag = array_column($efetivo, 'estag');

// Calculate total for label or other uses if needed, but chart will use categories
$dataEfetivoTotal = [];
foreach ($efetivo as $e) {
    $dataEfetivoTotal[] = ($e['clt'] + $e['pj'] + $e['coop'] + $e['scp'] + $e['estag']);
}

$dataCusto = array_column($folha, 'custo');
$dataHoras = array_column($horasext, 'he');
$dataFeedback = array_column($feedback, 'feedback');
$dataTempo = array_column($rstempo, 'tempo');

// Calculate Economy (Orcado - Contratado)
$dataEconomia = [];
$dataEconomiaAcum = [];
$currentSum = 0;
foreach ($rsdesc as $r) {
    $val = ($r['orcado'] - $r['contratado']);
    $dataEconomia[] = $val;
    $currentSum += $val;
    $dataEconomiaAcum[] = $currentSum;
}

$dataRealoc = array_column($realoc, 'total');
$dataCapacita = array_column($capacita, 'cumpriu');

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Dashboard RH</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .chart-card {
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid var(--glass-border);
            border-radius: 1rem;
            padding: 1.5rem;
        }

        h3 {
            color: var(--text-color);
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h1>📊 Dashboard RH</h1>
                <div style="display:flex; gap:1rem; align-items:center;">
                    <form method="GET" style="margin:0;">
                        <select name="ano" onchange="this.form.submit()"
                            style="padding:0.5rem; border-radius:0.5rem; background:#1e293b; color:white; border:1px solid #475569;">
                            <?php foreach ($availableYears as $y): ?>
                                <option value="<?php echo $y; ?>" <?php echo $y == $selectedYear ? 'selected' : ''; ?>>
                                    <?php echo $y; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                    <a href="rh_indicators_input.php" class="btn btn-primary">Adicionar Dados</a>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="chart-card">
                    <h3>1. Contratações vs Demissões</h3>
                    <canvas id="chartTurnover"></canvas>
                </div>
                <div class="chart-card">
                    <h3>2. Evolução do Efetivo Total</h3>
                    <canvas id="chartEfetivo"></canvas>
                </div>
                <div class="chart-card">
                    <h3>3. Custo Folha de Pagamento</h3>
                    <canvas id="chartCusto"></canvas>
                </div>
                <div class="chart-card">
                    <h3>4. Horas Extras</h3>
                    <canvas id="chartHE"></canvas>
                </div>
                <div class="chart-card">
                    <h3>5. Humor (Feliz + M.Feliz)</h3>
                    <canvas id="chartHumor"></canvas>
                </div>
                <div class="chart-card">
                    <h3>6. Feedbacks Realizados</h3>
                    <canvas id="chartFeedback"></canvas>
                </div>
                <div class="chart-card">
                    <h3>7. Tempo Médio R&S (Dias)</h3>
                    <canvas id="chartTempo"></canvas>
                </div>
                <div class="chart-card">
                    <h3>8. Vagas Abertas vs Fechadas</h3>
                    <canvas id="chartVagas"></canvas>
                </div>
                <div class="chart-card">
                    <h3>9. Economia Mensal R&S (R$)</h3>
                    <canvas id="chartEconomia"></canvas>
                </div>
                <div class="chart-card">
                    <h3>10. Realocação (Total)</h3>
                    <canvas id="chartRealoc"></canvas>
                </div>
                <div class="chart-card">
                    <h3>11. Capacitação (Cumpriu Meta)</h3>
                    <canvas id="chartCapacita"></canvas>
                </div>
                <div class="chart-card">
                    <h3>12. Economia Acumulada R&S (R$)</h3>
                    <canvas id="chartEconomiaAcum"></canvas>
                </div>
            </div>
        </main>
    </div>

    <script>
        const labels = <?php echo json_encode($labels); ?>;
        const colors = {
            primary: '#3b82f6', danger: '#ef4444', success: '#10b981', purple: '#8b5cf6', orange: '#f97316'
        };

        function createLineChart(id, label, data, color) {
            new Chart(document.getElementById(id), {
                type: 'line',
                data: { labels: labels, datasets: [{ label: label, data: data, borderColor: color, tension: 0.3, fill: true, backgroundColor: color + '20' }] },
                options: { responsive: true, plugins: { legend: { display: false } } }
            });
        }

        // 1. Turnover
        new Chart(document.getElementById('chartTurnover'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Contratações', data: <?php echo json_encode($dataContrata); ?>, borderColor: colors.success },
                    { label: 'Demissões', data: <?php echo json_encode($dataDemissao); ?>, borderColor: colors.danger }
                ]
            }
        });

        // 2. Efetivo Chart (Multi-line)
        new Chart(document.getElementById('chartEfetivo'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    { label: 'CLT', data: <?php echo json_encode($dataCLT); ?>, borderColor: colors.primary, tension: 0.3 },
                    { label: 'PJ', data: <?php echo json_encode($dataPJ); ?>, borderColor: colors.purple, tension: 0.3 },
                    { label: 'COOP', data: <?php echo json_encode($dataCOOP); ?>, borderColor: colors.orange, tension: 0.3 },
                    { label: 'SCP', data: <?php echo json_encode($dataSCP); ?>, borderColor: colors.success, tension: 0.3 },
                    { label: 'Estagiário', data: <?php echo json_encode($dataEstag); ?>, borderColor: '#db2777', tension: 0.3 } // pink-600
                ]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } }
        });

        // 3. Custo
        createLineChart('chartCusto', 'Custo Folha', <?php echo json_encode($dataCusto); ?>, colors.purple);

        // 4. HE
        createLineChart('chartHE', 'Horas Extras', <?php echo json_encode($dataHoras); ?>, colors.orange);

        // 5. Humor
        <?php
        $dataHumorPositive = [];
        foreach ($humor as $h) {
            $dataHumorPositive[] = ($h['mfeliz'] + $h['feliz']);
        }
        ?>
        createLineChart('chartHumor', 'Pessoas Felizes', <?php echo json_encode($dataHumorPositive); ?>, '#ec4899'); // pink

        // 6. Feedback
        createLineChart('chartFeedback', 'Feedbacks', <?php echo json_encode($dataFeedback); ?>, '#06b6d4'); // cyan

        <?php $dataTempoVagas = array_column($rstempo, 'vagas'); ?>
        new Chart(document.getElementById('chartTempo'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Dias Médios', data: <?php echo json_encode($dataTempo); ?>, borderColor: '#eab308', tension: 0.3 },
                    { label: 'Vagas', data: <?php echo json_encode($dataTempoVagas); ?>, borderColor: '#3b82f6', tension: 0.3 } // Blue
                ]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });

        // 8. Vagas
        <?php
        $vagasA = array_column($rsvagas, 'abertas');
        $vagasF = array_column($rsvagas, 'fechadas');
        ?>
        new Chart(document.getElementById('chartVagas'), {
            type: 'bar',
            data: {
                labels: labels, datasets: [
                    { label: 'Abertas', data: <?php echo json_encode($vagasA); ?>, backgroundColor: colors.orange },
                    { label: 'Fechadas', data: <?php echo json_encode($vagasF); ?>, backgroundColor: colors.success }
                ]
            }
        });

        // 9. Economia
        createLineChart('chartEconomia', 'Economia R$', <?php echo json_encode($dataEconomia); ?>, '#84cc16'); // lime

        // 10. Realoc
        createLineChart('chartRealoc', 'Em Realocação', <?php echo json_encode($dataRealoc); ?>, '#6366f1'); // indigo

        // 11. Capacita
        createLineChart('chartCapacita', 'Treinamentos Cumpridos', <?php echo json_encode($dataCapacita); ?>, '#14b8a6'); // teal

        // 12. Economia Acumulada
        createLineChart('chartEconomiaAcum', 'Acumulado R$', <?php echo json_encode($dataEconomiaAcum); ?>, '#22c55e'); // green-500

    </script>

    </script>
</body>

</html>
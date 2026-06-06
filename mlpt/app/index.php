<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkAuth('responsible');

$companyId = $_SESSION['company_id'];

// Get company data (with sector)
$stmt = $pdo->prepare("SELECT * FROM companies WHERE id = ?");
$stmt->execute([$companyId]);
$company = $stmt->fetch();
$mySector = $company['sector'] ?? 'Tecnologia';

// Get assessments
$assessments = $pdo->prepare("SELECT * FROM maturity_assessments WHERE company_id = ? ORDER BY assessment_date DESC LIMIT 2");
$assessments->execute([$companyId]);
$history = $assessments->fetchAll();

$current = $history[0] ?? null;
$previous = $history[1] ?? null;

// Mock Sector Benchmarks (In a real app, query AVG of other companies in sector)
// We will generate pseudo-randomly stable for demo based on sector hash
$baseScore = (crc32($mySector) % 30) + 60; // 60-90 range
$sectorAvg = [
    'overall' => $baseScore,
    'cred' => $baseScore + 2,
    'resp' => $baseScore - 2,
    'imp' => $baseScore - 5,
    'pride' => $baseScore + 5,
    'cam' => $baseScore
];

// Check Alerts
$alerts = [];
if ($current && $previous) {
    if ($current['overall_score'] < ($previous['overall_score'] * 0.9)) {
        $alerts[] = ['type' => 'critical', 'msg' => 'Queda de ' . round($previous['overall_score'] - $current['overall_score']) . ' pontos no índice geral.'];
    }
    if ($current['respect_score'] < 60) {
        $alerts[] = ['type' => 'warning', 'msg' => 'O pilar "Respeito" está em nível crítico.'];
    }
}
if ($current && $current['overall_score'] < $sectorAvg['overall']) {
    $alerts[] = ['type' => 'info', 'msg' => 'Sua maturidade está ' . round($sectorAvg['overall'] - $current['overall_score']) . ' pontos abaixo da média do setor ' . $mySector . '.'];
}

/* Charts Data */
$labels = ['Credibilidade', 'Respeito', 'Imparcialidade', 'Orgulho', 'Camaradagem'];
$myScores = [0, 0, 0, 0, 0];
$sectorScores = array_values(array_slice($sectorAvg, 1));

if ($current) {
    $myScores = [
        $current['credibility_score'],
        $current['respect_score'],
        $current['impartiality_score'],
        $current['pride_score'],
        $current['camaraderie_score']
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Dashboard - MLPT</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
        }

        .chart-card {
            background: var(--card-bg);
            padding: 24px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .welcome-header {
            margin-bottom: 30px;
        }

        .welcome-header h2 {
            font-size: 2rem;
            margin-bottom: 5px;
        }

        /* Alert Styles */
        .alert-box {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-critical {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }

        .alert-warning {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.3);
            color: #fcd34d;
        }

        .alert-info {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #93c5fd;
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <div class="sidebar">
            <div class="logo">MLPT System</div>
            <a href="index.php" class="menu-item active"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
            <a href="pulse.php" class="menu-item"><i class="fa-solid fa-heart-pulse"></i> Pulso & Clima</a>
            <a href="maturity.php" class="menu-item"><i class="fa-solid fa-sliders"></i> Avaliação Maturidade</a>
            <a href="documents.php" class="menu-item"><i class="fa-solid fa-file-contract"></i> Análise Documental</a>
            <a href="trust_index.php" class="menu-item"><i class="fa-solid fa-flask"></i> Simulador Trust Index</a>
            <a href="action_plan.php" class="menu-item"><i class="fa-solid fa-list-check"></i> Planos de Ação</a>
            <a href="../logout.php" class="menu-item"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
        </div>
        <div class="content">
            <div class="welcome-header">
                <h2>Olá, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h2>
                <div style="display:flex; justify-content:space-between;">
                    <p>Maturidade e Clima Organizacional.</p>
                    <span
                        style="background: rgba(255,255,255,0.1); padding: 5px 15px; border-radius: 20px; font-size: 0.8rem;">Setor:
                        <?php echo htmlspecialchars($mySector); ?></span>
                </div>
            </div>

            <!-- Alerts Section -->
            <?php if (!empty($alerts)): ?>
                <div class="alerts-section" style="margin-bottom: 30px;">
                    <h3 style="margin-bottom: 15px; font-size: 1.1rem;"><i class="fa-solid fa-bell"></i> Alertas Preventivos
                    </h3>
                    <?php foreach ($alerts as $alert): ?>
                        <div class="alert-box alert-<?php echo $alert['type']; ?>">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <?php echo $alert['msg']; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!$current): ?>
                <div
                    style="background: rgba(99, 102, 241, 0.1); padding: 20px; border-radius: 12px; border: 1px solid var(--primary); margin-bottom: 30px;">
                    <h3>Dados insuficientes</h3>
                    <p>Realize sua primeira avaliação de maturidade para visualizar os gráficos.</p>
                    <a href="maturity.php" class="btn btn-primary" style="margin-top: 10px;">Iniciar Avaliação</a>
                </div>
            <?php else: ?>
                <div class="dashboard-grid">
                    <div class="chart-card">
                        <h3>Maturidade vs Setor</h3>
                        <canvas id="maturityChart"></canvas>
                    </div>
                    <div class="chart-card">
                        <h3>Benchmarking Setorial</h3>
                        <canvas id="benchmarkChart"></canvas>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('maturityChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: <?php echo json_encode($labels); ?>,
                    datasets: [{
                        label: 'Sua Empresa',
                        data: <?php echo json_encode($myScores); ?>,
                        backgroundColor: 'rgba(99, 102, 241, 0.2)',
                        borderColor: 'rgba(99, 102, 241, 1)',
                        borderWidth: 2
                    },
                    {
                        label: 'Média de <?php echo $mySector; ?>',
                        data: <?php echo json_encode($sectorScores); ?>,
                        backgroundColor: 'rgba(148, 163, 184, 0.1)',
                        borderColor: 'rgba(148, 163, 184, 0.5)',
                        borderWidth: 1,
                        borderDash: [5, 5]
                    }]
                },
                options: {
                    scales: {
                        r: {
                            angleLines: { color: 'rgba(255,255,255,0.1)' },
                            grid: { color: 'rgba(255,255,255,0.1)' },
                            pointLabels: { color: '#94a3b8' },
                            ticks: { display: false, max: 100 }
                        }
                    },
                    plugins: {
                        legend: { labels: { color: '#f8fafc' } }
                    }
                }
            });
        }

        const ctx2 = document.getElementById('benchmarkChart');
        if (ctx2) {
            new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: ['Geral', 'Cred.', 'Resp.', 'Imp.', 'Org.', 'Cam.'],
                    datasets: [{
                        label: 'Sua Empresa',
                        data: [<?php echo $current['overall_score']; ?>, ...<?php echo json_encode($myScores); ?>],
                        backgroundColor: '#6366f1'
                    },
                    {
                        label: 'Setor (<?php echo $mySector; ?>)',
                        data: [<?php echo $sectorAvg['overall']; ?>, ...<?php echo json_encode($sectorScores); ?>],
                        backgroundColor: '#475569'
                    }]
                },
                options: {
                    scales: {
                        y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } },
                        x: { grid: { display: false }, ticks: { color: '#94a3b8' } }
                    },
                    plugins: { legend: { labels: { color: '#f8fafc' } } }
                }
            });
        }
    </script>
</body>

</html>
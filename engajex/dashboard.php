<?php
require_once 'config.php';
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}
checkAccess();

$userId    = $_SESSION['user_id'];
$companyId = $_SESSION['company_id'] ?? 1;

// Humor de hoje
$todayMood = null;
try {
    $s = $pdo->prepare("SELECT mood_level FROM mood_tracker WHERE user_id = ? AND DATE(created_at) = CURDATE() ORDER BY created_at DESC LIMIT 1");
    $s->execute([$userId]);
    $todayMood = $s->fetchColumn();
} catch (PDOException $e) {}

// Feedbacks recebidos não lidos (todos recebidos como fallback)
$feedbackCount = 0;
try {
    $s = $pdo->prepare("SELECT COUNT(*) FROM feedbacks WHERE receiver_id = ?");
    $s->execute([$userId]);
    $feedbackCount = (int) $s->fetchColumn();
} catch (PDOException $e) {}

// Pontos de gamificação do usuário
$myPoints = 0;
try {
    $s = $pdo->prepare("SELECT COALESCE(SUM(points),0) FROM gamification_ledger WHERE user_id = ? AND company_id = ?");
    $s->execute([$userId, $companyId]);
    $myPoints = (int) $s->fetchColumn();
} catch (PDOException $e) {}

// Tendência de humor da empresa (7 dias)
$moodTrendLabels = [];
$moodTrendData   = [];
try {
    $s = $pdo->prepare("
        SELECT DATE(created_at) AS day, ROUND(AVG(mood_level),2) AS avg_mood
        FROM mood_tracker
        WHERE company_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        GROUP BY DATE(created_at)
        ORDER BY day ASC
    ");
    $s->execute([$companyId]);
    $rows = $s->fetchAll();

    // Garantir todos os 7 dias mesmo sem dados
    $allDays = [];
    for ($i = 6; $i >= 0; $i--) {
        $allDays[date('Y-m-d', strtotime("-{$i} days"))] = null;
    }
    foreach ($rows as $r) {
        $allDays[$r['day']] = (float) $r['avg_mood'];
    }
    foreach ($allDays as $date => $avg) {
        $moodTrendLabels[] = date('d/m', strtotime($date));
        $moodTrendData[]   = $avg;
    }
} catch (PDOException $e) {}

// Mapa de emojis
$moodEmojis = [1 => '😡', 2 => '😞', 3 => '😐', 4 => '🙂', 5 => '😁'];
$moodLabels = [1 => 'Péssimo', 2 => 'Ruim', 3 => 'Neutro', 4 => 'Bem', 5 => 'Ótimo'];
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Dashboard - TestProf Engaja</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script src="assets/js/charts-config.js"></script>
    <style>
        /* Styles moved to assets/css/style.css */
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <h1 style="margin-bottom: 2rem;">Bem-vindo de volta!</h1>

            <!-- ── KPI Cards ── -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.25rem;margin-bottom:2rem;">

                <div class="card-stat">
                    <div class="card-stat__icon card-stat__icon--primary">
                        <i class="fa-solid fa-face-smile"></i>
                    </div>
                    <div class="card-stat__body">
                        <span class="card-stat__label">Meu Humor Hoje</span>
                        <span class="card-stat__value" style="font-size:1.75rem;">
                            <?php echo $todayMood ? $moodEmojis[$todayMood] . ' ' . $moodLabels[$todayMood] : '— '; ?>
                        </span>
                        <a href="modules/mood.php" style="font-size:.8125rem;color:var(--indigo-400);text-decoration:none;">
                            <?php echo $todayMood ? 'Atualizar' : 'Registrar agora'; ?> →
                        </a>
                    </div>
                </div>

                <div class="card-stat">
                    <div class="card-stat__icon card-stat__icon--accent">
                        <i class="fa-solid fa-comments"></i>
                    </div>
                    <div class="card-stat__body">
                        <span class="card-stat__label">Feedbacks Recebidos</span>
                        <span class="card-stat__value"><?php echo $feedbackCount; ?></span>
                        <a href="modules/feedback.php" style="font-size:.8125rem;color:var(--indigo-400);text-decoration:none;">Ver feedbacks →</a>
                    </div>
                </div>

                <div class="card-stat">
                    <div class="card-stat__icon card-stat__icon--warning">
                        <i class="fa-solid fa-trophy"></i>
                    </div>
                    <div class="card-stat__body">
                        <span class="card-stat__label">Meus Pontos</span>
                        <span class="card-stat__value"><?php echo number_format($myPoints); ?></span>
                        <a href="modules/gamification.php" style="font-size:.8125rem;color:var(--indigo-400);text-decoration:none;">Ver ranking →</a>
                    </div>
                </div>

            </div>

            <!-- ── Gráfico: Humor da Empresa (7 dias) ── -->
            <?php if (!empty($moodTrendData) && count(array_filter($moodTrendData, fn($v) => $v !== null)) > 0): ?>
            <div class="card" style="margin-bottom:2rem;padding:1.5rem;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
                    <div>
                        <h3 style="font-size:1rem;font-weight:600;color:#e2e8f0;margin:0;">Humor da Empresa</h3>
                        <p style="font-size:.8125rem;color:#64748b;margin:.25rem 0 0;">Média diária dos últimos 7 dias</p>
                    </div>
                    <a href="modules/mood.php" style="font-size:.8125rem;color:var(--indigo-400);text-decoration:none;">Ver detalhes →</a>
                </div>
                <canvas id="companyMoodChart" height="80"></canvas>
            </div>
            <?php endif; ?>

            <h2 style="margin-bottom: 1.5rem;">Destaques & Dinâmicas</h2>
            <div class="features">
                <div class="card" style="background: linear-gradient(135deg, #4f46e5 0%, #312e81 100%);">
                    <h3>🎭 Conexões Virtuais</h3>
                    <p style="color: #e0e7ff; margin-bottom: 1rem;">O sistema lúdico para apresentações da equipe.</p>
                    <a href="modules/connections.php" class="btn btn-primary"
                        style="background: white; color: #4f46e5;">Acessar</a>
                </div>
                <div class="card" style="background: linear-gradient(135deg, #ec4899 0%, #831843 100%);">
                    <h3>🎱 Bingo Corporativo</h3>
                    <p style="color: #fce7f3; margin-bottom: 1rem;">O jogo que todo mundo conhece das calls.</p>
                    <a href="modules/bingo.php" class="btn btn-primary"
                        style="background: white; color: #ec4899;">Jogar</a>
                </div>
                <div class="card" style="background: linear-gradient(135deg, #10b981 0%, #064e3b 100%);">
                    <h3>📜 História da Empresa</h3>
                    <p style="color: #d1fae5; margin-bottom: 1rem;">Organize os eventos na ordem cronológica.</p>
                    <a href="modules/history.php" class="btn btn-primary"
                        style="background: white; color: #10b981;">Conhecer</a>
                </div>
            </div>

        </main>
    </div>

    <?php if (!empty($moodTrendData) && count(array_filter($moodTrendData, fn($v) => $v !== null)) > 0): ?>
    <script>
    (function () {
        var labels = <?php echo json_encode($moodTrendLabels); ?>;
        var data   = <?php echo json_encode($moodTrendData); ?>;

        var ctx = document.getElementById('companyMoodChart');
        if (!ctx) return;

        var gradFill = ctx.getContext('2d').createLinearGradient(0, 0, 0, ctx.offsetHeight || 160);
        gradFill.addColorStop(0, 'rgba(99,102,241,0.22)');
        gradFill.addColorStop(1, 'rgba(99,102,241,0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Humor médio',
                    data: data,
                    borderColor: '#6366f1',
                    backgroundColor: gradFill,
                    fill: true,
                    spanGaps: true,
                    pointBackgroundColor: '#6366f1',
                    pointBorderColor: '#0f172a',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        min: 1, max: 5,
                        ticks: {
                            stepSize: 1,
                            callback: function (v) {
                                return ['','😡','😞','😐','🙂','😁'][v] || '';
                            }
                        }
                    },
                    x: { grid: { display: false } }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                var v = ctx.parsed.y;
                                if (v === null) return ' Sem dados';
                                var e = ['','😡','😞','😐','🙂','😁'][Math.round(v)] || '';
                                return ' Média: ' + v.toFixed(1) + ' ' + e;
                            }
                        }
                    }
                }
            }
        });
    })();
    </script>
    <?php endif; ?>
</body>

</html>
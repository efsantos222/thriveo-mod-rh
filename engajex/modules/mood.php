<?php
// Display errors for debugging (Keep enabled until fully fixed)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config.php';
require_once '../includes/flash_toast.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$message = '';
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'] ?? 'employee';
$companyId = $_SESSION['company_id'] ?? null;

// Determine if User is a Manager
$isManager = ($userRole === 'manager' || $userRole === 'responsible' || $userRole === 'admin');

// Handle Mood Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'log_mood') {
    $mood = (int) $_POST['mood'];
    $note = $_POST['note'] ?? '';

    if ($mood >= 1 && $mood <= 5) {
        try {
            // Note: Using 'mood_level' column now
            // Saving to both columns for compatibility
            $stmt = $pdo->prepare("INSERT INTO mood_tracker (company_id, user_id, mood_level, mood_score, note) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$companyId, $userId, $mood, $mood, $note]);
            $message = "Humor registrado com sucesso!";
        } catch (PDOException $e) {
            $message = "Erro ao salvar: " . $e->getMessage();
        }
    }
}

// Fetch Manager's Team Moods
$teamMoods = [];
$avgMood = 0;

if ($isManager) {
    try {
        // Query to get recent team moods
        // Note: 'm.mood_level' is the column name we standardized on
        $sql = "
            SELECT m.mood_level, m.note, m.created_at, u.name as user_name 
            FROM mood_tracker m 
            JOIN users u ON m.user_id = u.id 
            WHERE u.manager_id = ? AND m.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
            ORDER BY m.created_at DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        $teamMoods = $stmt->fetchAll();

        // Calculate Average
        if (count($teamMoods) > 0) {
            $sum = 0;
            foreach ($teamMoods as $t) {
                // Ensure we handle case where mood_level might be null (unlikely but safe)
                $sum += (int) $t['mood_level'];
            }
            $avgMood = $sum / count($teamMoods);
        }
    } catch (PDOException $e) {
        $message .= " (Erro ao carregar time: " . $e->getMessage() . ")";
    }
}

// ── Dados para Chart.js (apenas gestores) ────────────────
$chartDistrib = [0, 0, 0, 0, 0]; // índices 0-4 = mood 1-5
$trendLabels  = [];
$trendData    = [];

if ($isManager && !empty($teamMoods)) {
    // Distribuição de humores
    foreach ($teamMoods as $t) {
        $lvl = (int)$t['mood_level'];
        if ($lvl >= 1 && $lvl <= 5) $chartDistrib[$lvl - 1]++;
    }

    // Tendência dos últimos 7 dias (média diária)
    $daily = [];
    for ($i = 6; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-{$i} days"));
        $daily[$d] = ['sum' => 0, 'cnt' => 0];
    }
    foreach ($teamMoods as $t) {
        $d = date('Y-m-d', strtotime($t['created_at']));
        if (isset($daily[$d])) {
            $daily[$d]['sum'] += (int)$t['mood_level'];
            $daily[$d]['cnt']++;
        }
    }
    foreach ($daily as $date => $v) {
        $trendLabels[] = date('d/m', strtotime($date));
        $trendData[]   = $v['cnt'] > 0 ? round($v['sum'] / $v['cnt'], 2) : null;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Registro de Humor - TestProf Engaja</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script src="../assets/js/charts-config.js"></script>
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

        .mood-container {
            max-width: 800px;
            margin: 0 auto;
            display: grid;
            grid-template-columns:
                <?php echo $isManager ? '1fr 1fr' : '1fr'; ?>
            ;
            gap: 2rem;
        }

        .mood-card {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
        }

        .mood-options {
            display: flex;
            justify-content: space-between;
            margin: 2rem 0;
        }

        .mood-option {
            font-size: 2.5rem;
            cursor: pointer;
            transition: transform 0.2s;
            opacity: 0.5;
            background: none;
            border: none;
        }

        .mood-option:hover,
        .mood-option.selected {
            transform: scale(1.2);
            opacity: 1;
        }

        .team-list {
            max-height: 400px;
            overflow-y: auto;
            text-align: left;
        }

        .team-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem;
            border-bottom: 1px solid var(--glass-border);
        }

        .mood-badge {
            font-size: 1.2rem;
        }

        .mood-1 {
            color: #ef4444;
        }

        /* Angry */
        .mood-2 {
            color: #f97316;
        }

        /* Sad */
        .mood-3 {
            color: #eab308;
        }

        /* Neutral */
        .mood-4 {
            color: #84cc16;
        }

        /* Good */
        .mood-5 {
            color: #22c55e;
        }

        /* Happy */

        /* Mobile responsive */
        @media (max-width: 768px) {
            .mood-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include '../includes/sidebar.php'; ?>

        <main class="main-content">
            <h1 style="margin-bottom: 2rem; text-align: center;">Como você está se sentindo hoje?</h1>

            <?php if ($message): flashToast($message, 'success'); endif; ?>

            <?php if ($isManager && !empty($teamMoods)): ?>
            <!-- ── Gráficos do gestor ── -->
            <div style="display:grid;grid-template-columns:1fr 2fr;gap:1.5rem;margin-bottom:2rem;max-width:900px;margin-left:auto;margin-right:auto;">

                <!-- Distribuição de humores (Doughnut) -->
                <div class="card" style="padding:1.5rem;display:flex;flex-direction:column;align-items:center;">
                    <h4 style="font-size:.875rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:1.25rem;">Distribuição</h4>
                    <div style="position:relative;width:180px;height:180px;">
                        <canvas id="moodDistribChart"></canvas>
                        <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none;">
                            <span style="font-size:1.75rem;line-height:1;"><?php
                                $avg = $avgMood;
                                if ($avg < 2) echo '😡';
                                elseif ($avg < 3) echo '😞';
                                elseif ($avg < 4) echo '😐';
                                elseif ($avg < 5) echo '🙂';
                                else echo '😁';
                            ?></span>
                            <span style="font-size:.8rem;color:#94a3b8;margin-top:.25rem;"><?php echo number_format($avgMood,1); ?>/5</span>
                        </div>
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:.5rem;justify-content:center;margin-top:1rem;">
                        <?php
                        $moodEmojis = ['😡','😞','😐','🙂','😁'];
                        foreach ($chartDistrib as $i => $cnt):
                            if ($cnt > 0):
                        ?>
                        <span style="font-size:.8rem;color:#94a3b8;"><?php echo $moodEmojis[$i].' '.$cnt; ?></span>
                        <?php endif; endforeach; ?>
                    </div>
                </div>

                <!-- Tendência 7 dias (Line) -->
                <div class="card" style="padding:1.5rem;">
                    <h4 style="font-size:.875rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:1.25rem;">Tendência — Últimos 7 dias</h4>
                    <canvas id="moodTrendChart" height="140"></canvas>
                </div>

            </div>
            <?php endif; ?>

            <div class="mood-container">
                <!-- User Mood Input -->
                <div class="mood-card">
                    <h3>Registrar meu humor</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="log_mood">
                        <input type="hidden" id="selected-mood" name="mood" required>

                        <div class="mood-options">
                            <button type="button" class="mood-option" onclick="selectMood(1, this)">😡</button>
                            <button type="button" class="mood-option" onclick="selectMood(2, this)">😞</button>
                            <button type="button" class="mood-option" onclick="selectMood(3, this)">😐</button>
                            <button type="button" class="mood-option" onclick="selectMood(4, this)">🙂</button>
                            <button type="button" class="mood-option" onclick="selectMood(5, this)">😁</button>
                        </div>

                        <textarea name="note" class="form-control" rows="2"
                            placeholder="Quer adicionar uma nota? (Opcional)" style="margin-bottom: 1rem;"></textarea>

                        <button type="submit" class="btn btn-primary" style="width: 100%;">Salvar</button>
                    </form>
                </div>

                <!-- Manager View -->
                <?php if ($isManager): ?>
                    <div class="mood-card">
                        <h3 style="margin-bottom: 1.5rem;">Time (7 dias)</h3>

                        <?php if ($avgMood > 0): ?>
                            <div style="text-align: center; margin-bottom: 1.5rem;">
                                <span style="font-size: 3rem;">
                                    <?php
                                    if ($avgMood < 2)
                                        echo '😡';
                                    elseif ($avgMood < 3)
                                        echo '😞';
                                    elseif ($avgMood < 4)
                                        echo '😐';
                                    elseif ($avgMood < 5)
                                        echo '🙂';
                                    else
                                        echo '😁';
                                    ?>
                                </span>
                                <p style="color: var(--text-muted);">Média: <?php echo number_format($avgMood, 1); ?>/5</p>
                            </div>
                        <?php else: ?>
                            <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Sem dados recentes.</p>
                        <?php endif; ?>

                        <div class="team-list">
                            <?php if (empty($teamMoods)): ?>
                                <p style="color: var(--text-muted); font-size: 0.9rem;">Nenhum registro da equipe.</p>
                            <?php else: ?>
                                <?php foreach ($teamMoods as $log): ?>
                                    <div class="team-item">
                                        <div>
                                            <div style="font-weight: 500; color: #fff;">
                                                <?php echo htmlspecialchars($log['user_name']); ?></div>
                                            <div style="font-size: 0.8rem; color: var(--text-muted);">
                                                <?php echo date('d/m H:i', strtotime($log['created_at'])); ?></div>
                                            <?php if ($log['note']): ?>
                                                <div style="font-size: 0.8rem; font-style: italic; color: var(--text-light);">
                                                    "<?php echo htmlspecialchars($log['note']); ?>"</div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mood-badge mood-<?php echo $log['mood_level']; ?>">
                                            <?php
                                            $emojis = [1 => '😡', 2 => '😞', 3 => '😐', 4 => '🙂', 5 => '😁'];
                                            echo $emojis[$log['mood_level']] ?? '-';
                                            ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </main>
    </div>

    <script>
        function selectMood(level, btn) {
            document.getElementById('selected-mood').value = level;
            document.querySelectorAll('.mood-option').forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');
        }
    </script>

    <?php if ($isManager && !empty($teamMoods)): ?>
    <script>
    (function () {
        var distrib = <?php echo json_encode(array_values($chartDistrib)); ?>;
        var labels  = <?php echo json_encode($trendLabels); ?>;
        var trend   = <?php echo json_encode($trendData); ?>;
        var C = window.EC ? window.EC.color : {};

        // ── Doughnut: distribuição ───────────────────────────
        var ctxD = document.getElementById('moodDistribChart');
        if (ctxD) {
            new Chart(ctxD, {
                type: 'doughnut',
                data: {
                    labels: ['😡 Mal','😞 Triste','😐 Neutro','🙂 Bem','😁 Ótimo'],
                    datasets: [{
                        data: distrib,
                        backgroundColor: C.moodBg || ['rgba(239,68,68,.5)','rgba(249,115,22,.5)','rgba(234,179,8,.5)','rgba(132,204,22,.5)','rgba(34,197,94,.5)'],
                        borderColor:     C.mood    || ['#ef4444','#f97316','#eab308','#84cc16','#22c55e'],
                        borderWidth: 2,
                        hoverOffset: 8,
                    }]
                },
                options: {
                    cutout: '68%',
                    plugins: { legend: { display: false } },
                    animation: { animateRotate: true, duration: 800 }
                }
            });
        }

        // ── Line: tendência 7 dias ───────────────────────────
        var ctxL = document.getElementById('moodTrendChart');
        if (ctxL) {
            var gradFill = EC && EC.linearGradient
                ? EC.linearGradient(ctxL.getContext('2d'), '#6366f1', 0.25, 0)
                : 'rgba(99,102,241,0.15)';

            new Chart(ctxL, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Humor médio',
                        data: trend,
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
                                callback: function(v) {
                                    return ['','😡','😞','😐','🙂','😁'][v] || v;
                                }
                            }
                        },
                        x: { grid: { display: false } }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    var v = ctx.parsed.y;
                                    var e = ['','😡','😞','😐','🙂','😁'][Math.round(v)] || '';
                                    return ' Média: ' + v.toFixed(1) + ' ' + e;
                                }
                            }
                        }
                    }
                }
            });
        }
    })();
    </script>
    <?php endif; ?>
</body>

</html>
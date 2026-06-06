<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$companyId = $_SESSION['company_id'] ?? 1;

$userRole = $_SESSION['role'];
$mode = $_GET['mode'] ?? 'ranking'; // ranking, rewards, history
$isAdmin = ($userRole === 'admin' || $userRole === 'responsible' || $userRole === 'manager');

// --- ACTIONS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin) {
    if ($_POST['action'] === 'save_goal') {
        $month = $_POST['month'];
        $year = $_POST['year'];
        $target = $_POST['target_points'];
        $reward = $_POST['reward_description'];
        $challenges = $_POST['challenges_description']; // New Field
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $sql = "INSERT INTO gamification_goals (company_id, month, year, target_points, reward_description, challenges_description, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                target_points = VALUES(target_points), 
                reward_description = VALUES(reward_description), 
                challenges_description = VALUES(challenges_description),
                is_active = VALUES(is_active)";

        $pdo->prepare($sql)->execute([$companyId, $month, $year, $target, $reward, $challenges, $isActive]);
    }
}

// --- HELPERS ---
function getMonthName($m)
{
    $months = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
    return $months[$m];
}

$currentMonth = date('n');
$currentYear = date('Y');
$selectedYear = isset($_GET['year']) ? (int) $_GET['year'] : $currentYear;

// Ensure at least one goal exists for current month
$stmt = $pdo->prepare("SELECT count(*) FROM gamification_goals WHERE company_id = ? AND month = ? AND year = ?");
$stmt->execute([$companyId, $currentMonth, $currentYear]);
if ($stmt->fetchColumn() == 0) {
    $pdo->prepare("INSERT INTO gamification_goals (company_id, month, year, target_points, reward_description) VALUES (?, ?, ?, 5000, 'Um prêmio surpresa!')")->execute([$companyId, $currentMonth, $currentYear]);
}

// Fetch Goals for Selected Year
$goals = [];
$stmt = $pdo->prepare("SELECT * FROM gamification_goals WHERE company_id = ? AND year = ? ORDER BY month ASC");
$stmt->execute([$companyId, $selectedYear]);
while ($row = $stmt->fetch()) {
    $goals[$row['month']] = $row;
}
$currentGoal = $goals[$currentMonth] ?? ['target_points' => 5000];

// Pontos do usuário atual no mês corrente
$myPoints = 0;
try {
    $stmtMy = $pdo->prepare("SELECT COALESCE(SUM(points),0) FROM gamification_ledger WHERE user_id = ? AND company_id = ?");
    $stmtMy->execute([$userId, $companyId]);
    $myPoints = (int) $stmtMy->fetchColumn();
} catch (PDOException $e) { /* tabela pode não existir ainda */ }

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Gamificação - Thriveo Engajex</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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

        .tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--glass-border);
            padding-bottom: 2px;
        }

        .tab-btn {
            background: none;
            border: none;
            padding: 0.75rem 1.5rem;
            color: var(--text-muted);
            font-weight: 600;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.3s;
            font-size: 1rem;
            text-decoration: none;
        }

        .tab-btn:hover {
            color: white;
        }

        .tab-btn.active {
            color: #f97316;
            border-bottom-color: #f97316;
        }

        /* Rewards Grid */
        .rewards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        .month-card {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 1rem;
            overflow: hidden;
        }

        .month-header {
            background: rgba(249, 115, 22, 0.1);
            padding: 0.75rem;
            font-weight: 700;
            color: #f97316;
            text-align: center;
            border-bottom: 1px solid var(--glass-border);
        }

        .month-body {
            padding: 1.25rem;
        }

        /* Ranking */
        .ranking-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .rank-item {
            display: flex;
            align-items: center;
            background: var(--card-bg);
            padding: 1.5rem;
            border-radius: 1rem;
            border: 1px solid var(--glass-border);
            transition: transform 0.2s;
        }

        .rank-item:hover {
            transform: translateX(5px);
            border-color: rgba(249, 115, 22, 0.3);
        }

        .rank-number {
            font-size: 1.5rem;
            font-weight: 800;
            width: 50px;
            color: var(--text-muted);
        }

        .rank-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #334155;
            margin-right: 1rem;
            display: grid;
            place-items: center;
            font-weight: 700;
            color: white;
            border: 2px solid var(--glass-border);
        }

        .rank-info {
            flex: 1;
        }

        .rank-name {
            font-weight: 700;
            font-size: 1.1rem;
        }

        .rank-dept {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .points-val {
            font-size: 1.5rem;
            font-weight: 800;
            color: #f97316;
        }

        .progress-container {
            width: 100%;
            height: 6px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
            margin-top: 0.5rem;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #f97316, #fbbf24);
            border-radius: 3px;
        }

        .year-selector {
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .year-link {
            padding: 0.5rem 1rem;
            border: 1px solid var(--glass-border);
            border-radius: 0.5rem;
            text-decoration: none;
            color: var(--text-muted);
        }

        .year-link.active {
            background: #f97316;
            color: white;
            border-color: #f97316;
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include '../includes/sidebar.php'; ?>

        <main class="main-content">
            <h1 style="margin-bottom: 1rem;">Gamificação & Recompensas</h1>

            <div class="tabs">
                <a href="?mode=ranking" class="tab-btn <?php echo $mode === 'ranking' ? 'active' : ''; ?>">🏆
                    Ranking</a>
                <a href="?mode=rewards" class="tab-btn <?php echo $mode === 'rewards' ? 'active' : ''; ?>">🎯
                    Configurar</a>
                <a href="?mode=history" class="tab-btn <?php echo $mode === 'history' ? 'active' : ''; ?>">📜
                    Histórico</a>
            </div>

            <!-- VIEW: RANKING -->
            <?php if ($mode === 'ranking'): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h2 style="font-size: 1.2rem;">Ranking de <?php echo getMonthName(date('n')); ?></h2>
                    <div style="font-weight: 700; color: #f97316;">Meta:
                        <?php echo number_format($currentGoal['target_points'] ?? 5000); ?>
                    </div>
                </div>

                <!-- Desafios do Mês Atual (Visible to all) -->
                <?php if (!empty($currentGoal['challenges_description'])): ?>
                    <div
                        style="background: rgba(249, 115, 22, 0.1); border: 1px solid rgba(249, 115, 22, 0.3); padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem;">
                        <h4 style="color: #f97316; margin-bottom: 0.5rem;">🔥 Desafios do Mês</h4>
                        <p style="white-space: pre-line; margin: 0; color: #cbd5e1;">
                            <?php echo htmlspecialchars($currentGoal['challenges_description']); ?>
                        </p>
                        <?php if ($currentGoal['reward_description']): ?>
                            <p style="margin-top: 0.5rem; font-size: 0.9rem; color: #fbbf24;">🎁 Prêmio:
                                <?php echo htmlspecialchars($currentGoal['reward_description']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php
                // Fetch Ranking
                $sql = "SELECT u.name, u.area, SUM(l.points) as total FROM gamification_ledger l JOIN users u ON l.user_id = u.id WHERE l.company_id = ? GROUP BY l.user_id ORDER BY total DESC";
                $ranking = $pdo->prepare($sql);
                $ranking->execute([$companyId]);
                $rankingData = $ranking->fetchAll();
                $target = (int)($currentGoal['target_points'] ?? 5000);
                ?>

                <!-- ── Gráficos de ranking ── -->
                <?php if (!empty($rankingData)): ?>
                <div style="display:grid;grid-template-columns:200px 1fr;gap:1.5rem;margin-bottom:2rem;align-items:center;">

                    <!-- Meu progresso (Doughnut) -->
                    <div class="card" style="padding:1.25rem;text-align:center;">
                        <p style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin-bottom:1rem;">Meu Progresso</p>
                        <div style="position:relative;width:140px;height:140px;margin:0 auto;">
                            <canvas id="myProgressChart"></canvas>
                            <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none;">
                                <span style="font-size:1.25rem;font-weight:800;color:#f97316;"><?php echo number_format($myPoints); ?></span>
                                <span style="font-size:.7rem;color:#64748b;">/ <?php echo number_format($target); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Ranking em barras -->
                    <div class="card" style="padding:1.25rem;">
                        <p style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin-bottom:1rem;">Top <?php echo min(8, count($rankingData)); ?> — <?php echo getMonthName($currentMonth); ?></p>
                        <canvas id="rankingBarChart" height="<?php echo min(8, count($rankingData)) * 36; ?>"></canvas>
                    </div>

                </div>
                <?php endif; ?>

                <div class="ranking-list">
                    <?php $rank = 1; ?>
                    <?php foreach ($rankingData as $r):
                        $pct = min(100, ($r['total'] / ($currentGoal['target_points'] ?? 5000)) * 100); ?>
                        <div class="rank-item">
                            <div class="rank-number">#<?php echo $rank++; ?></div>
                            <div class="rank-avatar"><?php echo strtoupper(substr($r['name'], 0, 2)); ?></div>
                            <div class="rank-info">
                                <div class="rank-name"><?php echo htmlspecialchars($r['name']); ?></div>
                                <div class="rank-dept"><?php echo htmlspecialchars($r['area'] ?? '-'); ?></div>
                                <div class="progress-container">
                                    <div class="progress-bar" style="width: <?php echo $pct; ?>%;"></div>
                                </div>
                            </div>
                            <div class="points-val"><?php echo number_format($r['total']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- VIEW: REWARDS CONFIG -->
            <?php if ($mode === 'rewards'): ?>
                <!-- Year Selector -->
                <div class="year-selector">
                    <span style="font-weight: 600;">Ano de referência:</span>
                    <?php for ($y = $currentYear - 1; $y <= $currentYear + 2; $y++): ?>
                        <a href="?mode=rewards&year=<?php echo $y; ?>"
                            class="year-link <?php echo $y == $selectedYear ? 'active' : ''; ?>"><?php echo $y; ?></a>
                    <?php endfor; ?>
                </div>

                <div class="rewards-grid">
                    <?php
                    // Render all 12 months for selected year
                    for ($m = 1; $m <= 12; $m++) {
                        $g = $goals[$m] ?? ['target_points' => 5000, 'reward_description' => '', 'challenges_description' => '', 'is_active' => 1];
                        ?>
                        <div class="month-card">
                            <div class="month-header"><?php echo getMonthName($m); ?></div>
                            <div class="month-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="save_goal">
                                    <input type="hidden" name="month" value="<?php echo $m; ?>">
                                    <input type="hidden" name="year" value="<?php echo $selectedYear; ?>">

                                    <!-- Meta -->
                                    <div class="form-group" style="margin-bottom: 0.75rem;">
                                        <label class="form-label" style="font-size: 0.8rem;">Meta (Pontos)</label>
                                        <input type="number" name="target_points" value="<?php echo $g['target_points']; ?>"
                                            class="form-control" style="padding: 0.5rem;">
                                    </div>

                                    <!-- Recompensa -->
                                    <div class="form-group" style="margin-bottom: 0.75rem;">
                                        <label class="form-label" style="font-size: 0.8rem;">Recompensa</label>
                                        <input type="text" name="reward_description"
                                            value="<?php echo htmlspecialchars($g['reward_description'] ?? ''); ?>"
                                            class="form-control" placeholder="Prêmio..." style="padding: 0.5rem;">
                                    </div>

                                    <!-- Desafios (Novo Campo) -->
                                    <div class="form-group" style="margin-bottom: 0.75rem;">
                                        <label class="form-label" style="font-size: 0.8rem;">Desafios do Mês</label>
                                        <textarea name="challenges_description" class="form-control" rows="3"
                                            placeholder="- Feedback (+100)&#10;- Meta X (+500)"
                                            style="font-size: 0.85rem; padding: 0.5rem;"><?php echo htmlspecialchars($g['challenges_description'] ?? ''); ?></textarea>
                                    </div>

                                    <div style="text-align: right;">
                                        <button class="btn btn-primary"
                                            style="padding: 0.4rem 1rem; font-size: 0.85rem;">Salvar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php endif; ?>

            <!-- HISTORY (Placeholder) -->
            <?php if ($mode === 'history'): ?>
                <div
                    style="background: var(--card-bg); padding: 2rem; border-radius: 1rem; text-align: center; color: var(--text-muted);">
                    Histórico em desenvolvimento (baseado em dados reais futuros).
                </div>
            <?php endif; ?>

        </main>
    </div>
    <script>
    (function () {
        var rankingData = <?php echo json_encode(array_map(function($r){ return ['name' => $r['name'], 'total' => (int)$r['total']]; }, array_slice($rankingData ?? [], 0, 8))); ?>;
        var myPts   = <?php echo $myPoints; ?>;
        var target  = <?php echo $target ?? 5000; ?>;

        // ── Doughnut: meu progresso ──────────────────────────
        var ctxP = document.getElementById('myProgressChart');
        if (ctxP && myPts >= 0) {
            var done = Math.min(myPts, target);
            new Chart(ctxP, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [done, Math.max(0, target - done)],
                        backgroundColor: ['#f97316', 'rgba(255,255,255,0.06)'],
                        borderColor:     ['#f97316', 'rgba(255,255,255,0.04)'],
                        borderWidth: 2,
                    }]
                },
                options: {
                    cutout: '72%',
                    plugins: { legend: { display: false }, tooltip: { enabled: false } },
                    animation: { animateRotate: true, duration: 900 }
                }
            });
        }

        // ── Horizontal bar: ranking ──────────────────────────
        var ctxR = document.getElementById('rankingBarChart');
        if (ctxR && rankingData.length) {
            var names  = rankingData.map(function(r){ return r.name.split(' ')[0]; });
            var totals = rankingData.map(function(r){ return r.total; });
            var bgColors = totals.map(function(_,i){
                return i === 0 ? '#f97316' : i === 1 ? '#f59e0b' : 'rgba(99,102,241,0.7)';
            });

            new Chart(ctxR, {
                type: 'bar',
                data: {
                    labels: names,
                    datasets: [{
                        label: 'Pontos',
                        data: totals,
                        backgroundColor: bgColors,
                        borderWidth: 0,
                        barThickness: 22,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            beginAtZero: true,
                            max: Math.max(target, Math.max.apply(null, totals) * 1.1),
                            ticks: { callback: function(v){ return v >= 1000 ? (v/1000).toFixed(1)+'k' : v; } }
                        },
                        y: { grid: { display: false } }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(ctx){ return ' ' + ctx.parsed.x.toLocaleString('pt-BR') + ' pts'; }
                            }
                        }
                    }
                }
            });
        }
    })();
    </script>
</body>

</html>
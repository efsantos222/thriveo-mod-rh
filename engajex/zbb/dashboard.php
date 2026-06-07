<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../config.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

if (!in_array($_SESSION['role'], ['responsible', 'company_admin', 'manager', 'admin'])) {
    die("Acesso restrito: Apenas Responsáveis, Admins e Gerentes podem acessar este módulo (ZBB).");
}

$page = 'dashboard_zbb'; // Custom active state if sidebar supports it

// --- Dashboard Logic ---
$companyId = $_SESSION['company_id'] ?? 1;
$currentYear = date('Y');
$currentMonth = date('m');

// ... (Rest of logic remains, using $pdo from config.php) ...

// 1. KPIs
// Total Budgeted vs Realized (Current Year)
$sqlKPI = "SELECT 
    SUM(amount_budgeted) as total_ref, 
    SUM(amount_realized) as total_realized,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count
    FROM budgets 
    WHERE company_id = ? AND fiscal_year = ?";
$stmtKPI = $pdo->prepare($sqlKPI);
$stmtKPI->execute([$companyId, $currentYear]);
$kpiData = $stmtKPI->fetch(PDO::FETCH_ASSOC);

$totalRef = $kpiData['total_ref'] ?? 0;
$totalReal = $kpiData['total_realized'] ?? 0;
$pendingCount = $kpiData['pending_count'] ?? 0;

// Adherence: (Total Realized / Total Ref) * 100 seems wrong if lower is better for budget? 
// Usually Adherence = (Realized / Budget). If Realized < Budget, good. 
// Let's use % of Budget Used.
$percentUsed = ($totalRef > 0) ? ($totalReal / $totalRef) * 100 : 0;
$economy = $totalRef - $totalReal; // Positive means saving

// 2. Charts Data (Monthly Evolution)
$sqlMonth = "SELECT month, SUM(amount_budgeted) as ref, SUM(amount_realized) as real_val 
             FROM budgets 
             WHERE company_id = ? AND fiscal_year = ? 
             GROUP BY month ORDER BY month ASC";
$stmtMonth = $pdo->prepare($sqlMonth);
$stmtMonth->execute([$companyId, $currentYear]);
$monthsData = $stmtMonth->fetchAll(PDO::FETCH_ASSOC);

$chartLabels = [];
$chartRef = [];
$chartReal = [];

// Initialize all 12 months with 0
for ($i = 1; $i <= 12; $i++) {
    $found = false;
    foreach ($monthsData as $m) {
        if ($m['month'] == $i) {
            $chartLabels[] = date("M", mktime(0, 0, 0, $i, 10)); // Jan, Feb...
            $chartRef[] = (float) $m['ref'];
            $chartReal[] = (float) $m['real_val'];
            $found = true;
            break;
        }
    }
    if (!$found) {
        $chartLabels[] = date("M", mktime(0, 0, 0, $i, 10));
        $chartRef[] = 0;
        $chartReal[] = 0;
    }
}

// 3. Category Data
$sqlCat = "SELECT c.name, SUM(b.amount_realized) as total 
           FROM budgets b 
           JOIN categories c ON b.category_id = c.id 
           WHERE b.company_id = ? AND b.fiscal_year = ? 
           GROUP BY c.name";
$stmtCat = $pdo->prepare($sqlCat);
$stmtCat->execute([$companyId, $currentYear]);
$catData = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

$catLabels = [];
$catValues = [];
$catColors = ['#2563eb', '#10b981', '#f59e0b', '#64748b', '#8b5cf6', '#ec4899', '#f43f5e'];
foreach ($catData as $c) {
    if ($c['total'] > 0) {
        $catLabels[] = $c['name'];
        $catValues[] = (float) $c['total'];
    }
}

// 4. Recent Activity
$sqlRecent = "SELECT b.*, c.name as category, cc.name as cost_center 
              FROM budgets b 
              JOIN categories c ON b.category_id = c.id
              JOIN cost_centers cc ON b.cost_center_id = cc.id
              WHERE b.company_id = ? 
              ORDER BY b.created_at DESC LIMIT 5";
$stmtRecent = $pdo->prepare($sqlRecent);
$stmtRecent->execute([$companyId]);
$recentItems = $stmtRecent->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Thriveo ZBB</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Chart.js for graphs -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Thriveo ZBB</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Chart.js for graphs -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="app-layout">
        <?php include '../includes/sidebar.php'; ?>

        <main class="main-content">
            <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div>
                    <h1 style="font-size: 1.75rem; margin-bottom: 0.5rem;">Dashboard Geral (ZBB)</h1>
                    <p style="color: var(--text-secondary);">Bem-vindo,
                        <?php echo htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['name'] ?? 'Usuário'); ?>
                    </p>
                </div>
                <div>
                    <!-- Link to ZBB internal page, make sure budget.php is also adapted if linked -->
                    <a href="budget.php" class="btn btn-primary">+ Detalhes do Orçamento</a>
                </div>
            </header>

            <!-- KPIs -->
            <div
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <div class="card">
                    <h3 style="font-size: 0.875rem; color: var(--text-secondary); text-transform: uppercase;">Aderência Utilização</h3>
                    <div style="font-size: 2rem; font-weight: 700; color: var(--primary-color);">
                        <?php echo number_format($percentUsed, 1, ',', '.'); ?>%
                    </div>
                    <div style="font-size: 0.875rem; color: var(--text-secondary);">do orçamento anual</div>
                </div>
                <div class="card">
                    <h3 style="font-size: 0.875rem; color: var(--text-secondary); text-transform: uppercase;">Economia Gerada (ZBB)</h3>
                    <?php 
                        $ecoColor = $economy >= 0 ? 'var(--success-color)' : 'var(--danger-color)';
                        $ecoSign = $economy >= 0 ? '' : '-';
                    ?>
                    <div style="font-size: 2rem; font-weight: 700; color: <?php echo $ecoColor; ?>;">
                        R$ <?php echo number_format(abs($economy), 0, ',', '.'); ?>
                    </div>
                    <div style="font-size: 0.875rem; color: <?php echo $ecoColor; ?>;">vs Referência</div>
                </div>
                <div class="card">
                    <h3 style="font-size: 0.875rem; color: var(--text-secondary); text-transform: uppercase;">Aguardando Aprovação</h3>
                    <div style="font-size: 2rem; font-weight: 700; color: var(--warning-color);">
                        <?php echo $pendingCount; ?>
                    </div>
                    <div style="font-size: 0.875rem; color: var(--text-secondary);">Itens pendentes</div>
                </div>
                <div class="card">
                    <h3 style="font-size: 0.875rem; color: var(--text-secondary); text-transform: uppercase;">Variação Média</h3>
                    <div style="font-size: 2rem; font-weight: 700;">
                        <?php 
                             // Just a simple calculation: (Total Real - Total Ref) / Total Ref
                             $varTotal = ($totalRef > 0) ? (($totalReal - $totalRef) / $totalRef) * 100 : 0;
                             echo ($varTotal > 0 ? '+' : '') . number_format($varTotal, 1, ',', '.'); 
                        ?>%
                    </div>
                    <div style="font-size: 0.875rem; color: var(--text-secondary);">Geral</div>
                </div>
            </div>

            <!-- Charts Row 1 -->
            <div
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <!-- Evolution Chart -->
                <div class="card">
                    <h3 style="font-size: 1.25rem; margin-bottom: 1.5rem;">Evolução de Despesas (Real vs Orçado)</h3>
                    <canvas id="evolutionChart"></canvas>
                </div>

                <!-- Category Breakdown -->
                <div class="card">
                    <h3 style="font-size: 1.25rem; margin-bottom: 1.5rem;">Distribuição por Categoria</h3>
                    <div style="height: 300px; display: flex; justify-content: center;">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Activity / Alerts -->
            <div class="card">
                <h3 style="font-size: 1.25rem; margin-bottom: 1.5rem;">Últimas Justificativas & Alertas</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 1px solid #e2e8f0;">
                            <th style="padding: 1rem;">Data</th>
                            <th style="padding: 1rem;">Centro de Custo</th>
                            <th style="padding: 1rem;">Categoria</th>
                            <th style="padding: 1rem;">Valor</th>
                            <th style="padding: 1rem;">Status</th>
                            <th style="padding: 1rem;">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($recentItems) > 0): ?>
                            <?php foreach($recentItems as $item): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 1rem;"><?php echo date('d/m/Y', strtotime($item['updated_at'] ?? $item['created_at'])); ?></td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($item['cost_center']); ?></td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($item['category']); ?></td>
                                <td style="padding: 1rem;">R$ <?php echo number_format($item['amount_realized'], 2, ',', '.'); ?></td>
                                <td style="padding: 1rem;">
                                    <?php 
                                        $stColor = 'var(--text-secondary)';
                                        $stLabel = ucfirst($item['status']);
                                        if($item['status'] == 'approved') { $stColor = 'var(--success-color)'; $stLabel = 'Aprovado'; }
                                        if($item['status'] == 'pending') { $stColor = 'var(--warning-color)'; $stLabel = 'Pendente'; }
                                    ?>
                                    <span style="color: <?php echo $stColor; ?>; font-weight: 600;"><?php echo $stLabel; ?></span>
                                </td>
                                <td style="padding: 1rem;">
                                    <a href="budget.php" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; text-decoration: none;">Ver</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align:center; padding: 2rem; color: #aaa;">Nenhuma atividade recente.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>

    <!-- Chart Config -->
    <script>
        // Evolution Chart
        const ctxEvolution = document.getElementById('evolutionChart').getContext('2d');
        const evolutionChart = new Chart(ctxEvolution, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chartLabels); ?>,
                datasets: [{
                    label: 'Orçado',
                    data: <?php echo json_encode($chartRef); ?>,
                    borderColor: '#2563eb', // Primary
                    tension: 0.4
                }, {
                    label: 'Realizado',
                    data: <?php echo json_encode($chartReal); ?>,
                    borderColor: '#10b981', // Success
                    borderDash: [5, 5],
                    tension: 0.4
                }]
            },
            options: { responsive: true }
        });

        // Category Chart
        const ctxCategory = document.getElementById('categoryChart').getContext('2d');
        const categoryChart = new Chart(ctxCategory, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($catLabels); ?>,
                datasets: [{
                    data: <?php echo json_encode($catValues); ?>,
                    backgroundColor: <?php echo json_encode(array_slice($catColors, 0, count($catValues))); ?>
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    </script>
</body>

</html>
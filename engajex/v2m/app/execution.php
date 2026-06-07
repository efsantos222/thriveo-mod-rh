<?php
session_start();
require_once dirname(__DIR__) . '/config.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'responsavel') {
    redirect('login.php');
}

// Fetch Active V2MOM
$stmt = $pdo->prepare("SELECT id, versao FROM v2mom WHERE id_empresa = ? AND id_responsavel = ? ORDER BY id DESC LIMIT 1");
$stmt->execute([$_SESSION['empresa_id'], $_SESSION['user_id']]);
$v2mom = $stmt->fetch();
$v2mom_id = $v2mom['id'] ?? null;

// Handle Updates (Methods)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_method'])) {
    $met_id = $_POST['met_id'];
    $status = $_POST['met_status'];

    $stmt = $pdo->prepare("UPDATE metodos SET status = ? WHERE id = ?");
    $stmt->execute([$status, $met_id]);
}

// Handle Updates (Metrics)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_metric'])) {
    $metric_id = $_POST['metric_id'];
    $valor_atual = $_POST['metric_value'];

    $stmt = $pdo->prepare("UPDATE metricas SET valor_atual = ? WHERE id = ?");
    $stmt->execute([$valor_atual, $metric_id]);
}

// Fetch Data
$methods = [];
$metrics = [];
if ($v2mom_id) {
    // Methods
    $stmt = $pdo->prepare("SELECT * FROM metodos WHERE id_v2mom = ? ORDER BY status ASC");
    $stmt->execute([$v2mom_id]);
    $methods = $stmt->fetchAll();

    // Metrics
    $stmt = $pdo->prepare("SELECT * FROM metricas WHERE id_v2mom = ?");
    $stmt->execute([$v2mom_id]);
    $metrics = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Execução - V2MOM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            padding-top: 80px;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 80px;
            bottom: 0;
            width: 250px;
            background: rgba(30, 41, 59, 0.9);
            border-right: 1px solid var(--glass-border);
            padding: 20px;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
        }

        .app-nav li {
            margin-bottom: 10px;
        }

        .app-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 6px;
            color: #cbd5e1;
        }

        .app-nav a:hover,
        .app-nav a.active {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary-color);
        }
    </style>
</head>

<body>
    <header class="glass"
        style="position: fixed; top: 0; width: 100%; height: 80px; z-index: 50; display: flex; align-items: center; padding: 0 20px; justify-content: space-between;">
        <div class="logo"><i class="ph ph-target"></i> V2MOM APP</div>
        <a href="logout.php" class="btn btn-outline" style="padding: 5px 15px; font-size: 0.8rem;">Sair</a>
    </header>

    <aside class="sidebar">
        <ul class="app-nav">
            <li><a href="dashboard.php"><i class="ph ph-squares-four"></i> Visão Geral</a></li>
            <li><a href="v2mom_builder.php"><i class="ph ph-tree-structure"></i> Construir V2MOM</a></li>
            <li><a href="execution.php" class="active"><i class="ph ph-check-square-offset"></i> Execução & Métricas</a>
            </li>
            <li><a href="reports.php"><i class="ph ph-file-pdf"></i> Relatórios</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h2 style="margin-bottom: 30px;">Execução e Acompanhamento <span
                style="font-size: 1rem; color: #94a3b8; font-weight: normal;">(<?= htmlspecialchars($v2mom['versao'] ?? 'Nenhum V2MOM Ativo') ?>)</span>
        </h2>

        <?php if (!$v2mom_id): ?>
            <div class="glass" style="padding: 40px; text-align: center;">
                <p>Nenhum V2MOM encontrado. <a href="v2mom_builder.php" style="color: var(--primary-color);">Crie o seu
                        agora.</a></p>
            </div>
        <?php else: ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                <!-- Methods Tracking -->
                <div>
                    <h3 style="margin-bottom: 20px; color: var(--secondary-color);"><i class="ph ph-list-checks"></i> Status
                        dos Métodos</h3>
                    <div class="glass" style="padding: 20px;">
                        <?php if (empty($methods)): ?>
                            <p style="color: #94a3b8;">Nenhum método cadastrado.</p>
                        <?php else: ?>
                            <div style="display: flex; flex-direction: column; gap: 15px;">
                                <?php foreach ($methods as $met): ?>
                                    <div
                                        style="padding: 15px; border-bottom: 1px solid var(--glass-border); display: flex; align-items: center; justify-content: space-between;">
                                        <div style="flex: 1;">
                                            <p style="margin-bottom: 5px; font-weight: 600;">
                                                <?= htmlspecialchars($met['descricao']) ?>
                                            </p>
                                            <span style="font-size: 0.8rem; color: #94a3b8;"><i class="ph ph-calendar"></i>
                                                <?= htmlspecialchars($met['prazo']) ?></span>
                                        </div>
                                        <form method="POST" style="margin-left: 20px;">
                                            <input type="hidden" name="met_id" value="<?= $met['id'] ?>">
                                            <select name="met_status" onchange="this.form.submit()" name="update_method"
                                                style="padding: 5px; border-radius: 4px; border: none; font-size: 0.8rem; color: white; background: <?= $met['status'] == 'concluido' ? '#10b981' : ($met['status'] == 'em_andamento' ? '#f59e0b' : '#64748b') ?>;">
                                                <option value="nao_iniciado" <?= $met['status'] == 'nao_iniciado' ? 'selected' : '' ?>>
                                                    Não Iniciado</option>
                                                <option value="em_andamento" <?= $met['status'] == 'em_andamento' ? 'selected' : '' ?>>
                                                    Em Andamento</option>
                                                <option value="concluido" <?= $met['status'] == 'concluido' ? 'selected' : '' ?>>
                                                    Concluído</option>
                                            </select>
                                            <input type="hidden" name="update_method" value="1">
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Metrics Tracking -->
                <div>
                    <h3 style="margin-bottom: 20px; color: var(--secondary-color);"><i class="ph ph-chart-line-up"></i>
                        Atualização de Métricas</h3>
                    <div style="display: grid; gap: 15px;">
                        <?php if (empty($metrics)): ?>
                            <div class="glass" style="padding: 20px;">
                                <p style="color: #94a3b8;">Nenhuma métrica cadastrada.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($metrics as $metric):
                                // Corrigir erro de valor numérico mal formado
                                $meta_val = (float) str_replace(',', '.', $metric['meta'] ?? 0);
                                $atual_val = (float) str_replace(',', '.', $metric['valor_atual'] ?? 0);

                                $progress = $meta_val > 0 ? ($atual_val / $meta_val) * 100 : 0;
                                $progress = min(100, max(0, $progress));
                                ?>
                                <div class="glass" style="padding: 20px;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                        <span style="font-weight: 600;"><?= htmlspecialchars($metric['descricao']) ?></span>
                                        <span style="color: #94a3b8;"><?= htmlspecialchars($metric['unidade']) ?></span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 10px;">
                                        <div
                                            style="flex: 1; height: 8px; background: #334155; border-radius: 4px; overflow: hidden;">
                                            <div style="height: 100%; width: <?= $progress ?>%; background: var(--primary-color);">
                                            </div>
                                        </div>
                                        <span
                                            style="font-size: 0.85rem; color: var(--primary-color);"><?= number_format($progress, 0) ?>%</span>
                                    </div>
                                    <form method="POST" style="display: flex; gap: 10px; align-items: center;">
                                        <input type="hidden" name="metric_id" value="<?= $metric['id'] ?>">
                                        <input type="number" name="metric_value"
                                            value="<?= htmlspecialchars($metric['valor_atual']) ?>" step="any"
                                            style="width: 100px; padding: 5px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 4px;">
                                        <span style="font-size: 0.9rem; color: #64748b;">/
                                            <?= htmlspecialchars($metric['meta']) ?></span>
                                        <button type="submit" name="update_metric" value="1" class="btn btn-primary"
                                            style="padding: 5px 10px; font-size: 0.8rem;">Atualizar</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </main>
</body>

</html>
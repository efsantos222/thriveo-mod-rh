<?php
session_start();
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/AIHelper.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'responsavel') {
    redirect('login.php');
}

$aiHelper = new AIHelper($pdo, $_SESSION['empresa_id']);

// Fetch Active V2MOM
$stmt = $pdo->prepare("SELECT id, versao, data_criacao FROM v2mom WHERE id_empresa = ? AND id_responsavel = ? ORDER BY id DESC LIMIT 1");
$stmt->execute([$_SESSION['empresa_id'], $_SESSION['user_id']]);
$v2mom = $stmt->fetch();
$v2mom_id = $v2mom['id'] ?? null;

$full_report = [];
$analysis = '';

if ($v2mom_id) {
    // 1. Vision
    $stmt = $pdo->prepare("SELECT descricao FROM visoes WHERE id_v2mom = ?");
    $stmt->execute([$v2mom_id]);
    $full_report['vision'] = $stmt->fetchColumn();

    // 2. Values
    $stmt = $pdo->prepare("SELECT descricao, prioridade FROM valores WHERE id_v2mom = ? ORDER BY prioridade ASC");
    $stmt->execute([$v2mom_id]);
    $full_report['values'] = $stmt->fetchAll();

    // 3. Methods
    $stmt = $pdo->prepare("SELECT * FROM metodos WHERE id_v2mom = ?");
    $stmt->execute([$v2mom_id]);
    $full_report['methods'] = $stmt->fetchAll();

    // Calculate Methods Stats
    $total_methods = count($full_report['methods']);
    $completed_methods = 0;
    foreach ($full_report['methods'] as $m)
        if ($m['status'] == 'concluido')
            $completed_methods++;
    $methods_progress = $total_methods > 0 ? ($completed_methods / $total_methods) * 100 : 0;

    // 4. Obstacles
    $stmt = $pdo->prepare("SELECT * FROM obstaculos WHERE id_v2mom = ?");
    $stmt->execute([$v2mom_id]);
    $full_report['obstacles'] = $stmt->fetchAll();

    // 5. Metrics
    $stmt = $pdo->prepare("SELECT * FROM metricas WHERE id_v2mom = ?");
    $stmt->execute([$v2mom_id]);
    $full_report['metrics'] = $stmt->fetchAll();

    // Calculate Metrics Overall Progress
    $metrics_progress_sum = 0;
    $metrics_count = count($full_report['metrics']);
    foreach ($full_report['metrics'] as $met) {
        $meta = floatval($met['meta'] ?? 0);
        $atual = floatval($met['valor_atual'] ?? 0);

        $p = $meta > 0 ? ($atual / $meta) : 0;
        $metrics_progress_sum += min(1.0, $p);
    }
    $metrics_overall = $metrics_count > 0 ? ($metrics_progress_sum / $metrics_count) * 100 : 0;
}

// AI Analysis Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['analyze_report'])) {
    $context = "Visão: {$full_report['vision']}. Progresso Métodos: " . number_format($methods_progress, 0) . "%. Progresso Métricas: " . number_format($metrics_overall, 0) . "%.";
    $prompt = "Gere um resumo executivo breve e motivador sobre o status atual deste V2MOM. Destaque o progresso e sugira foco para a próxima semana.";

    $res = $aiHelper->generateResponse("Seja um Chief of Staff executivo.", $prompt, $v2mom_id, 'report_analysis');
    $analysis = $res['content'] ?? 'Erro ao gerar análise.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - V2MOM</title>
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

        @media print {

            .sidebar,
            header,
            .no-print {
                display: none !important;
            }

            .main-content {
                margin-left: 0 !important;
                padding: 0 !important;
            }

            body {
                padding-top: 0 !important;
                background: white !important;
                color: black !important;
            }

            .glass {
                background: none !important;
                border: 1px solid #ccc !important;
                box-shadow: none !important;
            }

            h1,
            h2,
            h3,
            h4,
            p,
            span,
            li,
            label {
                color: black !important;
            }

            .btn {
                display: none !important;
            }
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
            <li><a href="execution.php"><i class="ph ph-check-square-offset"></i> Execução & Métricas</a></li>
            <li><a href="reports.php" class="active"><i class="ph ph-file-pdf"></i> Relatórios</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <h2>Relatório de Performance Executiva</h2>
                <p style="color: #94a3b8;">Gerado em: <?= date('d/m/Y H:i') ?></p>
            </div>
            <div class="no-print" style="display: flex; gap: 10px;">
                <button onclick="window.print()" class="btn btn-outline"><i class="ph ph-printer"></i> Imprimir /
                    PDF</button>
            </div>
        </div>

        <?php if (!$v2mom_id): ?>
            <div class="glass" style="padding: 40px; text-align: center;">
                <p>Nenhum dado para exibir.</p>
            </div>
        <?php else: ?>

            <!-- AI Summary Section -->
            <div class="glass" style="padding: 30px; margin-bottom: 30px; border-left: 4px solid var(--primary-color);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                    <h3 style="color: white;">Resumo Executivo (IA)</h3>
                    <?php if (!$analysis): ?>
                        <form method="POST" class="no-print">
                            <button type="submit" name="analyze_report" value="1" class="btn btn-primary"
                                style="font-size: 0.8rem;"><i class="ph ph-sparkle"></i> Gerar Análise</button>
                        </form>
                    <?php endif; ?>
                </div>

                <?php if ($analysis): ?>
                    <div style="background: rgba(255,255,255,0.05); padding: 20px; border-radius: 8px; line-height: 1.6;">
                        <?= nl2br(htmlspecialchars($analysis)) ?>
                    </div>
                <?php else: ?>
                    <p style="color: #94a3b8; font-style: italic;">Clique em "Gerar Análise" para obter um insight estratégico
                        do seu progresso.</p>
                <?php endif; ?>
            </div>

            <!-- Stats Overview -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                <div class="glass" style="padding: 20px; text-align: center;">
                    <h4 style="color: #94a3b8; margin-bottom: 10px;">Progresso dos Métodos</h4>
                    <div style="font-size: 2.5rem; color: #6ee7b7; font-weight: bold;">
                        <?= number_format($methods_progress, 0) ?>%
                    </div>
                    <p style="font-size: 0.9rem; color: #cbd5e1;"><?= $completed_methods ?> de <?= $total_methods ?>
                        concluídos</p>
                </div>
                <div class="glass" style="padding: 20px; text-align: center;">
                    <h4 style="color: #94a3b8; margin-bottom: 10px;">Atingimento de Metas</h4>
                    <div style="font-size: 2.5rem; color: var(--secondary-color); font-weight: bold;">
                        <?= number_format($metrics_overall, 0) ?>%
                    </div>
                    <p style="font-size: 0.9rem; color: #cbd5e1;">Média global dos KPIs</p>
                </div>
            </div>

            <!-- Report Details -->
            <div class="glass" style="padding: 40px;">
                <h3
                    style="color: var(--primary-color); border-bottom: 1px solid #334155; padding-bottom: 10px; margin-bottom: 20px;">
                    1. Visão</h3>
                <p style="font-size: 1.1rem; line-height: 1.6; margin-bottom: 30px;">
                    "<?= htmlspecialchars($full_report['vision']) ?>"</p>

                <h3
                    style="color: var(--primary-color); border-bottom: 1px solid #334155; padding-bottom: 10px; margin-bottom: 20px;">
                    2. Valores</h3>
                <ul style="margin-bottom: 30px; list-style-type: none;">
                    <?php foreach ($full_report['values'] as $val): ?>
                        <li style="margin-bottom: 8px; font-weight: 500;">• <?= htmlspecialchars($val['descricao']) ?></li>
                    <?php endforeach; ?>
                </ul>

                <h3
                    style="color: var(--primary-color); border-bottom: 1px solid #334155; padding-bottom: 10px; margin-bottom: 20px;">
                    3. Métricas Principais</h3>
                <table style="width: 100%; text-align: left; border-collapse: collapse; margin-bottom: 30px;">
                    <thead>
                        <tr style="border-bottom: 1px solid #475569;">
                            <th style="padding: 10px; color: #94a3b8;">Métrica</th>
                            <th style="padding: 10px; color: #94a3b8;">Meta</th>
                            <th style="padding: 10px; color: #94a3b8;">Atual</th>
                            <th style="padding: 10px; color: #94a3b8;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($full_report['metrics'] as $met):
                            $meta = floatval($met['meta'] ?? 0);
                            $atual = floatval($met['valor_atual'] ?? 0);
                            $p = $meta > 0 ? ($atual / $meta) * 100 : 0;
                            ?>
                            <tr style="border-bottom: 1px solid #1e293b;">
                                <td style="padding: 10px;"><?= htmlspecialchars($met['descricao']) ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($met['meta']) ?>
                                    <?= htmlspecialchars($met['unidade']) ?>
                                </td>
                                <td style="padding: 10px;"><?= htmlspecialchars($met['valor_atual']) ?></td>
                                <td style="padding: 10px;">
                                    <span
                                        style="color: <?= $p >= 100 ? '#6ee7b7' : ($p >= 50 ? '#fde047' : '#fca5a5') ?>"><?= number_format($p, 0) ?>%</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>
    </main>
</body>

</html>
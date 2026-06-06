<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkAuth('responsible');

$companyId = $_SESSION['company_id'];

// Mocked action plans based on maturity gaps (simple logic)
$actions = [];

// Get latest assessment
$stmt = $pdo->prepare("SELECT * FROM maturity_assessments WHERE company_id = ? ORDER BY assessment_date DESC LIMIT 1");
$stmt->execute([$companyId]);
$assessment = $stmt->fetch();

if ($assessment) {
    if ($assessment['credibility_score'] < 70) {
        $actions[] = ['area' => 'Credibilidade', 'action' => 'Implementar reuniões mensais de transparência com a liderança.', 'priority' => 'Alta'];
    }
    if ($assessment['respect_score'] < 70) {
        $actions[] = ['area' => 'Respeito', 'action' => 'Revisar pacote de benefícios e programas de saúde mental.', 'priority' => 'Média'];
    }
    if ($assessment['impartiality_score'] < 70) {
        $actions[] = ['area' => 'Imparcialidade', 'action' => 'Criar comitê de diversidade e inclusão.', 'priority' => 'Alta'];
    }
    if ($assessment['pride_score'] < 70) {
        $actions[] = ['area' => 'Orgulho', 'action' => 'Reforçar comunicação sobre missão e valores da empresa.', 'priority' => 'Média'];
    }
    if ($assessment['camaraderie_score'] < 70) {
        $actions[] = ['area' => 'Camaradagem', 'action' => 'Organizar eventos de integração trimestrais.', 'priority' => 'Baixa'];
    }

    if (empty($actions)) {
        $actions[] = ['area' => 'Geral', 'action' => 'Manter os bons índices e buscar certificação oficial.', 'priority' => 'Baixa'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Planos de Ação - MLPT</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        .action-card {
            background: var(--card-bg);
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 15px;
            border-left: 4px solid var(--primary);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .action-priority {
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .priority-Alta {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }

        .priority-Média {
            background: rgba(245, 158, 11, 0.2);
            color: #f59e0b;
        }

        .priority-Baixa {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <div class="sidebar">
            <div class="logo">MLPT System</div>
            <a href="index.php" class="menu-item"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
            <a href="maturity.php" class="menu-item"><i class="fa-solid fa-sliders"></i> Avaliação Maturidade</a>
            <a href="documents.php" class="menu-item"><i class="fa-solid fa-file-contract"></i> Análise Documental</a>
            <a href="trust_index.php" class="menu-item"><i class="fa-solid fa-flask"></i> Simulador Trust Index</a>
            <a href="action_plan.php" class="menu-item active"><i class="fa-solid fa-list-check"></i> Planos de Ação</a>
            <a href="../logout.php" class="menu-item"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
        </div>
        <div class="content">
            <h2>Planos de Ação Recomendados</h2>
            <p style="margin-bottom: 30px; color: var(--text-dim);">Baseado na sua avaliação de maturidade.</p>

            <?php if (!$assessment): ?>
                <div
                    style="background: rgba(99, 102, 241, 0.1); padding: 20px; border-radius: 12px; border: 1px solid var(--primary);">
                    <p>Realize a Avaliação de Maturidade para gerar seu plano de ação.</p>
                </div>
            <?php else: ?>
                <?php foreach ($actions as $act): ?>
                    <div class="action-card">
                        <div>
                            <span
                                style="display:block; font-size: 0.9rem; color: var(--text-dim); margin-bottom: 5px;"><?php echo htmlspecialchars($act['area']); ?></span>
                            <h4><?php echo htmlspecialchars($act['action']); ?></h4>
                        </div>
                        <span
                            class="action-priority priority-<?php echo $act['priority']; ?>"><?php echo $act['priority']; ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
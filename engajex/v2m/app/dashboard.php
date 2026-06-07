<?php
session_start();
require_once dirname(__DIR__) . '/config.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'responsavel') {
    redirect('login.php');
}

$user_name = $_SESSION['user_name'];
$empresa_id = $_SESSION['empresa_id'];

// Buscar V2MOM ativo
$stmt = $pdo->prepare("SELECT * FROM v2mom WHERE id_empresa = ? ORDER BY data_criacao DESC LIMIT 1");
$stmt->execute([$empresa_id]);
$v2mom = $stmt->fetch();

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - V2MOM Intelligence</title>
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
            margin-bottom: 15px;
        }

        .app-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            border-radius: 8px;
            color: #cbd5e1;
            transition: all 0.3s;
        }

        .app-nav a:hover,
        .app-nav a.active {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary-color);
            border: 1px solid rgba(99, 102, 241, 0.2);
        }
    </style>
</head>

<body>

    <header class="glass"
        style="position: fixed; top: 0; width: 100%; height: 80px; z-index: 50; display: flex; align-items: center; padding: 0 20px; justify-content: space-between;">
        <div class="logo">
            <i class="ph ph-target" style="color: var(--secondary-color)"></i> V2MOM <span
                style="font-size: 0.8em; opacity: 0.7; margin-left: 10px;">APP</span>
        </div>
        <div style="display: flex; align-items: center; gap: 15px;">
            <span>Olá, <?= htmlspecialchars($user_name) ?></span>
            <a href="logout.php" class="btn btn-outline" style="padding: 5px 15px; font-size: 0.8rem;">Sair</a>
        </div>
    </header>

    <aside class="sidebar">
        <div style="margin-bottom: 30px; padding: 0 10px;">
            <small style="color: #64748b; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px;">Menu
                Principal</small>
        </div>
        <ul class="app-nav">
            <li><a href="dashboard.php" class="active"><i class="ph ph-squares-four"></i> Visão Geral</a></li>
            <li><a href="v2mom_builder.php"><i class="ph ph-tree-structure"></i> Construir V2MOM</a></li>
            <li><a href="execution.php"><i class="ph ph-check-square-offset"></i> Execução & Métricas</a></li>
            <li><a href="reports.php"><i class="ph ph-file-pdf"></i> Relatórios</a></li>
        </ul>

        <div
            style="margin-top: 50px; padding: 20px; background: rgba(99, 102, 241, 0.1); border-radius: 12px; text-align: center;">
            <i class="ph ph-sparkle" style="color: var(--warning-color); font-size: 1.5rem; margin-bottom: 10px;"></i>
            <p style="font-size: 0.9rem; margin-bottom: 10px;">Precisa de ajuda estratégica?</p>
            <button class="btn btn-primary" style="width: 100%; font-size: 0.8rem;">Falar com IA</button>
        </div>
    </aside>

    <main class="main-content">
        <h1 style="margin-bottom: 30px;">Dashboard Executivo</h1>

        <?php if (!$v2mom): ?>
            <div class="glass" style="text-align: center; padding: 60px;">
                <i class="ph ph-paper-plane-tilt"
                    style="font-size: 4rem; color: var(--primary-color); margin-bottom: 20px;"></i>
                <h2>Nenhum V2MOM encontrado</h2>
                <p style="margin-bottom: 30px; color: #94a3b8;">Comece agora mesmo a planejar o futuro da sua empresa com
                    inteligência.</p>
                <a href="v2mom_builder.php" class="btn btn-primary">Criar Meu Primeiro V2MOM</a>
            </div>
        <?php else: ?>
            <div class="glass" style="padding: 20px; margin-bottom: 30px;">
                <h2>V2MOM: <?= htmlspecialchars($v2mom['versao']) ?></h2>
                <p class="status-badge">Status: <?= htmlspecialchars($v2mom['status']) ?></p>
            </div>
            <!-- Metrics would go here -->
        <?php endif; ?>
    </main>

</body>

</html>
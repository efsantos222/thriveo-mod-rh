<?php
session_start();
require_once dirname(__DIR__) . '/config.php';

if (!isAdmin()) {
    redirect('login.php');
}

// Estatísticas Rápidas
$stmt_empresas = $pdo->query("SELECT COUNT(*) FROM empresas WHERE status='ativo'");
$total_empresas = $stmt_empresas->fetchColumn();

$stmt_responsaveis = $pdo->query("SELECT COUNT(*) FROM responsaveis WHERE status='ativo'");
$total_responsaveis = $stmt_responsaveis->fetchColumn();

// Simulação de Tokens (se não tiver dados ainda)
$tokens_usados = 0; // Exemplo
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - V2MOM Intelligence</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Admin specific styles override */
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

        .stat-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0.01));
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 20px;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            margin: 10px 0;
        }

        .stat-label {
            color: #94a3b8;
        }

        .admin-nav li {
            margin-bottom: 10px;
        }

        .admin-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 6px;
            color: #cbd5e1;
        }

        .admin-nav a:hover,
        .admin-nav a.active {
            background: var(--primary-color);
            color: white;
        }
    </style>
</head>

<body>

    <header class="glass"
        style="position: fixed; top: 0; width: 100%; height: 80px; z-index: 50; display: flex; align-items: center; padding: 0 20px; justify-content: space-between;">
        <div class="logo">
            <i class="ph ph-strategy"></i> V2MOM <span
                style="font-size: 0.8em; opacity: 0.7; margin-left: 10px;">ADMIN</span>
        </div>
        <div style="display: flex; align-items: center; gap: 15px;">
            <span>Olá, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
            <a href="logout.php" class="btn btn-outline" style="padding: 5px 15px; font-size: 0.8rem;">Sair</a>
        </div>
    </header>

    <aside class="sidebar">
        <ul class="admin-nav">
            <li><a href="dashboard.php" class="active"><i class="ph ph-squares-four"></i> Dashboard</a></li>
            <li><a href="empresas.php"><i class="ph ph-buildings"></i> Empresas</a></li>
            <li><a href="responsaveis.php"><i class="ph ph-users"></i> Responsáveis</a></li>
            <li><a href="config_ia.php"><i class="ph ph-robot"></i> Configuração IA</a></li>
            <li><a href="logs.php"><i class="ph ph-scroll"></i> Logs & Auditoria</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h1 style="margin-bottom: 30px;">Visão Geral</h1>

        <div class="cards-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
            <div class="stat-card">
                <div class="stat-label">Empresas Ativas</div>
                <div class="stat-value text-gradient"><?= $total_empresas ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Responsáveis</div>
                <div class="stat-value"><?= $total_responsaveis ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Tokens OpenAI (Mês)</div>
                <div class="stat-value"><?= number_format($tokens_usados) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Acessos (30 dias)</div>
                <div class="stat-value">--</div>
            </div>
        </div>

        <div style="margin-top: 40px;">
            <div class="glass" style="padding: 20px;">
                <h3 style="margin-bottom: 20px;">Atividades Recentes</h3>
                <p style="color: #94a3b8; text-align: center; padding: 20px;">Nenhuma atividade registrada recentemente.
                </p>
            </div>
        </div>
    </main>

</body>

</html>
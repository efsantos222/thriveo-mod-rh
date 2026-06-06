<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkAuth('responsible');
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Simulador Trust Index - MLPT</title>
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

        .simulation-card {
            background: var(--card-bg);
            padding: 40px;
            border-radius: 12px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .coming-soon {
            font-size: 1.2rem;
            color: var(--text-dim);
            margin-top: 20px;
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
            <a href="trust_index.php" class="menu-item active"><i class="fa-solid fa-flask"></i> Simulador Trust
                Index</a>
            <a href="action_plan.php" class="menu-item"><i class="fa-solid fa-list-check"></i> Planos de Ação</a>
            <a href="../logout.php" class="menu-item"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
        </div>
        <div class="content">
            <h2>Simulador Trust Index</h2>
            <div class="simulation-card">
                <i class="fa-solid fa-flask" style="font-size: 4rem; color: var(--primary); margin-bottom: 20px;"></i>
                <h3>Em Desenvolvimento</h3>
                <p class="coming-soon">O simulador avançado de pesquisa Trust Index estará disponível em breve.
                    <br>Utilize a Avaliação de Maturidade para diagnósticos preliminares.</p>
            </div>
        </div>
    </div>
</body>

</html>
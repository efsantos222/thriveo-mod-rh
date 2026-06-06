<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkAuth('admin');

// Get stats
$stmt = $pdo->query("SELECT COUNT(*) as count FROM companies");
$companyCount = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'responsible'");
$userCount = $stmt->fetch()['count'];

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Painel Admin - MLPT</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: #1e293b;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            padding: 20px;
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
            transition: all 0.2s;
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

        .content {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--card-bg);
            padding: 24px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .stat-card h3 {
            color: var(--text-dim);
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .stat-card .value {
            font-size: 2rem;
            font-weight: 700;
        }
    </style>
</head>

<body>
    <div class="admin-layout">
        <div class="sidebar">
            <div class="logo">MLPT Admin</div>
            <a href="index.php" class="menu-item active"><i class="fa-solid fa-house"></i> Home</a>
            <a href="companies.php" class="menu-item"><i class="fa-solid fa-building"></i> Empresas</a>
            <a href="settings.php" class="menu-item"><i class="fa-solid fa-gear"></i> Configurações</a>
            <a href="../logout.php" class="menu-item"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
        </div>
        <div class="content">
            <h2 style="margin-bottom: 30px;">Visão Geral</h2>
            <div class="stat-grid">
                <div class="stat-card">
                    <h3>Empresas Cadastradas</h3>
                    <div class="value"><?php echo $companyCount; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Responsáveis Ativos</h3>
                    <div class="value"><?php echo $userCount; ?></div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
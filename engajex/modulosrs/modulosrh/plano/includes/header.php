<?php
require_once 'includes/auth.php';
// checkLogin() removed here because this is included in header, but header.php might be included in login.php (unlikely but safe to keep clean)
// Actually header.php usually has the nav.
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sinergy Matrix</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@2.2.1"></script>
    <!-- CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="app-container">
        <header class="main-header">
            <a href="index.php" class="brand">
                <img src="img/logo.jpg" alt="Sinergy Matrix" style="height: 40px;">
                <span>Sinergy Matrix</span>
            </a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <nav class="nav-links">
                    <a href="index.php"
                        class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">Dashboard</a>
                    <a href="new_service.php"
                        class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'new_service.php' ? 'active' : '' ?>">Adicionar
                        Serviço</a>
                    <a href="ai_opportunities.php"
                        class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'ai_opportunities.php' ? 'active' : '' ?>"><i
                            class="fa-solid fa-wand-magic-sparkles"></i> IA Insights</a>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <a href="admin_dashboard.php"
                            class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : '' ?>"
                            style="color: var(--text-primary); font-weight: bold;"><i class="fa-solid fa-lock"></i> Admin</a>
                    <?php endif; ?>
                </nav>
                <div class="user-menu" style="display: flex; gap: 10px; align-items: center;">
                    <span style="font-size: 0.9rem; color: var(--text-secondary);">
                        <i class="fa-solid fa-building"></i> <?= htmlspecialchars($_SESSION['company_name']) ?>
                    </span>
                    <a href="logout.php" class="btn btn-outline btn-sm" title="Sair">
                        <i class="fa-solid fa-sign-out-alt"></i>
                    </a>
                </div>
            <?php endif; ?>
        </header>
        <main class="container">
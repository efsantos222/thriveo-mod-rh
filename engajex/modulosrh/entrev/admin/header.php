<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'superadmin') {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Superadmin - ProfTest</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>

<body>
    <div class="app-container">
        <aside class="sidebar">
            <div class="logo">
                <i class="ph ph-shield-check"></i> Admin
            </div>
            <ul class="nav-links">
                <li class="nav-item">
                    <a href="dashboard.php"
                        class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                        <i class="ph ph-chart-pie-slice"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="companies.php"
                        class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'companies.php' ? 'active' : '' ?>">
                        <i class="ph ph-buildings"></i> Empresas
                    </a>
                </li>
                <li class="nav-item">
                    <a href="users.php"
                        class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>">
                        <i class="ph ph-users"></i> Responsáveis
                    </a>
                </li>
                <li class="nav-item">
                    <a href="settings.php"
                        class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>">
                        <i class="ph ph-gear"></i> Configurações
                    </a>
                </li>
                <li class="nav-item" style="margin-top: auto;">
                    <a href="../back_to_system.php" class="nav-link" style="color: #6366f1;">
                        <i class="ph ph-arrow-left"></i> Voltar ao Sistema
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../logout.php" class="nav-link">
                        <i class="ph ph-sign-out"></i> Sair
                    </a>
                </li>
            </ul>
        </aside>
        <main class="main-content">
            <div class="top-bar">
                <h2>
                    <?= $pageTitle ?? 'Painel' ?>
                </h2>
                <div class="user-profile">
                    <span>Olá,
                        <?= htmlspecialchars($_SESSION['user_name']) ?>
                    </span>
                </div>
            </div>
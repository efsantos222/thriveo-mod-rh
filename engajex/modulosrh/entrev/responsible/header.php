<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'responsible') {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Responsável - ProfTest</title>
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
                <i class="ph ph-briefcase"></i> Recrutamento
            </div>
            <ul class="nav-links">
                <li class="nav-item">
                    <a href="dashboard.php"
                        class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                        <i class="ph ph-chart-pie-slice"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="jobs.php"
                        class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'jobs.php' || basename($_SERVER['PHP_SELF']) == 'job_details.php' ? 'active' : '' ?>">
                        <i class="ph ph-briefcase"></i> Vagas & Roteiros
                    </a>
                </li>
                <li class="nav-item">
                    <a href="candidates.php"
                        class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'candidates.php' ? 'active' : '' ?>">
                        <i class="ph ph-users"></i> Candidatos
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
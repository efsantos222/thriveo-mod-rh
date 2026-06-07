<?php
// Bridge session from main system
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check main system login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

// Connect to main system config/db
require_once 'config/db.php';
checkAccess(); // This will enforce trial/subscription in fvit as well

// Map main session info to FVIT expectations
// Ensure fvit works with the system role
$role = $_SESSION['role']; 
$_SESSION['user_name'] = $_SESSION['name']; // Align for fvit
$_SESSION['user_role'] = $_SESSION['role']; // Align for fvit
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Proftest Formata</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- PDF.js for parsing -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    </script>
</head>

<body>
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h3 style="margin-bottom: 2rem; color: #0f172a;">Proftest Formata</h3>

            <div class="user-info"
                style="margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid var(--glass-border);">
                <div style="font-weight: 600; color: var(--text-main);">
                    <?= htmlspecialchars($_SESSION['user_name']) ?>
                </div>
                <div style="font-size: 0.85rem; color: var(--text-muted); text-transform: capitalize;">
                    <?= $role ?>
                </div>
            </div>

            <nav>
                <a href="?page=home"
                    class="nav-link <?= (!isset($_GET['page']) || $_GET['page'] == 'home') ? 'active' : '' ?>">
                    <?= $role === 'admin' ? 'Gerenciar Usuários' : 'Formatar Currículo' ?>
                </a>

                <?php if ($role === 'admin'): ?>
                    <a href="?page=companies"
                        class="nav-link <?= (isset($_GET['page']) && $_GET['page'] == 'companies') ? 'active' : '' ?>">Gerenciar
                        Empresas</a>
                    <a href="?page=settings"
                        class="nav-link <?= (isset($_GET['page']) && $_GET['page'] == 'settings') ? 'active' : '' ?>">Configurações
                        API</a>
                <?php endif; ?>

                <a href="../../dashboard.php" class="nav-link" style="margin-top: 2rem; color: var(--primary-light);">← Voltar ao Sistema</a>
                <a href="logout.php" class="nav-link" style="color: #ff8888;">Sair</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <?php
            $page = $_GET['page'] ?? 'home';

            if ($role === 'admin') {
                if ($page === 'home')
                    include 'views/admin_users.php';
                elseif ($page === 'companies')
                    include 'views/admin_companies.php';
                elseif ($page === 'settings')
                    include 'views/admin_settings.php';
                else
                    include 'views/admin_users.php';
            } else {
                // Responsible Role
                include 'views/formatter_panel.php';
            }
            ?>
        </main>
    </div>
</body>

</html>
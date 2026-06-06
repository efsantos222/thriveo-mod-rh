<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'config/db.php';

// Route based on role
$role = $_SESSION['user_role'];
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
            <h3 style="margin-bottom: 2rem; color: white;">Proftest Formata</h3>

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

                <a href="logout.php" class="nav-link" style="margin-top: 2rem; color: #ff8888;">Sair</a>
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
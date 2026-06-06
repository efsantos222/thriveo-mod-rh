<?php
require_once '../config/config.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Proftest Board</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php include '../includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Topbar -->
            <header class="topbar">
                <div>
                    <!-- Breadcrumbs could go here -->
                    <h2 style="font-size: 1.5rem; margin:0;">Dashboard</h2>
                </div>
                <div class="user-profile">
                    <span>Olá, <strong><?php echo $_SESSION['user_name']; ?></strong></span>
                    <span class="badge"
                        style="background:#eff6ff; color:#2563eb; padding:2px 8px; border-radius:4px; font-size:0.8em; margin-left:5px;">
                        <?php echo ucfirst($_SESSION['user_role']); ?>
                    </span>
                </div>
            </header>

            <div class="page-content">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Bem-vindo ao Proftest Board</h3>
                    </div>
                    <div class="card-body">
                        <p>Bem-vindo ao sistema de gestão inteligente de talentos.</p>

                        <?php if (hasRole('superadmin')): ?>
                            <div
                                style="margin-top:2rem; display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:1rem;">
                                <div class="card" style="margin:0; background:#f8fafc; border:none;">
                                    <h4>Empresas</h4>
                                    <p>Gerencie as empresas cadastradas.</p>
                                    <a href="admin_companies.php" class="btn btn-primary"
                                        style="font-size:0.8rem; margin-top:0.5rem;">Gerenciar</a>
                                </div>
                                <div class="card" style="margin:0; background:#f8fafc; border:none;">
                                    <h4>Configurações</h4>
                                    <p>Configure a API Key do OpenAI.</p>
                                    <a href="admin_settings.php" class="btn btn-primary"
                                        style="font-size:0.8rem; margin-top:0.5rem;">Acessar</a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (hasRole('admin')): ?>
                            <div style="margin-top:2rem;">
                                <h4>Ações Rápidas</h4>
                                <div style="display:flex; gap:1rem; margin-top:1rem;">
                                    <a href="company_users.php" class="btn btn-primary">Gerenciar Usuários</a>
                                    <a href="company_users.php?action=create" class="btn btn-outline"
                                        style="border-color:#ccc; color:#333;">Novo Usuário</a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (hasRole('user')): ?>
                            <div style="margin-top:2rem;">
                                <h4>Meus Assistentes</h4>
                                <div style="display:flex; gap:1rem; margin-top:1rem; flex-wrap:wrap;">
                                    <a href="app_onboarding.php" class="btn btn-primary"><i class="fa-solid fa-robot"></i>
                                        Onboarding Buddy</a>
                                    <a href="app_offboarding.php" class="btn btn-outline"
                                        style="border-color:#ccc; color:#333;"><i class="fa-solid fa-door-open"></i>
                                        Offboarding</a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>

</html>
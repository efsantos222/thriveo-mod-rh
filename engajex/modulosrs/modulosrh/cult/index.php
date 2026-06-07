<?php
require_once 'config/db.php';
requireAuth();
require_once 'includes/header.php';

// Redirect based on role
if ($_SESSION['role'] === 'superadmin') {
    $dashboardTitle = "Painel Administrativo";
    // Usually redirect to companies is better, but let's show a dashboard
} elseif ($_SESSION['role'] === 'manager') {
    $dashboardTitle = "Gestão da Cultura";
} else {
    $dashboardTitle = "Minha Área";
}
?>

<div class="text-center" style="padding: 4rem 0;">
    <h1>Bem-vindo, <?= htmlspecialchars($_SESSION['name']) ?></h1>
    <p style="color: var(--text-muted); font-size: 1.2rem; margin-bottom: 2rem;">
        <?= $dashboardTitle ?>
    </p>

    <div class="grid-3">
        <?php if ($_SESSION['role'] === 'superadmin'): ?>
            <a href="admin_companies.php" class="card"
                style="display: block; text-align: center; transition: transform 0.2s;">
                <h3 style="color: var(--primary)">Empresas</h3>
                <p>Gerenciar clientes</p>
            </a>
            <a href="admin_settings.php" class="card"
                style="display: block; text-align: center; transition: transform 0.2s;">
                <h3 style="color: var(--success)">Configurações</h3>
                <p>Chave API OpenAI</p>
            </a>
        <?php elseif ($_SESSION['role'] === 'manager'): ?>
            <a href="manager_users.php" class="card" style="display: block; text-align: center;">
                <h3 style="color: var(--primary)">Colaboradores</h3>
                <p>Gerenciar acessos</p>
            </a>
            <a href="manager_identity.php" class="card" style="display: block; text-align: center;">
                <h3 style="color: var(--success)">Identidade</h3>
                <p>Missão e Valores</p>
            </a>
            <a href="manager_culture.php" class="card" style="display: block; text-align: center;">
                <h3 style="color: #f59e0b">Cultura IA</h3>
                <p>Ver Relatório</p>
            </a>
        <?php else: ?>
            <a href="user_surveys.php" class="card" style="display: block; text-align: center; grid-column: 2;">
                <h3 style="color: var(--primary)">Pesquisas</h3>
                <p>Responder questionários</p>
            </a>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
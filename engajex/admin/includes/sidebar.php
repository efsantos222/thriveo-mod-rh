<?php
// admin/includes/sidebar.php
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <h3 class="sidebar-logo">Admin Panel</h3>
        <p class="sidebar-user">Administrador</p>
    </div>

    <nav class="sidebar-nav">
        <div class="sidebar-section">
            <p class="sidebar-section-title">Gerenciamento</p>
            <a href="dashboard.php"
                class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                Dashboard
            </a>
            <a href="companies.php"
                class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'companies.php' ? 'active' : ''; ?>">
                Empresas
            </a>
            <a href="users.php"
                class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                Usuários
            </a>
        </div>

        <div class="sidebar-section">
            <a href="../index.php" class="menu-item">
                ⬅️ Voltar ao App
            </a>
            <a href="../logout.php" class="menu-item danger">
                🚪 Sair
            </a>
        </div>
    </nav>
</aside>
<aside class="sidebar">
    <div class="sidebar-header">
        <i class="fa-solid fa-brain"></i> Proftest
    </div>
    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php"
                class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-chart-line"></i> Dashboard
            </a>
        </li>

        <?php if (hasRole('superadmin')): ?>
            <li class="menu-label">Administração</li>
            <li>
                <a href="admin_companies.php"
                    class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_companies.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-building"></i> Empresas
                </a>
            </li>
            <li>
                <a href="admin_settings.php"
                    class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_settings.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-gear"></i> Configurações (API)
                </a>
            </li>
        <?php endif; ?>

        <?php if (hasRole('admin')): // Company Admin ?>
            <li class="menu-label">Gestão</li>
            <li>
                <a href="company_users.php"
                    class="<?php echo basename($_SERVER['PHP_SELF']) == 'company_users.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-users"></i> Usuários
                </a>
            </li>
            <li>
                <a href="company_culture.php"
                    class="<?php echo basename($_SERVER['PHP_SELF']) == 'company_culture.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-sitemap"></i> Cultura & Identidade
                </a>
            </li>
            <li>
                <a href="company_knowledge.php"
                    class="<?php echo basename($_SERVER['PHP_SELF']) == 'company_knowledge.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-book-open"></i> Treinamento IA
                </a>
            </li>
            <li>
                <a href="surveys.php"
                    class="<?php echo basename($_SERVER['PHP_SELF']) == 'surveys.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-clipboard-question"></i> Pesquisas
                </a>
            </li>
        <?php endif; ?>

        <?php if (hasRole('admin') || hasRole('user')): ?>
            <li class="menu-label">Ferramentas IA</li>
            <li>
                <a href="app_onboarding.php"
                    class="<?php echo basename($_SERVER['PHP_SELF']) == 'app_onboarding.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-robot"></i> Onboarding Buddy
                </a>
            </li>
            <li>
                <a href="app_offboarding.php"
                    class="<?php echo basename($_SERVER['PHP_SELF']) == 'app_offboarding.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-comments"></i> Offboarding
                </a>
            </li>
            <li>
                <a href="app_knowledge.php"
                    class="<?php echo basename($_SERVER['PHP_SELF']) == 'app_knowledge.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-brain"></i> Knowledge Transfer
                </a>
            </li>
        <?php endif; ?>
    </ul>

    <div class="sidebar-footer">
        <a href="../logout.php"><i class="fa-solid fa-sign-out-alt"></i> Sair</a>
    </div>
</aside>
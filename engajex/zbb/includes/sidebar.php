<!-- includes/sidebar.php -->
<aside class="sidebar">
    <div class="mb-4">
        <h2 style="color: var(--primary-color); font-size: 1.5rem;">Thriveo ZBB</h2>
    </div>

    <nav>
        <div class="nav-item">
            <a href="dashboard.php" class="nav-link-dashboard <?php echo ($page === 'dashboard') ? 'active' : ''; ?>">
                <span class="nav-icon">📊</span> Dashboard
            </a>
        </div>

        <div class="nav-item">
            <a href="budget.php" class="nav-link-dashboard <?php echo ($page === 'budget') ? 'active' : ''; ?>">
                <span class="nav-icon">💰</span> Orçamento ZBB
            </a>
        </div>

        <div class="nav-item">
            <a href="reports.php" class="nav-link-dashboard <?php echo ($page === 'reports') ? 'active' : ''; ?>">
                <span class="nav-icon">📑</span> Relatórios
            </a>
        </div>

        <div class="nav-item">
            <a href="intelligence.php"
                class="nav-link-dashboard <?php echo ($page === 'intelligence') ? 'active' : ''; ?>">
                <span class="nav-icon">🧠</span> Inteli. Competitiva
            </a>
        </div>

        <?php if ($_SESSION['user_role'] === 'super_admin' || $_SESSION['user_role'] === 'admin'): ?>
            <div
                style="margin-top: 2rem; margin-bottom: 0.5rem; text-transform: uppercase; font-size: 0.75rem; color: var(--text-secondary); font-weight: 700;">
                Administração
            </div>

            <div class="nav-item">
                <a href="companies.php" class="nav-link-dashboard <?php echo ($page === 'companies') ? 'active' : ''; ?>">
                    <span class="nav-icon">🏢</span> Empresas
                </a>
            </div>

            <div class="nav-item">
                <a href="users.php" class="nav-link-dashboard <?php echo ($page === 'users') ? 'active' : ''; ?>">
                    <span class="nav-icon">👥</span> Usuários
                </a>
            </div>

            <div class="nav-item">
                <a href="settings.php" class="nav-link-dashboard <?php echo ($page === 'settings') ? 'active' : ''; ?>">
                    <span class="nav-icon">⚙️</span> Config. Sistema
                </a>
            </div>
        <?php endif; ?>
    </nav>

    <div style="margin-top: auto; padding-top: 2rem;">
        <a href="logout.php" class="nav-link-dashboard" style="color: var(--danger-color);">
            <span class="nav-icon">🚪</span> Sair
        </a>
    </div>
</aside>
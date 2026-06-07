<?php
// templates/admin/index.php
// Super Admin Dashboard Styling Update

// Fetch basic stats (Mockup counters for now, replace with real queries if needed)
$totalCompanies = $pdo->query("SELECT COUNT(*) FROM companies")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Check if companies exist to show guided setup
$hasCompanies = $totalCompanies > 0;

ob_start();
?>
<div class="mb-5">
    <h2 class="fw-bold text-dark">Visão Geral</h2>
    <p class="text-muted">Bem-vindo ao painel administrativo principal.</p>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm"
            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-white-50 small mb-1 fw-bold text-uppercase">Total Empresas</div>
                        <div class="display-5 fw-bold"><?php echo $totalCompanies; ?></div>
                    </div>
                    <div class="bg-white bg-opacity-25 rounded-circle p-3">
                        <i class="fas fa-building fa-2x text-white"></i>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-top border-white border-opacity-25 small">
                    <a href="?route=admin/companies" class="text-white text-decoration-none hover-opacity">
                        Gerenciar Empresas <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm bg-white">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small mb-1 fw-bold text-uppercase">Usuários Cadastrados</div>
                        <div class="display-5 fw-bold text-dark"><?php echo $totalUsers; ?></div>
                    </div>
                    <div class="bg-success bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-users fa-2x text-success"></i>
                    </div>
                </div>
                <!-- Optional Subtext -->
                <div class="mt-4 pt-3 border-top small text-muted">
                    Todas as organizações
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm bg-white">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small mb-1 fw-bold text-uppercase">Configuração IA</div>
                        <div class="fs-4 fw-bold text-dark mb-1">Status</div>
                        <span
                            class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Ativo</span>
                    </div>
                    <div class="bg-info bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-robot fa-2x text-info"></i>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-top small">
                    <a href="?route=admin/settings" class="text-decoration-none text-primary fw-medium">
                        Configurar Chave API <i class="fas fa-cog ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions / Alert -->
<?php if (!$hasCompanies): ?>
    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center p-4">
        <i class="fas fa-lightbulb fa-2x me-4 text-warning"></i>
        <div>
            <h5 class="alert-heading fw-bold">Primeiros Passos</h5>
            <p class="mb-0">Parece que você ainda não tem empresas cadastradas. Comece criando uma organização.</p>
            <a href="?route=admin/companies" class="btn btn-warning text-white mt-3 fw-bold">Criar Primeira Empresa</a>
        </div>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="mb-0 fw-bold">Atalhos Administrativos</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <a href="?route=admin/companies"
                        class="list-group-item list-group-item-action py-3 d-flex align-items-center border-0 rounded mb-2 bg-light-hover">
                        <div class="bg-primary bg-opacity-10 rounded p-2 me-3 text-primary">
                            <i class="fas fa-building"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark">Gerenciar Empresas</div>
                            <div class="small text-muted">Adicionar, editar ou remover organizações</div>
                        </div>
                        <i class="fas fa-chevron-right ms-auto text-muted"></i>
                    </a>
                    <a href="?route=courses/manage"
                        class="list-group-item list-group-item-action py-3 d-flex align-items-center border-0 rounded mb-2 bg-light-hover">
                        <div class="bg-info bg-opacity-10 rounded p-2 me-3 text-info">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark">Catálogo de Cursos</div>
                            <div class="small text-muted">Gerenciar conteúdos educacionais</div>
                        </div>
                        <i class="fas fa-chevron-right ms-auto text-muted"></i>
                    </a>
                    <a href="?route=admin/settings"
                        class="list-group-item list-group-item-action py-3 d-flex align-items-center border-0 rounded bg-light-hover">
                        <div class="bg-secondary bg-opacity-10 rounded p-2 me-3 text-secondary">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark">Configurações do Sistema</div>
                            <div class="small text-muted">API Keys e parâmetros globais</div>
                        </div>
                        <i class="fas fa-chevron-right ms-auto text-muted"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once TEMPLATES_PATH . '/base.php';
?>
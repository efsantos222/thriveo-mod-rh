<?php
ob_start();
?>
<div class="row">
    <div class="col-md-12 mb-4">
        <h2>Painel da Empresa</h2>
        <p class="lead">Bem-vindo, <?php echo htmlspecialchars($_SESSION['user_name']); ?>.</p>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card text-white bg-primary mb-3 h-100">
            <div class="card-header"><i class="fas fa-users"></i> Colaboradores</div>
            <div class="card-body">
                <h5 class="card-title">Gerenciar Equipe</h5>
                <p class="card-text">Cadastre, edite e gerencie o acesso dos seus colaboradores.</p>
                <a href="?route=company/users" class="btn btn-light btn-sm stretched-link">Acessar</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success mb-3 h-100">
            <div class="card-header"><i class="fas fa-bullseye"></i> Identidade</div>
            <div class="card-body">
                <h5 class="card-title">Identidade Organizacional</h5>
                <p class="card-text">Defina Missão, Visão, Valores e Propósito para alinhar os PDIs.</p>
                <a href="?route=company/identity" class="btn btn-light btn-sm stretched-link">Configurar</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-info mb-3 h-100">
            <div class="card-header"><i class="fas fa-chart-line"></i> Relatórios</div>
            <div class="card-body">
                <h5 class="card-title">PDIs e Progresso</h5>
                <p class="card-text">Visualize o andamento dos PDIs e avaliações da equipe.</p>
                <a href="?route=company/reports" class="btn btn-light btn-sm stretched-link">Ver Relatórios</a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = "Painel da Empresa";
require_once TEMPLATES_PATH . '/base.php';
?>
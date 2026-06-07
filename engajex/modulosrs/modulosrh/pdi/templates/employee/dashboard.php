<?php
ob_start();
?>
<div class="row">
    <div class="col-md-12 mb-4">
        <h2>Meu Painel</h2>
        <p class="lead">Olá, <?php echo htmlspecialchars($_SESSION['user_name']); ?>.</p>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card border-primary mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-clipboard-list text-primary"></i> Meu PDI</h5>
                <p class="card-text">Acesse seu Plano de Desenvolvimento Individual, acompanhe metas e ações.</p>
                <a href="?route=pdi" class="btn btn-primary">Acessar PDI</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-secondary mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-graduation-cap text-secondary"></i> Meus Cursos</h5>
                <p class="card-text">Veja os cursos sugeridos e em andamento para o seu desenvolvimento.</p>
                <a href="?route=courses" class="btn btn-outline-secondary">Ver Cursos</a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = "Meu Painel";
require_once TEMPLATES_PATH . '/base.php';
?>
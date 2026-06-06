<?php
$pageTitle = "Home";
require_once TEMPLATES_PATH . '/base.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="text-center mb-5">
                <h1>Portal de Desenvolvimento Profissional</h1>
                <p class="lead">Bem-vindo ao seu espaço de crescimento profissional</p>
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-tasks"></i> PDI
                            </h5>
                            <p class="card-text">Gerencie seu Plano de Desenvolvimento Individual.</p>
                            <a href="?route=pdi" class="btn btn-primary">Acessar PDI</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-graduation-cap"></i> Cursos
                            </h5>
                            <p class="card-text">Explore nossos cursos e treinamentos.</p>
                            <a href="?route=courses" class="btn btn-primary">Ver Cursos</a>
                        </div>
                    </div>
                </div>

                <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-cog"></i> Administração
                            </h5>
                            <p class="card-text">Gerencie usuários e configurações do sistema.</p>
                            <a href="?route=admin" class="btn btn-primary">Painel Admin</a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-user"></i> Perfil
                            </h5>
                            <p class="card-text">Atualize suas informações pessoais.</p>
                            <a href="?route=profile" class="btn btn-primary">Meu Perfil</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once TEMPLATES_PATH . '/footer.php'; ?>

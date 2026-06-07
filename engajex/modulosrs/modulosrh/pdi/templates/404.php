<?php
$pageTitle = "Página não encontrada";
require_once TEMPLATES_PATH . '/base.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <h1 class="display-1">404</h1>
            <h2>Página não encontrada</h2>
            <p>A página que você está procurando não existe ou foi movida.</p>
            <a href="?route=home" class="btn btn-primary">Voltar para Home</a>
        </div>
    </div>
</div>

<?php require_once TEMPLATES_PATH . '/footer.php'; ?>

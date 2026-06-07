<?php
// Guard
if (($_SESSION['user_role'] ?? '') !== 'company_admin') {
    die('Acesso negado');
}

ob_start();
?>
<h2>Relatórios da Empresa</h2>
<p>Em breve: Relatórios detalhados de progresso dos PDIs.</p>

<div class="alert alert-info">
    Funcionalidade em desenvolvimento. Aqui você poderá visualizar gráficos de competências e status dos PDIs da equipe.
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Relatórios';
require_once TEMPLATES_PATH . '/base.php';
?>
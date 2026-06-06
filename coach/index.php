<?php
session_start();
// Se já estiver logado, vai para o dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
// Se não, redireciona para a página de login/landing page moderna
header('Location: auth/login.php');
exit;
?>
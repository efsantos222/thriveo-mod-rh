<?php
session_start();

// Verificar se o usuário está autenticado e tem permissão para o teste BigFive
if (!isset($_SESSION['email']) || !isset($_SESSION['pending_tests']) || !in_array('bigfive', $_SESSION['pending_tests'])) {
    header('Location: login.php');
    exit;
}

// Configurar a sessão para o teste BigFive
$_SESSION['authenticated'] = true;
$_SESSION['user_type'] = 'bigfive';
$_SESSION['questao_atual'] = 0;

// Redirecionar para a página de questões BigFive
header('Location: question_bigfive.php');
exit;

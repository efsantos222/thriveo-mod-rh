<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['email']) || !isset($_SESSION['pending_tests'])) {
    header('Location: login.php');
    exit;
}

// Verificar se o tipo de teste foi enviado
if (!isset($_POST['test_type'])) {
    header('Location: select_test.php');
    exit;
}

$test_type = $_POST['test_type'];
$pending_tests = $_SESSION['pending_tests'];

// Verificar se o teste está na lista de pendentes
if (!in_array($test_type, $pending_tests)) {
    header('Location: select_test.php');
    exit;
}

// Configurar a sessão para o teste selecionado
$_SESSION['authenticated'] = true;
$_SESSION['user_type'] = $test_type;
$_SESSION['questao_atual'] = 0;

// Redirecionar para o teste apropriado
$test_pages = [
    'disc' => 'test_disc.php',
    'mbti' => 'test_mbti.php',
    'bigfive' => 'test_bigfive.php',
    'jss' => 'test_jss.php'
];

if (!isset($test_pages[$test_type])) {
    header('Location: select_test.php');
    exit;
}

header('Location: ' . $test_pages[$test_type]);
exit;

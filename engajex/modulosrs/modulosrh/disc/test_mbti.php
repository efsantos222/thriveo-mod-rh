<?php
session_start();

// Verificar se o usuário está autenticado
if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated'] || $_SESSION['user_type'] !== 'mbti') {
    header('Location: login.php');
    exit;
}

// Verificar se o teste já foi concluído
if (isset($_SESSION['user_data']['status']) && strtolower($_SESSION['user_data']['status']) === 'concluído') {
    header('Location: login.php?error=test_completed');
    exit;
}

// Redirecionar para a página de questões MBTI
header('Location: mbti/mbti_question.php');
exit;

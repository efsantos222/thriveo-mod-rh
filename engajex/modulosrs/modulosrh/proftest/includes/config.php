<?php
// Configurações do sistema
define('DISC_TEST_PATH', 'question.php');
define('MBTI_TEST_PATH', 'mbti/test.php');
define('DISC_RESULTS_PATH', 'view_result.php');
define('MBTI_RESULTS_PATH', 'mbti/view_results.php');

// Função para obter o caminho do teste com base no tipo
function getTestPath($userType) {
    return $userType === 'mbti' ? MBTI_TEST_PATH : DISC_TEST_PATH;
}

// Função para obter o caminho dos resultados com base no tipo
function getResultsPath($userType) {
    return $userType === 'mbti' ? MBTI_RESULTS_PATH : DISC_RESULTS_PATH;
}

// Função para verificar se o usuário já completou o teste
function hasCompletedTest($email, $userType) {
    $avaliacao_file = 'resultados/' . str_replace(['@', '.'], '_', $email) . 
                      ($userType === 'mbti' ? '_avaliacao_mbti.csv' : '_avaliacao.csv');
    return file_exists($avaliacao_file);
}

// Função para redirecionar o usuário para a página correta
function redirectToCorrectPage($userType, $email) {
    if (hasCompletedTest($email, $userType)) {
        header('Location: ' . getResultsPath($userType));
    } else {
        header('Location: ' . getTestPath($userType));
    }
    exit;
}

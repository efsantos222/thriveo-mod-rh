<?php
require_once '../config/config.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/public/');
}

$codigo = sanitize($_POST['codigo'] ?? '');

if (empty($codigo)) {
    $_SESSION['error'] = "Por favor, insira um código.";
    redirect('/public/');
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Verificar se o código existe e está ativo
    $stmt = $db->prepare("SELECT id FROM respondentes WHERE codigo = ? AND ativo = 1");
    $stmt->execute([$codigo]);
    $respondente = $stmt->fetch();

    if (!$respondente) {
        $_SESSION['error'] = "Código inválido ou inativo.";
        redirect('/public/');
    }

    // Verificar se já respondeu a pesquisa da semana atual
    $stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM respostas r 
        WHERE r.id_respondente = ? 
        AND YEARWEEK(r.data_resposta) = YEARWEEK(NOW())
    ");
    $stmt->execute([$respondente['id']]);
    $jaRespondeu = $stmt->fetch()['total'] > 0;

    if ($jaRespondeu) {
        $_SESSION['error'] = "Você já respondeu a pesquisa desta semana.";
        redirect('/public/');
    }

    // Se tudo estiver ok, criar sessão e redirecionar para a pesquisa
    $_SESSION['respondente_id'] = $respondente['id'];
    redirect('/public/survey.php');

} catch (PDOException $e) {
    $_SESSION['error'] = "Erro ao validar código. Tente novamente.";
    redirect('/public/');
}

<?php
session_start();
require_once 'config.php';

// Registrar logout no log de atividades
if (isset($_SESSION[SESSION_NAME]['id'])) {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("INSERT INTO logs_atividades (usuario_id, acao, ip_address, user_agent) VALUES (?, 'logout', ?, ?)");
        $stmt->execute([$_SESSION[SESSION_NAME]['id'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);
    } catch(PDOException $e) {
        error_log($e->getMessage());
    }
}

// Destruir sessão
session_destroy();

// Redirecionar para login
header('Location: login.php');
exit();
?>

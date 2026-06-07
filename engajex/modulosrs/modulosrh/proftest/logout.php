<?php
session_start();
session_destroy();

// Limpar todas as variáveis de sessão
$_SESSION = array();

// Se houver um cookie de sessão, destruí-lo
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// URL do sistema
$home_url = "https://proftest.com.br/disc";
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    // Se não estiver em HTTPS, usar index.php local
    $home_url = "index.php";
}

// Redirecionar para a página inicial
header('Location: ' . $home_url);
exit;

<?php
// config.php

// ATIVAR EXIBIÇÃO DE ERROS (Para Debug)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$db_host = 'localhost';
$db_name = 'efsantos_v2m';
$db_user = 'efsantos_v2m';
$db_pass = 'Kyew1802';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

// Configurações globais
define('SITE_NAME', 'V2MOM Intelligence');
// Ajuste dinâmico ou manual da URL base
define('BASE_URL', 'https://testprof.com.br/v2m');

// Funções auxiliares básicas
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isAdmin()
{
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

function redirect($url)
{
    if (!headers_sent()) {
        header("Location: $url");
    } else {
        echo "<script>window.location.href='$url';</script>";
    }
    exit;
}

// Inicia sessão apenas se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
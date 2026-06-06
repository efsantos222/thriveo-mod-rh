<?php
// modulostd/unia/config/db.php
// Bridge to UniA Database while maintaining connection to main system

$host = 'localhost';
$db_name = 'efsantos_unia';
$username = 'efsantos_unia';
$password = 'Kyew@1802';

try {
    $pdo_unia = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $pdo_unia->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo_unia->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Also point the default $pdo to this if needed by UniA internally
    $pdo = $pdo_unia; 
} catch(PDOException $e) {
    die("Erro na conexão com o banco de dados UniA.");
}

// Load main system config for session and helpers
$mainRoot = dirname(dirname(dirname(__DIR__)));
require_once $mainRoot . '/config.php';
?>

<?php
// modulosrh/entrev/config.php
// Bridge to Thriveo Entrev Database while maintaining connection to main system

$host = 'localhost';
$dbname = 'efsantos_entrev';
$username = 'efsantos_entrev';
$password = 'Kyew@1802';

try {
    $pdo_entrev = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo_entrev->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo_entrev->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // For internal backward compatibility if files expect $pdo
    $pdo = $pdo_entrev;
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados Entrevista.");
}

// Load main system config for session and helpers
$mainRoot = dirname(dirname(__DIR__));
require_once $mainRoot . '/config.php';
?>

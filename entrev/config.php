<?php
// config.php
$host = 'localhost'; // Usually localhost for shared hosting
$dbname = 'efsantos_entrev';
$username = 'efsantos_entrev';
$password = 'Kyew1802';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados. Verifique o config.php");
}

session_start();
?>

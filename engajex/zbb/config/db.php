<?php
// config/db.php

$host = 'localhost';
$db_name = 'efsantos_zbb';
$username = 'efsantos_zbb';
$password = 'Kyew@1802';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // In production, log this error instead of echoing it
    // error_log("Connection failed: " . $e->getMessage());
    die("Erro de conexão com o banco de dados. Por favor, contate o administrador.");
}
?>
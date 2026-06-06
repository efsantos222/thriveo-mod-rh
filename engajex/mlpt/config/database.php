<?php
$host = '127.0.0.1';
$db_name = 'efsantos_mlpt';
$username = 'efsantos_mlpt';
$password = 'Kyew1802';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // In production, log this error instead of showing it
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>
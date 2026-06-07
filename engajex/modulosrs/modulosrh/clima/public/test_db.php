<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$dbname = 'efsantos_clima';
$user = 'efsantos_clima';
$pass = 'Kyew1802';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Conexão com o banco de dados estabelecida com sucesso!";
} catch(PDOException $e) {
    echo "Erro: " . $e->getMessage();
}

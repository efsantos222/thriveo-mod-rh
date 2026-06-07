<?php
$host = 'localhost';
$db = 'efsantos_gestaocargos';
$user = 'efsantos_gestaocargos';
$pass = 'Kyew1802';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Conexão falhou: ' . $e->getMessage();
}
?>

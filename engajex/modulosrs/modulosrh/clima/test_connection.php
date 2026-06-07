<?php
$host = 'localhost';
$dbname = 'efsantos_clima';
$user = 'efsantos_clima';
$pass = 'Kyew1802';

echo "Testando conexão com MySQL...<br>";
echo "Host: $host<br>";
echo "Database: $dbname<br>";
echo "Usuário: $user<br><br>";

// Teste 1: mysqli
echo "Teste 1 - mysqli:<br>";
try {
    $mysqli = new mysqli($host, $user, $pass, $dbname);
    if ($mysqli->connect_errno) {
        throw new Exception($mysqli->connect_error);
    }
    echo "✅ Conexão mysqli bem sucedida!<br><br>";
    $mysqli->close();
} catch (Exception $e) {
    echo "❌ Erro mysqli: " . $e->getMessage() . "<br><br>";
}

// Teste 2: PDO
echo "Teste 2 - PDO:<br>";
try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Conexão PDO bem sucedida!<br><br>";
} catch (PDOException $e) {
    echo "❌ Erro PDO: " . $e->getMessage() . "<br>";
}

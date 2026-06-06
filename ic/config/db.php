<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'efsantos_ic');
define('DB_PASS', 'Kyew1802');
define('DB_NAME', 'efsantos_ic');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // In production, log this instead of showing it
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>
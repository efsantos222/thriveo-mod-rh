<?php
// Configurações gerais do sistema
define('APP_NAME', 'TestProf Hardskill');
define('APP_URL', 'https://testprof.com.br/hardskill'); // Ajustar conforme ambiente

// Configuração do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'efsantos_hardskill');
define('DB_USER', 'efsantos_hardskill');
define('DB_PASS', 'Kyew1802');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

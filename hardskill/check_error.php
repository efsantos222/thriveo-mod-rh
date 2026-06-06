<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnóstico do Sistema</h1>";

// 1. Testar PHP
echo "<p>Versão do PHP: " . phpversion() . "</p>";

// 2. Testar Inclusão
echo "<p>Testando inclusão de config/database.php... ";
$configFile = __DIR__ . '/config/database.php';
if (file_exists($configFile)) {
    echo "<span style='color:green'>Arquivo existe.</span></p>";
    try {
        require_once $configFile;
        echo "<p style='color:green'>Configuração carregada com sucesso.</p>";
    } catch (Exception $e) {
        echo "<p style='color:red'>Erro ao carregar config: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<span style='color:red'>Arquivo NÃO encontrado! Caminho: $configFile</span></p>";
}

// 3. Testar Conexão Banco
echo "<p>Testando conexão com banco de dados... ";
if (defined('DB_HOST')) {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        echo "<span style='color:green'>Conexão BEM SUCEDIDA!</span></p>";
    } catch (PDOException $e) {
        echo "<span style='color:red'>FALHA NA CONEXÃO: " . $e->getMessage() . "</span></p>";
    }
} else {
    echo "<span style='color:red'>Constantes de banco não definidas.</span></p>";
}

// 4. Testar Permissões de Pasta (Sessão)
echo "<p>Testando iniciação de sessão... ";
try {
    session_start();
    echo "<span style='color:green'>Sessão iniciada OK.</span></p>";
} catch (Exception $e) {
    echo "<span style='color:red'>Erro de sessão: " . $e->getMessage() . "</span></p>";
}

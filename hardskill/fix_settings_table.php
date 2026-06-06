<?php
// fix_settings_table.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/database.php';

echo "<h1>Diagnóstico e Correção: Configurações</h1>";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Criar Tabela se não existir
    $sql = "CREATE TABLE IF NOT EXISTS settings (
        setting_key VARCHAR(50) PRIMARY KEY,
        setting_value TEXT
    )";
    $pdo->exec($sql);
    echo "<p style='color:green'>1. Tabela 'settings' verificada/criada com sucesso.</p>";

    // 2. Garantir que a chave existe (Insert Ignore)
    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('openai_api_key', '')");
    $stmt->execute();
    echo "<p style='color:green'>2. Registro padrão de API Key verificado.</p>";

    // 3. Testar Leitura
    $stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'openai_api_key'");
    $val = $stmt->fetchColumn();
    echo "<p style='color:green'>3. Teste de leitura OK. Valor atual: " . htmlspecialchars($val) . "</p>";

    echo "<h3>Tudo pronto! Tente acessar o menu Configurações novamente.</h3>";

} catch (PDOException $e) {
    echo "<h3 style='color:red'>ERRO NO BANCO DE DADOS: " . $e->getMessage() . "</h3>";
    echo "<p>Verifique se o usuário do banco tem permissão CREATE.</p>";
}

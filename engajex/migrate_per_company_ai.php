<?php
// migrate_per_company_ai.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

echo "<h2>🔧 Migração do Banco de Dados</h2>";

try {
    // 1. Alter companies table - Check if column exists first
    $stmt = $pdo->query("SHOW COLUMNS FROM companies LIKE 'openai_api_key'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE companies ADD COLUMN openai_api_key VARCHAR(255) DEFAULT NULL");
        echo "✅ Coluna <strong>openai_api_key</strong> adicionada à tabela <code>companies</code>.<br>";
    } else {
        echo "ℹ️ Coluna <strong>openai_api_key</strong> já existe na tabela <code>companies</code>.<br>";
    }

    // 2. Alter ss_users table - Check if column exists first
    $stmt = $pdo->query("SHOW COLUMNS FROM ss_users LIKE 'company_id'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE ss_users ADD COLUMN company_id INT DEFAULT NULL");
        echo "✅ Coluna <strong>company_id</strong> adicionada à tabela <code>ss_users</code>.<br>";
    } else {
        echo "ℹ️ Coluna <strong>company_id</strong> já existe na tabela <code>ss_users</code>.<br>";
    }

    // 3. Sync existing recruiters to their companies
    $affected = $pdo->exec("
        UPDATE ss_users s
        JOIN users u ON s.email = u.email
        SET s.company_id = u.company_id
        WHERE s.company_id IS NULL
    ");
    echo "✅ Sincronização de <strong>company_id</strong> realizada para $affected registros.<br>";

    echo "<h3>🎉 Migração Concluída com Sucesso!</h3>";
    echo "<p><a href='company_settings.php'>Ir para Configurações da Empresa</a></p>";

} catch (Exception $e) {
    echo "<p style='color:red'>❌ Erro na migração: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>

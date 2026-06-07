<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

echo "<h2>Atualizando Tabela Mood Tracker...</h2>";

try {
    // 1. Adicionar coluna company_id
    echo "<p>Tentando adicionar coluna 'company_id'...</p>";
    $pdo->exec("ALTER TABLE mood_tracker ADD COLUMN company_id INT NOT NULL DEFAULT 1");
    echo "<p style='color:green'>Sucesso: Coluna adicionada.</p>";

    // 2. Adicionar índice para performance
    $pdo->exec("CREATE INDEX idx_mood_company ON mood_tracker(company_id)");
    echo "<p style='color:green'>Sucesso: Índice criado.</p>";

} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "<p style='color:blue'>Info: A coluna já existe.</p>";
    } else {
        echo "<p style='color:red'>Erro SQL: " . $e->getMessage() . "</p>";
    }
}

echo "<h3>Concluído. Pode voltar ao <a href='modules/mood.php'>Registro de Humor</a>.</h3>";
?>
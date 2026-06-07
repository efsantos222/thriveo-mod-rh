<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

try {
    // Add columns for Search API
    $pdo->exec("ALTER TABLE ai_config ADD COLUMN search_provider ENUM('google', 'serper') DEFAULT 'google'");
    $pdo->exec("ALTER TABLE ai_config ADD COLUMN search_api_key VARCHAR(255) NULL");
    $pdo->exec("ALTER TABLE ai_config ADD COLUMN search_cx VARCHAR(255) NULL");

    echo "Tabela 'ai_config' atualizada com campos de busca (search_api_key, search_cx).";

} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column") !== false) {
        echo "Colunas já existem.";
    } else {
        echo "Erro SQL: " . $e->getMessage();
    }
}
?>
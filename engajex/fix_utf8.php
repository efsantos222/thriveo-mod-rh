<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

try {
    // Attempt to convert table to utf8mb4
    $pdo->exec("ALTER TABLE market_intelligence CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Tabela 'market_intelligence' convertida para utf8mb4.";
} catch (PDOException $e) {
    echo "Erro ao converter tabela: " . $e->getMessage();

    // Fallback: If conversion fails, maybe modify just the column
    try {
        $pdo->exec("ALTER TABLE market_intelligence MODIFY content TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "<br>Coluna 'content' modificada para utf8mb4.";
    } catch (PDOException $e2) {
        echo "<br>Erro ao modificar coluna: " . $e2->getMessage();
    }
}
?>
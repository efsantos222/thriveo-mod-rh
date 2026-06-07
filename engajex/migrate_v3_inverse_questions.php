<?php
// migrate_v3_inverse_questions.php
require_once 'config.php';

try {
    // Check if column exists
    $check = $pdo->query("SHOW COLUMNS FROM ss_questions LIKE 'is_inverse'");
    if ($check->rowCount() == 0) {
        $pdo->exec("ALTER TABLE ss_questions ADD COLUMN is_inverse BOOLEAN DEFAULT 0");
        echo "✅ Coluna is_inverse adicionada à tabela ss_questions.<br>";
    } else {
        echo "ℹ️ A coluna is_inverse já existe.<br>";
    }
    echo "<h3>Migração Concluída!</h3>";
} catch (Exception $e) {
    echo "❌ Erro na migração: " . $e->getMessage();
}
?>

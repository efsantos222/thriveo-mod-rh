<?php
// fix_ss_tables.php
require_once 'config.php';

try {
    // Aumentar o tamanho das colunas para suportar os novos testes
    $pdo->exec("ALTER TABLE ss_questions MODIFY COLUMN test_type VARCHAR(100)");
    $pdo->exec("ALTER TABLE ss_questions MODIFY COLUMN dimension VARCHAR(50)");
    
    $pdo->exec("ALTER TABLE ss_test_assignments MODIFY COLUMN test_type VARCHAR(100)");
    
    echo "✅ Estrutura de tabelas atualizada com sucesso!<br>";
    echo "Agora você pode rodar o seed_tests.php novamente.";
} catch (Exception $e) {
    echo "❌ Erro ao atualizar tabelas: " . $e->getMessage();
}
?>

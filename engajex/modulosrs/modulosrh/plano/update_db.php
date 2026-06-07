<?php
require_once 'config/db.php';

try {
    $pdo->exec("ALTER TABLE portfolio_services ADD COLUMN strategic_plan TEXT DEFAULT NULL");
    echo "Tabela atualizada com sucesso! Coluna 'strategic_plan' adicionada.";
} catch (PDOException $e) {
    if ($e->getCode() == '42S21') { // Duplicate column error
        echo "A coluna 'strategic_plan' já existe.";
    } else {
        echo "Erro ao atualizar tabela: " . $e->getMessage();
    }
}
?>
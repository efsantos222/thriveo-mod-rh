<?php
require_once 'config.php';

try {
    $stmt = $pdo->query("SHOW COLUMNS FROM gamification_goals LIKE 'challenges_description'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE gamification_goals ADD COLUMN challenges_description TEXT DEFAULT NULL");
        echo "Coluna 'challenges_description' adicionada em 'gamification_goals'.<br>";
    }
    echo "Banco de dados atualizado.";
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
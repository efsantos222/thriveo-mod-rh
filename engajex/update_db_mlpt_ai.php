<?php
require_once 'config.php';

try {
    // Adicionar coluna para salvar o resumo da IA
    $pdo->exec("ALTER TABLE mlpt_pulse_surveys ADD COLUMN ai_summary TEXT DEFAULT NULL");
    echo "Coluna ai_summary adicionada com sucesso em mlpt_pulse_surveys.";
} catch (PDOException $e) {
    echo "Nota: " . $e->getMessage(); // Provavelmente já existe, ignorar.
}
?>
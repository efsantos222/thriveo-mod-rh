<?php
require_once 'config.php';

try {
    // Tabela para Documentos (Culture Audit)
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `mlpt_documents` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company_id` int(11) NOT NULL,
      `title` varchar(255) NOT NULL,
      `content` text,
      `analysis_result` text,
      `created_at` timestamp DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    echo "Tabela mlpt_documents criada com sucesso.";

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
<?php
require_once 'config.php';

try {
    // Add ombudsman_user_id to companies
    try {
        $pdo->exec("ALTER TABLE companies ADD COLUMN ombudsman_user_id INT NULL");
        echo "Coluna 'ombudsman_user_id' adicionada à tabela companies.<br>";
    } catch (PDOException $e) {
        // Ignore if exists
    }

    // Create reports table
    $sql = "
    CREATE TABLE IF NOT EXISTS `ombudsman_reports` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company_id` int(11) NOT NULL,
      `sender_id` int(11) DEFAULT NULL,
      `type` enum('denuncia', 'reclamacao', 'sugestao') NOT NULL,
      `message` text NOT NULL,
      `is_anonymous` boolean DEFAULT false,
      `created_at` timestamp DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $pdo->exec($sql);
    echo "Tabela 'ombudsman_reports' criada com sucesso.<br>";

    // Remove old placeholder file
    if (file_exists('ouvidoria_config.php')) {
        // We will overwrite it, no need to delete
    }

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
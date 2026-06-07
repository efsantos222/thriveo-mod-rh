<?php
require_once 'config.php';

try {
    // Ensure feedbacks table exists with correct structure
    $sql = "
    CREATE TABLE IF NOT EXISTS `feedbacks` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company_id` int(11) NOT NULL,
      `sender_id` int(11) NOT NULL,
      `receiver_id` int(11) NOT NULL,
      `message` text NOT NULL,
      `created_at` timestamp DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `idx_company` (`company_id`),
      KEY `idx_sender` (`sender_id`),
      KEY `idx_receiver` (`receiver_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $pdo->exec($sql);
    echo "Tabela 'feedbacks' verificada/criada com sucesso.<br>";

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
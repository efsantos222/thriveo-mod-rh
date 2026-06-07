<?php
require_once 'config.php';

try {
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `meetup_entries` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      `role_name` varchar(100) NOT NULL COMMENT 'Cargo cadastrado pelo RH',
      `manager` varchar(100) DEFAULT NULL,
      `client` varchar(100) DEFAULT NULL,
      `projects` text DEFAULT NULL,
      `education` varchar(255) DEFAULT NULL,
      `city` varchar(100) DEFAULT NULL,
      `hobby` varchar(255) DEFAULT NULL,
      `status` enum('pending', 'completed', 'presented') DEFAULT 'pending',
      `created_at` timestamp DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_user_meetup` (`company_id`, `user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    echo "Tabela 'meetup_entries' criada com sucesso.";

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
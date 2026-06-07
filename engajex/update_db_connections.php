<?php
require_once 'config.php';

try {
    // Participants Table
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `connections_participants` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company_id` int(11) NOT NULL,
      `name` varchar(255) NOT NULL,
      `status` enum('pending', 'completed') DEFAULT 'pending',
      `created_at` timestamp DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Items (Questions) Table
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `connections_items` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company_id` int(11) NOT NULL,
      `content` text NOT NULL,
      `is_active` boolean DEFAULT true,
      `created_at` timestamp DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Config Table (Simple Key-Value per company)
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `connections_config` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company_id` int(11) NOT NULL,
      `config_key` varchar(50) NOT NULL,
      `config_value` varchar(255) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `comp_key` (`company_id`, `config_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Insert some default questions if none exist for the company? 
    // We'll handle that in the PHP logic to avoid complex SQL here.

    echo "Tabelas do módulo 'Conexões Virtuais' atualizadas com sucesso.<br>";

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
<?php
require_once 'config.php';

try {
    // History Events Table
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `history_events` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company_id` int(11) NOT NULL,
      `title` varchar(255) NOT NULL,
      `description` text,
      `year_label` varchar(50) NOT NULL COMMENT 'ex: 1996 or 1997-2005',
      `correct_order` int(11) NOT NULL,
      `is_active` boolean DEFAULT true,
      `created_at` timestamp DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `idx_company_order` (`company_id`, `correct_order`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // History Scores (Optional logic for tracking user sessions)
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `history_scores` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      `score` int(11) NOT NULL,
      `errors` int(11) NOT NULL DEFAULT 0,
      `created_at` timestamp DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    echo "Tabelas do módulo 'História' atualizadas.<br>";

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
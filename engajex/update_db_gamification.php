<?php
require_once 'config.php';

try {
    // 1. Table for Monthly Goals/Rewards
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `gamification_goals` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company_id` int(11) NOT NULL,
      `month` int(2) NOT NULL,
      `year` int(4) NOT NULL,
      `target_points` int(11) NOT NULL DEFAULT 5000,
      `reward_description` text,
      `is_active` boolean DEFAULT true,
      `created_at` timestamp DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_goal` (`company_id`, `month`, `year`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    echo "Tabela 'gamification_goals' criada/verificada.<br>";

    // 2. Table for User Points (Ledger style is best, but let's do a simple summary for now)
    // Actually, let's create a ledger so we can sum points by month easily.
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `gamification_ledger` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      `points` int(11) NOT NULL,
      `action_type` varchar(50) NOT NULL, -- e.g. 'feedback_sent', 'mood_logged'
      `reference_id` int(11) DEFAULT NULL, -- ID of the feedback/mood
      `created_at` timestamp DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `idx_user_date` (`user_id`, `created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    echo "Tabela 'gamification_ledger' criada/verificada.<br>";

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
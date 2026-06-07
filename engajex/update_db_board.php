<?php
require_once 'config.php';

try {
    // 1. Board Intellect (Cultura, Missão, etc)
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `board_intellect` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company_id` int(11) NOT NULL,
      `proposito` text,
      `missao` text,
      `visao` text,
      `principios` text,
      `valores` text,
      `cultura_organizacional` text,
      `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_company` (`company_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 2. Chat Sessions (Onboarding/Offboarding)
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `board_chat_sessions` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `company_id` int(11) NOT NULL,
      `type` enum('onboarding', 'offboarding', 'knowledge_transfer') NOT NULL,
      `started_at` timestamp DEFAULT current_timestamp(),
      `status` enum('active', 'completed') DEFAULT 'active',
      `summary` text,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 3. Chat Messages
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `board_chat_messages` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `session_id` int(11) NOT NULL,
      `sender` enum('user', 'ai') NOT NULL,
      `message` text NOT NULL,
      `created_at` timestamp DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 4. Knowledge Base (Base de Conhecimento)
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `board_knowledge` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `company_id` int(11) NOT NULL,
        `category` enum('general', 'onboarding', 'offboarding') DEFAULT 'general',
        `title` varchar(255) NOT NULL,
        `content` text NOT NULL,
        `created_at` timestamp DEFAULT current_timestamp(),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    echo "Tabelas do módulo Board criadas com sucesso.";

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
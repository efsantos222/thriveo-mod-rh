<?php
require_once 'config.php';

try {
    // 1. Matutiry Assessments (GPTW Type)
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `mlpt_maturity` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company_id` int(11) NOT NULL,
      `credibility_score` int(11) DEFAULT 0,
      `respect_score` int(11) DEFAULT 0,
      `impartiality_score` int(11) DEFAULT 0,
      `pride_score` int(11) DEFAULT 0,
      `camaraderie_score` int(11) DEFAULT 0,
      `overall_score` float DEFAULT 0,
      `assessment_date` timestamp DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 2. Action Plans
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `mlpt_actions` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company_id` int(11) NOT NULL,
      `title` varchar(255) NOT NULL,
      `details` text,
      `status` enum('pending', 'in_progress', 'completed') DEFAULT 'pending',
      `created_at` timestamp DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 3. Pulse Surveys (NPS)
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `mlpt_pulse_surveys` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company_id` int(11) NOT NULL,
      `title` varchar(255) DEFAULT 'Pesquisa de Pulso',
      `status` enum('active', 'closed') DEFAULT 'active',
      `created_at` timestamp DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `mlpt_pulse_responses` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `survey_id` int(11) NOT NULL,
      `rating` int(11) NOT NULL,
      `comment` text,
      `created_at` timestamp DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      FOREIGN KEY (`survey_id`) REFERENCES `mlpt_pulse_surveys`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    echo "Tabelas MLPT (Maturidade e Pulso) criadas com sucesso.";

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
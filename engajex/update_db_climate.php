<?php
require_once 'config.php';

try {
    // 1. Questions Table
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `climate_questions` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company_id` int(11) NOT NULL,
      `question_text` text NOT NULL,
      `category` varchar(50) DEFAULT 'Geral',
      `created_at` timestamp DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 2. Respondents (Access Codes)
    // 'label' is for admin reference (e.g. "Employee 1"), optional, not shown to respondent
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `climate_codes` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company_id` int(11) NOT NULL,
      `access_code` varchar(20) NOT NULL,
      `area` varchar(100) NOT NULL COMMENT 'Area vinculada anonimamente',
      `admin_label` varchar(100) DEFAULT NULL COMMENT 'Controle interno do RH',
      `is_used` boolean DEFAULT false,
      `created_at` timestamp DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_code` (`access_code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 3. Answers
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `climate_answers` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company_id` int(11) NOT NULL,
      `code_id` int(11) NOT NULL,
      `question_id` int(11) NOT NULL,
      `score` int(11) NOT NULL COMMENT '1 to 5',
      `comment` text DEFAULT NULL,
      `created_at` timestamp DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Populate Default Questions
    $check = $pdo->query("SELECT count(*) FROM climate_questions");
    if ($check->fetchColumn() == 0) {
        $defaults = [
            "Sinto que tenho clareza sobre minhas responsabilidades e metas.",
            "Recebo feedback frequente e construtivo do meu gestor.",
            "Tenho as ferramentas necessárias para realizar meu trabalho com eficiência.",
            "Sinto que a empresa valoriza o bem-estar dos colaboradores.",
            "Recomendaria esta empresa como um ótimo lugar para trabalhar."
        ];
        $stmt = $pdo->prepare("INSERT INTO climate_questions (company_id, question_text) VALUES (1, ?)");
        foreach ($defaults as $q) {
            $stmt->execute([$q]);
        }
        echo "Perguntas padrão inseridas.<br>";
    }

    echo "Tabelas de Pesquisa de Clima criadas.";

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
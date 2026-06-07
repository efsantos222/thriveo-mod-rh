<?php
require_once 'config.php';

try {
    // 1. Phrases Pool
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `bingo_phrases` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company_id` int(11) NOT NULL,
      `content` varchar(255) NOT NULL,
      `created_at` timestamp DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 2. Game State / Drawn Phrases History
    // We store individual draws to track order
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `bingo_draws` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company_id` int(11) NOT NULL,
      `phrase_id` int(11) NOT NULL,
      `drawn_at` timestamp DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_draw` (`company_id`, `phrase_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 3. Players and their Cards
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `bingo_players` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company_id` int(11) NOT NULL,
      `name` varchar(100) NOT NULL,
      `card_json` TEXT NOT NULL COMMENT 'JSON array of phrase IDs',
      `created_at` timestamp DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Seed some default corporate phrases if empty
    $check = $pdo->query("SELECT count(*) FROM bingo_phrases");
    if ($check->fetchColumn() == 0) {
        $defaults = [
            "Tá no mudo!",
            "Caiu minha conexão",
            "Pode ver minha tela?",
            "Vamos alinhar offline",
            "Novo normal",
            "Desculpa o atraso",
            "Manda invite",
            "Feedback construtivo",
            "Sinergia",
            "Pensar fora da caixa",
            "ASAP",
            "Deadline",
            "Call rápida",
            "Vou compartilhar a tela",
            "Estão me ouvindo?",
            "Travou aqui",
            "Próximos passos",
            "Gerar valor",
            "Mindset",
            "Soft skills"
        ];
        $stmt = $pdo->prepare("INSERT INTO bingo_phrases (company_id, content) VALUES (1, ?)");
        foreach ($defaults as $phrase) {
            $stmt->execute([$phrase]);
        }
        echo "Frases padrão inseridas.<br>";
    }

    echo "Tabelas do Bingo criadas com sucesso.";

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
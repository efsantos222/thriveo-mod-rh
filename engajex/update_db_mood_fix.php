<?php
require_once 'config.php';

try {
    // 1. Check if table 'mood_tracker' exists
    $tableExists = $pdo->query("SHOW TABLES LIKE 'mood_tracker'")->rowCount() > 0;

    if (!$tableExists) {
        $sql = "
        CREATE TABLE IF NOT EXISTS `mood_tracker` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `company_id` int(11) NOT NULL,
          `user_id` int(11) NOT NULL,
          `mood_level` int(1) NOT NULL,
          `note` text DEFAULT NULL,
          `created_at` timestamp DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          KEY `idx_user` (`user_id`),
          KEY `idx_company` (`company_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        $pdo->exec($sql);
        echo "Tabela 'mood_tracker' criada.<br>";
    } else {
        // 2. Check for 'mood_level' column vs 'mood'
        $stmt = $pdo->query("SHOW COLUMNS FROM mood_tracker LIKE 'mood_level'");
        $hasMoodLevel = ($stmt->rowCount() > 0);

        $stmt = $pdo->query("SHOW COLUMNS FROM mood_tracker LIKE 'mood'");
        $hasMood = ($stmt->rowCount() > 0);

        if ($hasMood && !$hasMoodLevel) {
            // Rename 'mood' to 'mood_level'
            $pdo->exec("ALTER TABLE mood_tracker CHANGE COLUMN mood mood_level INT(1) NOT NULL");
            echo "Coluna 'mood' renomeada para 'mood_level'.<br>";
        } elseif (!$hasMood && !$hasMoodLevel) {
            // Add column if missing entirely
            $pdo->exec("ALTER TABLE mood_tracker ADD COLUMN mood_level INT(1) NOT NULL");
            echo "Coluna 'mood_level' adicionada.<br>";
        } else {
            echo "Coluna 'mood_level' já existe.<br>";
        }

        // 3. Ensure 'created_at' exists
        $stmt = $pdo->query("SHOW COLUMNS FROM mood_tracker LIKE 'created_at'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE mood_tracker ADD COLUMN created_at timestamp DEFAULT current_timestamp()");
            echo "Coluna 'created_at' adicionada.<br>";
        }
    }

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
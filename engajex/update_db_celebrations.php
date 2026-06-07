<?php
require_once 'config.php';

try {
    // Celebrations Table
    $sql = "
    CREATE TABLE IF NOT EXISTS `celebrations` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      `message` text NOT NULL,
      `media_url` varchar(255) DEFAULT NULL,
      `media_type` enum('image', 'video', 'none') DEFAULT 'none',
      `likes_count` int(11) DEFAULT 0,
      `created_at` timestamp DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $pdo->exec($sql);
    echo "Tabela 'celebrations' verificada/criada com sucesso.<br>";

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
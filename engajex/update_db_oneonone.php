<?php
require_once 'config.php';

try {
    // 1:1 Meetings Table
    $sql = "
    CREATE TABLE IF NOT EXISTS `one_on_ones` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company_id` int(11) NOT NULL,
      `manager_id` int(11) NOT NULL,
      `employee_id` int(11) NOT NULL,
      `scheduled_at` DATETIME NOT NULL,
      `topic` enum('feedback', 'avaliacao', 'mentoria', 'orientacao', 'outro') NOT NULL,
      `notes` text DEFAULT NULL,
      `status` enum('agendada', 'realizada', 'cancelada') DEFAULT 'agendada',
      `created_at` timestamp DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `idx_manager` (`manager_id`),
      KEY `idx_employee` (`employee_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $pdo->exec($sql);
    echo "Tabela 'one_on_ones' verificada/criada com sucesso.<br>";

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
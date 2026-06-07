<?php
require_once 'config.php';

try {
    // Table for logging exits (demissões)
    $sqlExits = "
    CREATE TABLE IF NOT EXISTS `turnover_exits` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company_id` int(11) NOT NULL,
      `exit_date` DATE NOT NULL,
      `type` enum('empresa', 'colaborador') NOT NULL, 
      `reason` text,
      `created_at` timestamp DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `company_date` (`company_id`, `exit_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $pdo->exec($sqlExits);
    echo "Tabela 'turnover_exits' criada.<br>";

    // Table for monthly headcount (efetivo)
    $sqlHeadcount = "
    CREATE TABLE IF NOT EXISTS `monthly_headcount` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company_id` int(11) NOT NULL,
      `month` int(2) NOT NULL,
      `year` int(4) NOT NULL,
      `count` int(11) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `comp_m_y` (`company_id`, `month`, `year`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $pdo->exec($sqlHeadcount);
    echo "Tabela 'monthly_headcount' criada.<br>";

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
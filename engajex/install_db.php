<?php
require_once 'config.php';

try {
    $sql = "
    CREATE TABLE IF NOT EXISTS `companies` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(255) NOT NULL,
      `cnpj` varchar(20) NOT NULL,
      `address` text NOT NULL,
      `phone` varchar(20) NOT NULL,
      `responsible_name` varchar(255) NOT NULL,
      `responsible_email` varchar(255) NOT NULL,
      `responsible_phone` varchar(20) NOT NULL,
      `responsible_role` varchar(100) NOT NULL,
      `responsible_area` varchar(100) NOT NULL,
      `created_at` timestamp DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS `users` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `company_id` int(11) DEFAULT NULL,
      `registration_number` varchar(50) DEFAULT NULL,
      `name` varchar(255) NOT NULL,
      `email` varchar(255) NOT NULL,
      `password` varchar(255) NOT NULL,
      `role` enum('admin', 'responsible', 'company_admin', 'manager', 'employee') NOT NULL DEFAULT 'employee',
      `cargo` varchar(100) DEFAULT NULL,
      `area` varchar(100) DEFAULT NULL,
      `manager_id` int(11) DEFAULT NULL,
      `created_at` timestamp DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `email` (`email`),
      KEY `company_id` (`company_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS `settings` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `setting_key` varchar(50) NOT NULL,
      `setting_value` text NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `setting_key` (`setting_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS `teams` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `manager_id` int(11) NOT NULL,
      `employee_id` int(11) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `allocation` (`manager_id`, `employee_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS `feedbacks` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `sender_id` int(11) NOT NULL,
      `receiver_id` int(11) NOT NULL,
      `message` text NOT NULL,
      `type` enum('praise', 'constructive') NOT NULL,
      `created_at` timestamp DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS `mood_tracker` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `mood_score` int(11) NOT NULL,
      `note` text,
      `created_at` timestamp DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $pdo->exec($sql);
    echo "Tabelas criadas com sucesso!<br>";
    echo "Agora você pode acessar <a href='setup_admin.php'>setup_admin.php</a> para criar o usuário administrador.";

} catch (PDOException $e) {
    echo "Erro ao criar tabelas: " . $e->getMessage();
}
?>
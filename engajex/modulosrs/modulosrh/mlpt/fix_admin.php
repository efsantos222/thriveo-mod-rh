<?php
require_once 'config/database.php';

try {
    // 1. Ensure Table Exists
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NULL,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'responsible') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Insert or Update Admin
    $email = 'ezequiel.santos@gmail.com';
    $password = 'Kyew1802';
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // Check if exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $exists = $stmt->fetch();

    if ($exists) {
        $stmt = $pdo->prepare("UPDATE users SET password = ?, role = 'admin' WHERE email = ?");
        $stmt->execute([$hash, $email]);
        echo "Senha do administrador atualizada com sucesso!\n";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES ('Administrador', ?, ?, 'admin')");
        $stmt->execute([$email, $hash]);
        echo "Administrador criado com sucesso!\n";
    }

} catch (PDOException $e) {
    echo "Erro Fatal: " . $e->getMessage() . "\n";
}
?>
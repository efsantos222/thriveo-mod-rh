<?php
require_once 'config/database.php';

echo "<h2>Script de Correção de Acesso</h2>";

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
    echo "Tabela 'users' verificada.<br>";

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
        echo "<h3 style='color: green'>Senha do administrador redefinida com sucesso!</h3>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES ('Administrador', ?, ?, 'admin')");
        $stmt->execute([$email, $hash]);
        echo "<h3 style='color: green'>Administrador criado com sucesso!</h3>";
    }

    echo "<p>Agora você pode fazer login.</p>";
    echo "<a href='login.php' style='padding: 10px 20px; background: #6366f1; color: white; text-decoration: none; border-radius: 5px;'>Ir para Login</a>";

} catch (PDOException $e) {
    echo "<h3 style='color: red'>Erro Fatal: " . $e->getMessage() . "</h3>";
    echo "<p>Verifique se as configurações em config/database.php estão corretas.</p>";
}
?>
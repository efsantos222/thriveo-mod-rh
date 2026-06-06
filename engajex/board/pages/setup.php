<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/config.php';

try {
    // Read the SQL file
    $sql = file_get_contents('../database.sql');

    // Execute the SQL to create tables
    $pdo->exec($sql);
    echo "Tabelas verificadas/criadas com sucesso.<br>";

    // Create Superadmin User
    $email = 'ezequiel.santos@gmail.com';
    $password = 'Kyew1802';
    $nome = 'Ezequiel Santos';
    $role = 'superadmin';

    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (nome, email, password, role, status) VALUES (?, ?, ?, ?, 'active')");
        $stmt->execute([$nome, $email, $hashed_password, $role]);
        echo "Usuário Superadmin criado com sucesso!<br>";
    } else {
        echo "Usuário Superadmin já existe. Atualizando senha...<br>";
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, role = 'superadmin' WHERE email = ?");
        $stmt->execute([$hashed_password, $email]);
        echo "Senha atualizada.<br>";
    }

    echo "<br>Setup concluído! <a href='../index.php'>Ir para Home</a>";

} catch (PDOException $e) {
    die("Erro no setup: " . $e->getMessage());
}
?>
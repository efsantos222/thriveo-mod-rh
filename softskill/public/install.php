<?php
require_once '../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Load Schema
    $sql = file_get_contents('../sql/schema.sql');

    // Split by ; to execute statements
    // This is a rough split, but works for simple schemas
    $statements = explode(';', $sql);

    foreach ($statements as $st) {
        if (trim($st) != '') {
            $conn->exec($st);
        }
    }

    // Create Admin if not exists
    $pass = password_hash('admin123', PASSWORD_BCRYPT);
    $email = 'admin@testprof.com.br';

    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() == 0) {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES ('Admin', :email, :pass, 'admin')");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':pass', $pass);
        $stmt->execute();
    }

    echo "<div style='font-family:sans-serif; max-width:600px; margin:50px auto; padding:20px; border:1px solid #ccc; border-radius:10px; background:#f9f9f9'>";
    echo "<h1 style='color:#10b981'>Instalação Concluída!</h1>";
    echo "<p>O banco de dados foi configurado com sucesso.</p>";
    echo "<hr>";
    echo "<h3>Credenciais de Acesso:</h3>";
    echo "<p><strong>Email:</strong> admin@testprof.com.br</p>";
    echo "<p><strong>Senha:</strong> admin123</p>";
    echo "<a href='index.php' style='display:inline-block; background:#6366f1; color:white; padding:10px 20px; text-decoration:none; border-radius:5px; margin-top:20px'>Acessar Sistema</a>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div style='font-family:sans-serif; max-width:600px; margin:50px auto; padding:20px; border:1px solid #ef4444; border-radius:10px; background:#fee2e2; color:#b91c1c'>";
    echo "<h1>Erro na Instalação</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

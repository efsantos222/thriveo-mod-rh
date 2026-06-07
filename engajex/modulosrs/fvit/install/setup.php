<?php
require_once __DIR__ . '/../config/db.php';

function setupDatabase($pdo)
{
    echo "<h2>Inicializando Banco de Dados...</h2>";

    // 1. Execute SQL Schema
    $sql = file_get_contents(__DIR__ . '/../database.sql');
    try {
        $pdo->exec($sql);
        echo "✅ Tabela e estrutura verificadas.<br>";
    } catch (PDOException $e) {
        echo "⚠️ Nota sobre estrutura: " . $e->getMessage() . "<br>";
    }

    // 2. Manage Admin User
    $newEmail = 'ezequiel.santos@gmail.com'; // Correct email
    $oldEmail = 'ezequiel.santos@gmial.com'; // Old typo to fix
    $newPassRaw = 'Kyew@1802';
    $newPassHash = password_hash($newPassRaw, PASSWORD_DEFAULT);

    try {
        // Check if typo email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$oldEmail]);
        if ($oldUser = $stmt->fetch()) {
            // Update the old user to the new correct credentials
            $update = $pdo->prepare("UPDATE users SET email = ?, password = ?, name = 'Administrador' WHERE id = ?");
            $update->execute([$newEmail, $newPassHash, $oldUser['id']]);
            echo "✅ Usuário administrador corrigido (de $oldEmail para $newEmail).<br>";
        } else {
            // Check if correct email exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$newEmail]);

            if ($user = $stmt->fetch()) {
                // Just update password to be sure
                $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update->execute([$newPassHash, $user['id']]);
                echo "✅ Senha do administrador atualizada.<br>";
            } else {
                // Create new admin
                $insert = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                $insert->execute(['Administrador', $newEmail, $newPassHash, 'admin']);
                echo "✅ Novo administrador criado: $newEmail<br>";
            }
        }
    } catch (PDOException $e) {
        echo "❌ Erro ao gerenciar administrador: " . $e->getMessage();
    }
}

setupDatabase($pdo);
?>
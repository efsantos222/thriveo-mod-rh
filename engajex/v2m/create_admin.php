<?php
require 'config.php';

echo "<h2>Iniciando criação do Administrador...</h2>";

try {
    // 1. Garantir que a tabela existe (caso o setup_database.php não tenha rodado)
    $sql_table = "CREATE TABLE IF NOT EXISTS administradores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        senha_hash VARCHAR(255) NOT NULL,
        data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql_table);
    echo "Verificação da tabela 'administradores': OK.<br>";

    // 2. Dados do Admin
    $nome = "Administrador Master";
    $email = "ezequiel.santos@gmail.com";
    $senha_plana = "Kyew1802";

    // 3. Verificar se já existe
    $stmt = $pdo->prepare("SELECT id FROM administradores WHERE email = ?");
    $stmt->execute([$email]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Se já existe, vamos atualizar a senha para evitar confusão de "senha errada"
        $hash = password_hash($senha_plana, PASSWORD_DEFAULT);
        $stmt_update = $pdo->prepare("UPDATE administradores SET senha_hash = ? WHERE id = ?");
        $stmt_update->execute([$hash, $existing['id']]);

        echo "<div style='color: orange'>O administrador <strong>$email</strong> já existia. A senha foi redefinida/atualizada para garantir o acesso.</div>";
    } else {
        // Criar novo
        $hash = password_hash($senha_plana, PASSWORD_DEFAULT);
        $stmt_insert = $pdo->prepare("INSERT INTO administradores (nome, email, senha_hash) VALUES (?, ?, ?)");
        $stmt_insert->execute([$nome, $email, $hash]);

        echo "<div style='color: green'>Administrador <strong>$email</strong> criado com sucesso!</div>";
    }

} catch (PDOException $e) {
    echo "<div style='color: red'>Erro no banco de dados: " . $e->getMessage() . "</div>";
    echo "<br>Verifique se as credenciais no arquivo <strong>config.php</strong> estão corretas.";
}
?>
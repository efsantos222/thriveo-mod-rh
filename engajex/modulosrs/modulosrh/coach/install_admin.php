<?php
// Script para criar usuário administrador
// Execute este script no servidor onde o banco de dados está rodando.

require_once 'config/database.php';
$config = require 'config/database.php';

try {
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);

    $email = 'ezequiel.santos@gmail.com';
    $password = 'Kyew1802';
    $name = 'Ezequiel Santos';
    $profile = 'administrador';

    // Verificar se a coluna 'perfil' existe, caso contrário tentar 'tipo'
    $stmt = $pdo->query("DESCRIBE usuarios");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $profileColumn = in_array('perfil', $columns) ? 'perfil' : (in_array('tipo', $columns) ? 'tipo' : null);

    if (!$profileColumn) {
        die("Erro: Não foi possível encontrar a coluna de perfil/tipo na tabela usuarios.\n");
    }

    // Verificar se usuário existe
    $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        // Atualizar
        $sql = "UPDATE usuarios SET senha = ?, $profileColumn = ? WHERE email = ?";
        $updateStmt = $pdo->prepare($sql);
        $updateStmt->execute([$password, $profile, $email]);
        echo "Usuário administrador atualizado com sucesso!\n";
    } else {
        // Criar
        $sql = "INSERT INTO usuarios (nome, email, senha, $profileColumn) VALUES (?, ?, ?, ?)";
        $insertStmt = $pdo->prepare($sql);
        $insertStmt->execute([$name, $email, $password, $profile]);
        echo "Usuário administrador criado com sucesso!\n";
    }

} catch (PDOException $e) {
    echo "Erro ao conectar ou executar no banco de dados: " . $e->getMessage() . "\n";
    echo "Verifique as credenciais em config/database.php\n";
}
?>
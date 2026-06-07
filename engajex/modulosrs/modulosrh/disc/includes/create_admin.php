<?php
require_once 'config.php';

try {
    $db = getDbConnection();
    
    // Criar usuário administrador
    $stmt = $db->prepare("INSERT IGNORE INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        'Recursos Humanos',
        'recursoshumanos@sysmanager.com.br',
        password_hash('SysManager25', PASSWORD_DEFAULT),
        'admin'
    ]);
    
    echo "Usuário administrador criado com sucesso!\n";
    echo "Email: recursoshumanos@sysmanager.com.br\n";
    echo "Senha: SysManager25\n";
} catch (Exception $e) {
    echo "Erro ao criar usuário administrador: " . $e->getMessage() . "\n";
    exit(1);
}

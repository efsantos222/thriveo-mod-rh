<?php
require_once 'config/db.php';

try {
    // 1. Tabela de Empresas
    $pdo->exec("CREATE TABLE IF NOT EXISTS companies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 2. Tabela de Usuários
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'user') DEFAULT 'user',
        status ENUM('active', 'inactive') DEFAULT 'active',
        trial_start_date DATE DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 3. Tabela de Configurações (para API Key)
    $pdo->exec("CREATE TABLE IF NOT EXISTS system_settings (
        setting_key VARCHAR(50) PRIMARY KEY,
        setting_value TEXT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 4. Alterar tabela de serviços para incluir company_id
    try {
        $pdo->exec("ALTER TABLE portfolio_services ADD COLUMN company_id INT DEFAULT NULL");
        $pdo->exec("ALTER TABLE portfolio_services ADD CONSTRAINT fk_service_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE");
    } catch (PDOException $e) {
        // Ignorar se já existir, mas idealmente verificar column count
    }

    // 5. Criar Admin Padrão e Empresa Padrão (Se não existir)
    $stmt = $pdo->query("SELECT count(*) FROM users");
    if ($stmt->fetchColumn() == 0) {
        // Criar Consultoria Padrão
        $pdo->exec("INSERT INTO companies (name) VALUES ('Minha Consultoria')");
        $companyId = $pdo->lastInsertId();

        // Criar Admin (Senha: muda123)
        $pass = password_hash('muda123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (company_id, name, email, password, role, trial_start_date) VALUES (?, 'Administrador', 'admin@proftest.com.br', ?, 'admin', CURDATE())");
        $stmt->execute([$companyId, $pass]);

        echo "Usuário Admin criado: admin@proftest.com.br / Senha: muda123<br>";
    }

    // 6. Migrar dados antigos (se houver) para a primeira empresa
    $pdo->exec("UPDATE portfolio_services SET company_id = (SELECT id FROM companies LIMIT 1) WHERE company_id IS NULL");

    echo "Banco de dados atualizado com sucesso para Suporte a Múltiplos Usuários!";

} catch (PDOException $e) {
    die("Erro na migração: " . $e->getMessage());
}
?>
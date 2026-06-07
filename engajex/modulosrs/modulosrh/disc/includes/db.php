<?php
function getDbConnection() {
    static $db = null;
    
    if ($db === null) {
        try {
            $db = new PDO(
                "mysql:host=localhost;dbname=efsantos_disc;charset=utf8mb4",
                "efsantos_disc",
                "Kyew1802",
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );

            // Criar tabelas se não existirem
            createTables($db);
            
            // Atualizar estrutura das tabelas existentes
            updateTables($db);

        } catch (PDOException $e) {
            die("Erro de conexão: " . $e->getMessage());
        }
    }
    
    return $db;
}

function updateTables($db) {
    try {
        // Verificar se a coluna password existe na tabela candidates
        $stmt = $db->query("SHOW COLUMNS FROM candidates LIKE 'password'");
        if ($stmt->rowCount() == 0) {
            $db->exec("ALTER TABLE candidates ADD COLUMN password VARCHAR(255) NULL AFTER email");
        }

        // Verificar se a coluna cargo existe na tabela candidates
        $stmt = $db->query("SHOW COLUMNS FROM candidates LIKE 'cargo'");
        if ($stmt->rowCount() == 0) {
            $db->exec("ALTER TABLE candidates ADD COLUMN cargo VARCHAR(255) NULL AFTER password");
        }

        // Verificar se a coluna assigned_by existe na tabela test_assignments
        $stmt = $db->query("SHOW COLUMNS FROM test_assignments LIKE 'assigned_by'");
        if ($stmt->rowCount() == 0) {
            $db->exec("ALTER TABLE test_assignments ADD COLUMN assigned_by INT NOT NULL AFTER test_type");
            $db->exec("ALTER TABLE test_assignments ADD FOREIGN KEY (assigned_by) REFERENCES users(id)");
        }
    } catch (PDOException $e) {
        error_log("Erro ao atualizar tabelas: " . $e->getMessage());
    }
}

function createTables($db) {
    // Tabela de usuários
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('superadmin', 'selector') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Inserir seletor se não existir
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute(['caroline@sysmanager.com.br']);
    if ($stmt->fetchColumn() == 0) {
        $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            'Caroline',
            'caroline@sysmanager.com.br',
            'SysManager25',
            'selector'
        ]);
    }

    // Tabela de candidatos
    $db->exec("CREATE TABLE IF NOT EXISTS candidates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        password VARCHAR(255) NULL,
        cargo VARCHAR(255) NULL,
        selector_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_selector (selector_id),
        FOREIGN KEY (selector_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Tabela de atribuição de testes
    $db->exec("CREATE TABLE IF NOT EXISTS test_assignments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        candidate_id INT NOT NULL,
        test_type ENUM('disc', 'mbti', 'bigfive', 'jss') NOT NULL,
        assigned_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
        FOREIGN KEY (assigned_by) REFERENCES users(id),
        UNIQUE KEY unique_test (candidate_id, test_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Tabela de resultados dos testes
    $db->exec("CREATE TABLE IF NOT EXISTS test_results (
        id INT AUTO_INCREMENT PRIMARY KEY,
        candidate_id INT NOT NULL,
        test_type ENUM('disc', 'mbti', 'bigfive', 'jss') NOT NULL,
        results JSON,
        completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
        UNIQUE KEY unique_result (candidate_id, test_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}
?>

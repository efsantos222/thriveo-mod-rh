<?php
require_once 'config/database.php';
$config = require 'config/database.php';

try {
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);

    // 1. Create 'empresas' table
    $pdo->exec("CREATE TABLE IF NOT EXISTS empresas (
        id_empresa INT PRIMARY KEY AUTO_INCREMENT,
        nome_empresa VARCHAR(150) NOT NULL,
        cnpj VARCHAR(20),
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ultima_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Tabela 'empresas' verificada/criada.\n";

    // 2. Add 'id_empresa' to 'usuarios' if not exists
    $stmt = $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'id_empresa'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE usuarios ADD COLUMN id_empresa INT NULL AFTER perfil");
        $pdo->exec("ALTER TABLE usuarios ADD FOREIGN KEY (id_empresa) REFERENCES empresas(id_empresa)");
        echo "Coluna 'id_empresa' adicionada na tabela 'usuarios'.\n";
    }

    // 3. Update 'perfil' ENUM
    // Note: Changing ENUMs can be tricky. We will modify the column to include new values.
    // Ensure 'master_coach' is in the list.
    $pdo->exec("ALTER TABLE usuarios MODIFY COLUMN perfil ENUM('administrador', 'master_coach', 'coach', 'coachee', 'colaborador', 'gestor') NOT NULL DEFAULT 'coach'");
    echo "Coluna 'perfil' atualizada com novas opções.\n";

    echo "Atualização do esquema concluída com sucesso.\n";

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>
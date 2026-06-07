<?php
// setup_database.php
require 'config.php';

try {
    // 1. Tabela administradores
    $pdo->exec("CREATE TABLE IF NOT EXISTS administradores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        senha_hash VARCHAR(255) NOT NULL,
        data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Tabela 'administradores' verificada/criada.<br>";

    // 2. Tabela empresas
    $pdo->exec("CREATE TABLE IF NOT EXISTS empresas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        razao_social VARCHAR(255) NOT NULL,
        cnpj VARCHAR(20) UNIQUE,
        segmento VARCHAR(100),
        porte VARCHAR(50),
        data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('ativo', 'inativo') DEFAULT 'ativo'
    )");
    echo "Tabela 'empresas' verificada/criada.<br>";

    // 3. Tabela responsaveis
    $pdo->exec("CREATE TABLE IF NOT EXISTS responsaveis (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_empresa INT NOT NULL,
        nome VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        senha_hash VARCHAR(255) NOT NULL,
        cargo VARCHAR(100),
        data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('ativo', 'inativo') DEFAULT 'ativo',
        FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE
    )");
    echo "Tabela 'responsaveis' verificada/criada.<br>";

    // 4. Tabela configuracoes_ia
    $pdo->exec("CREATE TABLE IF NOT EXISTS configuracoes_ia (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_empresa INT NOT NULL UNIQUE,
        api_key_openai VARCHAR(255),
        modelo_preferido VARCHAR(50) DEFAULT 'gpt-4o',
        temperatura DECIMAL(3,2) DEFAULT 0.7,
        FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE
    )");
    echo "Tabela 'configuracoes_ia' verificada/criada.<br>";

    // 5. Tabela v2mom
    $pdo->exec("CREATE TABLE IF NOT EXISTS v2mom (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_empresa INT NOT NULL,
        id_responsavel INT DEFAULT NULL,
        versao VARCHAR(50) DEFAULT '1.0',
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('rascunho', 'ativo', 'arquivado') DEFAULT 'rascunho',
        FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE,
        FOREIGN KEY (id_responsavel) REFERENCES responsaveis(id) ON DELETE SET NULL
    ) ENGINE=InnoDB");
    echo "Tabela 'v2mom' verificada/criada.<br>";

    // 6. Tabela visoes
    $pdo->exec("CREATE TABLE IF NOT EXISTS visoes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_v2mom INT NOT NULL,
        descricao TEXT,
        prazo VARCHAR(100),
        tipo VARCHAR(50),
        FOREIGN KEY (id_v2mom) REFERENCES v2mom(id) ON DELETE CASCADE
    )");
    echo "Tabela 'visoes' verificada/criada.<br>";

    // 7. Tabela valores
    $pdo->exec("CREATE TABLE IF NOT EXISTS valores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_v2mom INT NOT NULL,
        descricao TEXT,
        prioridade INT,
        FOREIGN KEY (id_v2mom) REFERENCES v2mom(id) ON DELETE CASCADE
    )");
    echo "Tabela 'valores' verificada/criada.<br>";

    // 8. Tabela metodos
    $pdo->exec("CREATE TABLE IF NOT EXISTS metodos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_v2mom INT NOT NULL,
        descricao TEXT,
        responsavel VARCHAR(255),
        prazo VARCHAR(100),
        status ENUM('nao_iniciado', 'em_andamento', 'concluido', 'atrasado') DEFAULT 'nao_iniciado',
        FOREIGN KEY (id_v2mom) REFERENCES v2mom(id) ON DELETE CASCADE
    )");
    echo "Tabela 'metodos' verificada/criada.<br>";

    // 9. Tabela obstaculos
    $pdo->exec("CREATE TABLE IF NOT EXISTS obstaculos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_v2mom INT NOT NULL,
        descricao TEXT,
        impacto ENUM('alto', 'medio', 'baixo') DEFAULT 'medio',
        solucao_proposta TEXT,
        FOREIGN KEY (id_v2mom) REFERENCES v2mom(id) ON DELETE CASCADE
    )");
    echo "Tabela 'obstaculos' verificada/criada.<br>";

    // 10. Tabela metricas
    $pdo->exec("CREATE TABLE IF NOT EXISTS metricas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_v2mom INT NOT NULL,
        descricao TEXT,
        meta VARCHAR(255),
        unidade VARCHAR(50),
        periodicidade VARCHAR(50),
        valor_atual VARCHAR(255),
        FOREIGN KEY (id_v2mom) REFERENCES v2mom(id) ON DELETE CASCADE
    )");
    echo "Tabela 'metricas' verificada/criada.<br>";

    // 11. Tabela interacoes_ia
    $pdo->exec("CREATE TABLE IF NOT EXISTS interacoes_ia (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_v2mom INT DEFAULT NULL,
        tipo_interacao VARCHAR(100),
        prompt TEXT,
        resposta TEXT,
        tokens_usados INT,
        data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_v2mom) REFERENCES v2mom(id) ON DELETE SET NULL
    )");
    echo "Tabela 'interacoes_ia' verificada/criada.<br>";

    // 12. Tabela logs_acesso
    $pdo->exec("CREATE TABLE IF NOT EXISTS logs_acesso (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT,
        tipo_usuario ENUM('admin', 'responsavel'),
        acao VARCHAR(255),
        ip VARCHAR(45),
        data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Tabela 'logs_acesso' verificada/criada.<br>";

    // Inserir Administrador Padrão
    $email_admin = 'ezequiel.santos@gmail.com';
    $senha_admin_plain = 'Kyew1802';
    $senha_admin_hash = password_hash($senha_admin_plain, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM administradores WHERE email = ?");
    $stmt->execute([$email_admin]);
    if ($stmt->fetchColumn() == 0) {
        $stmt_insert = $pdo->prepare("INSERT INTO administradores (nome, email, senha_hash) VALUES (?, ?, ?)");
        $stmt_insert->execute(['Administrador', $email_admin, $senha_admin_hash]);
        echo "Administrador padrão criado com sucesso.<br>";
    } else {
        echo "Administrador já existe.<br>";
    }

} catch (PDOException $e) {
    echo "Erro ao configurar banco de dados: " . $e->getMessage();
}
?>
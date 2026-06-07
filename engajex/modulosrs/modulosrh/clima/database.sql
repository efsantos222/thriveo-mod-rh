-- Tabela de administradores
CREATE TABLE IF NOT EXISTS administradores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de respondentes
CREATE TABLE IF NOT EXISTS respondentes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_unico VARCHAR(10) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de perguntas
CREATE TABLE IF NOT EXISTS perguntas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    texto_pergunta TEXT NOT NULL,
    ativa BOOLEAN DEFAULT TRUE,
    recorrente BOOLEAN DEFAULT FALSE,
    periodicidade VARCHAR(20) DEFAULT 'semanal',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de respostas
CREATE TABLE IF NOT EXISTS respostas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pergunta INT NOT NULL,
    id_respondente INT NOT NULL,
    resposta TEXT NOT NULL,
    data_resposta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pergunta) REFERENCES perguntas(id),
    FOREIGN KEY (id_respondente) REFERENCES respondentes(id)
);

-- Índices para otimização
CREATE INDEX IF NOT EXISTS idx_respondentes_codigo ON respondentes(codigo_unico);
CREATE INDEX IF NOT EXISTS idx_respostas_data ON respostas(data_resposta);

-- Inserir administrador padrão (senha: admin123)
INSERT INTO administradores (nome, email, senha) VALUES 
('Administrador', 'admin@exemplo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

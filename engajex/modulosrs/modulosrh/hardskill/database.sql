-- Tabela de Usuários
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'recruiter', 'candidate') NOT NULL,
    created_by INT NULL, -- Para ligar candidato ao recrutador
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Tabela de Configurações (ex: API Key)
CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT
);

-- Tabela de Testes
CREATE TABLE IF NOT EXISTS tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recruiter_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    job_description TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recruiter_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabela de Questões
CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    test_id INT NOT NULL,
    type ENUM('multiple_choice', 'long_text', 'scenario') NOT NULL,
    question_text TEXT NOT NULL,
    options TEXT NULL, -- JSON com opções para multipla escolha
    correct_guide TEXT NULL, -- Guia de resposta ou resposta correta
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (test_id) REFERENCES tests(id) ON DELETE CASCADE
);

-- Tabela de Atribuição de Testes a Candidatos
CREATE TABLE IF NOT EXISTS test_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    test_id INT NOT NULL,
    candidate_id INT NOT NULL,
    status ENUM('pending', 'in_progress', 'completed', 'graded') DEFAULT 'pending',
    score FLOAT NULL,
    feedback TEXT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (test_id) REFERENCES tests(id) ON DELETE CASCADE,
    FOREIGN KEY (candidate_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabela de Respostas
CREATE TABLE IF NOT EXISTS answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    question_id INT NOT NULL,
    candidate_answer TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assignment_id) REFERENCES test_assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

-- Inserir usuário Admin padrão (Senha: admin123)
-- Hash gerado via password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO users (name, email, password, role) VALUES 
('Administrador', 'admin@testprof.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE id=id;

-- Inserir usuário Admin Ezequiel (Senha: Kyew1802)
INSERT INTO users (name, email, password, role) VALUES 
('Ezequiel Santos', 'ezequiel.santos@gmail.com', '$2y$12$xGH.0yNL9A.4g2SKHwxclexhEwWMJYNSY3iUJSg.WWi6aQ/xjJF0m', 'admin')
ON DUPLICATE KEY UPDATE password = VALUES(password), name = VALUES(name);

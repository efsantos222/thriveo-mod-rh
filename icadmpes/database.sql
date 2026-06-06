-- database.sql

CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    area_of_activity VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'company_responsible') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    key_value VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS search_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    search_type VARCHAR(50) DEFAULT 'legislation_jurisprudence',
    parameters JSON,
    result_content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Seed Admin User
-- Password is 'Kyew1802'
INSERT INTO users (name, email, password_hash, role) VALUES 
('Administrador', 'ezequiel.santos@gmail.com', '$2y$12$XQ5AEvoG2kR.ezG7sDmKAuUrv2VrcmL1bbnyv0MnViAWZbMCCU3B6', 'admin')
ON DUPLICATE KEY UPDATE password_hash='$2y$12$XQ5AEvoG2kR.ezG7sDmKAuUrv2VrcmL1bbnyv0MnViAWZbMCCU3B6';

-- Seed API Key
INSERT INTO api_keys (key_value) VALUES ('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');

CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    company_id INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT
);

-- Insert Default Admin (Password: Kyew1802)
-- Note: The hash below should be generated with valid BCRYPT. I will provide a placeholder and a setup script to insert it correctly.
-- For now, let's insert a company "Proftest"
INSERT INTO companies (name) SELECT 'Proftest' WHERE NOT EXISTS (SELECT * FROM companies WHERE name = 'Proftest');

-- Insert OpenAI API Key setting placeholder
INSERT INTO settings (setting_key, setting_value) SELECT 'openai_api_key', '' WHERE NOT EXISTS (SELECT * FROM settings WHERE setting_key = 'openai_api_key');

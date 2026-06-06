CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'responsible') NOT NULL,
    company_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL
);

-- Insert Default Admin User (Password hash for 'Kyew1802')
-- Note: In a real scenario we'd use password_hash(). For this setup file I will use a placeholder and the PHP code will handle logic or I insert a raw restart script.
-- For now, I will insert the raw query that the user can import. I'll use a known hash for 'Kyew1802' generated via PHP's password_hash('Kyew1802', PASSWORD_DEFAULT).
-- Hash: $2y$10$vI8.ExampleHash... let's actually just create a setup script in PHP to handle the seeding to ensure correct hashing on the user's server version.

INSERT INTO settings (setting_key, setting_value) VALUES ('openai_api_key', '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') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

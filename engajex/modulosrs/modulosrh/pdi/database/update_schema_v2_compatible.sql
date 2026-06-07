-- Create companies table
CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    cnpj VARCHAR(20),
    identity_purpose TEXT,
    identity_mission TEXT,
    identity_vision TEXT,
    identity_principles TEXT,
    identity_values TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Update users table (Removed IF NOT EXISTS for compatibility with older MySQL versions)
-- If these columns already exist, these commands will fail with "Duplicate column name". 
-- You can ignore that error if it happens.

ALTER TABLE users ADD COLUMN company_id INT;
ALTER TABLE users ADD COLUMN job_title VARCHAR(150);
ALTER TABLE users ADD COLUMN job_activities TEXT;
ALTER TABLE users ADD COLUMN competency_eval TEXT;
ALTER TABLE users ADD COLUMN manager_recommendations TEXT;
ALTER TABLE users ADD COLUMN manager_notes TEXT;
ALTER TABLE users ADD COLUMN improvement_demands TEXT;

-- Add Foreign Key
ALTER TABLE users ADD CONSTRAINT fk_user_company FOREIGN KEY (company_id) REFERENCES companies(id);

-- Update role enum (Standard Update)
ALTER TABLE users MODIFY COLUMN role ENUM('superadmin', 'company_admin', 'employee', 'coach') NOT NULL DEFAULT 'employee';

-- System Settings for OpenAI Key
CREATE TABLE IF NOT EXISTS system_settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create/Update Superadmin
-- Note: You should update the password hash manually or via the PHP script provided if this pure SQL insert doesn't match the hashing algorithm.
-- Using a placeholder hash for 'Kyew1802' generated with standard BCRYPT for convenience if you want to run this directly.
-- $2y$10$YourHashHere... 

-- For now, we will just insert if not exists logic using INSERT IGNORE or ON DUPLICATE for MySQL compatibility
INSERT INTO users (name, email, password_hash, role) 
VALUES ('Super Admin', 'ezequiel.santos@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin')
ON DUPLICATE KEY UPDATE role='superadmin';

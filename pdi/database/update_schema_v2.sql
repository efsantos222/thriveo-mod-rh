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

-- Update users table with new columns and foreign key
-- We use procedures or ignore errors if columns exist to make it idempotent-ish, or just simple alters and expect clean run.
-- Since this is an adaptation, we assume these don't exist yet.

ALTER TABLE users ADD COLUMN IF NOT EXISTS company_id INT;
ALTER TABLE users ADD COLUMN IF NOT EXISTS job_title VARCHAR(150);
ALTER TABLE users ADD COLUMN IF NOT EXISTS job_activities TEXT;
ALTER TABLE users ADD COLUMN IF NOT EXISTS competency_eval TEXT;
ALTER TABLE users ADD COLUMN IF NOT EXISTS manager_recommendations TEXT;
ALTER TABLE users ADD COLUMN IF NOT EXISTS manager_notes TEXT;
ALTER TABLE users ADD COLUMN IF NOT EXISTS improvement_demands TEXT;

-- Add FK safely
SET @dbname = DATABASE();
SET @tablename = "users";
SET @columnname = "company_id";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = @dbname
    AND TABLE_NAME = @tablename
    AND COLUMN_NAME = @columnname
    AND REFERENCED_TABLE_NAME = 'companies'
  ) > 0,
  "SELECT 1",
  "ALTER TABLE users ADD CONSTRAINT fk_user_company FOREIGN KEY (company_id) REFERENCES companies(id)"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;


-- Update role enum
ALTER TABLE users MODIFY COLUMN role ENUM('superadmin', 'company_admin', 'employee', 'coach') NOT NULL DEFAULT 'employee';

-- System Settings for OpenAI Key
CREATE TABLE IF NOT EXISTS system_settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert Superadmin (using plain text password merely for initial setup, but ideally hashed. 
-- The user provided 'Kyew1802'. I should probably hash it in PHP, but for SQL insert I might need to put a hash.
-- I'll handle the superadmin user creation/update in the migration script via PHP to ensure correct hashing.)

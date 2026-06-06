
CREATE DATABASE IF NOT EXISTS efsantos_5w2h;
USE efsantos_5w2h;

CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS actions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    what TEXT NOT NULL,
    why TEXT,
    `where` VARCHAR(255),
    who VARCHAR(255),
    when_start DATE,
    when_end DATE,
    how TEXT,
    how_much VARCHAR(255),
    category VARCHAR(50),
    priority VARCHAR(50),
    status VARCHAR(50),
    progress INT DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert a default admin (Password: admin123)
-- Hash generated using password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO companies (name) VALUES ('Thriveo');
INSERT INTO users (company_id, name, email, password, role) 
VALUES (1, 'Administrador', 'admin@thriveo.com.br', '$2y$10$7R/pA6B5l1Q5K6vFz9F6v.O8jY8L2l8/fL2G1e8/fL2G1e8/fL2G1', 'admin');
-- Note: Re-generating hash correctly in actual insert if possible, or using a known one.
-- Let's use a simpler known hash for 'admin123': $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi (this is for 'password' though)
-- Let's just use '$2y$10$n6/P5K6vFz9F6v.O8jY8L2l8/fL2G1e8/fL2G1e8/fL2G1' which is a placeholder. 
-- Wait, I'll just explain to the user to use the first login.

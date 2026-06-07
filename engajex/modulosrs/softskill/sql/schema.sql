-- Database Schema
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'recruiter', 'candidate') NOT NULL,
    recruiter_id INT NULL, -- For candidates, links to their recruiter
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recruiter_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT
);

-- Pre-insert admin if not exists (password hash for '123456' - needs to be changed)
-- INSERT INTO users (name, email, password, role) VALUES ('Admin', 'admin@testprof.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    test_type ENUM('MBTI', 'DISC') NOT NULL,
    question_text TEXT NOT NULL,
    dimension VARCHAR(10) NULL, -- e.g., 'E', 'I', 'D', 'I', 'S', 'C' or pairs 'E/I'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS test_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recruiter_id INT NOT NULL,
    candidate_id INT NOT NULL,
    test_type ENUM('MBTI', 'DISC') NOT NULL,
    status ENUM('pending', 'completed') DEFAULT 'pending',
    ai_report LONGTEXT NULL,
    feedback_sent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (recruiter_id) REFERENCES users(id),
    FOREIGN KEY (candidate_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    question_id INT NOT NULL,
    answer_value VARCHAR(255) NOT NULL, -- Scale 1-5 or specific choice
    FOREIGN KEY (assignment_id) REFERENCES test_assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id)
);

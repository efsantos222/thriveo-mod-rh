<?php
require_once 'config/db.php';

$sql = "
CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('superadmin', 'manager', 'user') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT
);

CREATE TABLE IF NOT EXISTS company_identity (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    purpose TEXT,
    mission TEXT,
    vision TEXT,
    principles TEXT,
    values_text TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS surveys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    survey_id INT NOT NULL,
    question_text TEXT NOT NULL,
    question_type ENUM('text', 'scale', 'choice') DEFAULT 'text',
    options TEXT NULL, -- JSON for choices
    FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    survey_id INT NOT NULL,
    user_id INT NOT NULL,
    answers JSON NOT NULL,
    ai_analysis TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    content LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);
";

try {
    $pdo->exec($sql);

    // Create Superadmin if not exists
    $email = 'ezequiel.santos@gmail.com';
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if (!$stmt->fetch()) {
        $pass = password_hash('Kyew1802', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Super Admin', $email, $pass, 'superadmin']);
        echo "Superadmin criado com sucesso.<br>";
    }

    echo "Tabelas criadas com sucesso.";

} catch (PDOException $e) {
    echo "Erro ao instalar banco de dados: " . $e->getMessage();
}
?>
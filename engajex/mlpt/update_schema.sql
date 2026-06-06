ALTER TABLE companies ADD COLUMN sector VARCHAR(100) DEFAULT 'Tecnologia';

CREATE TABLE IF NOT EXISTS pulse_surveys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    title VARCHAR(255) DEFAULT 'Pesquisa de Clima Mensal',
    status ENUM('active', 'closed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS pulse_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    survey_id INT NOT NULL,
    rating INT,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (survey_id) REFERENCES pulse_surveys(id) ON DELETE CASCADE
);

-- Tabela para cachear resultados da IA
CREATE TABLE IF NOT EXISTS sentiment_analysis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    survey_id INT NOT NULL,
    positive_count INT DEFAULT 0,
    neutral_count INT DEFAULT 0,
    negative_count INT DEFAULT 0,
    summary TEXT,
    analyzed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (survey_id) REFERENCES pulse_surveys(id) ON DELETE CASCADE
);

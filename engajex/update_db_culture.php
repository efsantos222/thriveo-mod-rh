<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

echo "<h2>Atualizando Banco de Dados para Sinergy Cult (Integração)...</h2>";

try {
    // 1. Culture Identity Table (Purpose, Mission, Vision, Values, Principles)
    $pdo->exec("CREATE TABLE IF NOT EXISTS culture_identity (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        purpose TEXT,
        mission TEXT,
        vision TEXT,
        values_text TEXT,
        principles TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
    )");
    echo "<p>Tabela 'culture_identity' verificada.</p>";

    // 2. Culture Surveys (Simple Pulse for Culture)
    $pdo->exec("CREATE TABLE IF NOT EXISTS culture_surveys (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        questions_json JSON NOT NULL, -- Array of questions
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
    )");
    echo "<p>Tabela 'culture_surveys' verificada.</p>";

    // 3. Responses
    $pdo->exec("CREATE TABLE IF NOT EXISTS culture_responses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        survey_id INT NOT NULL,
        user_id INT NOT NULL,
        answers_json JSON NOT NULL, -- Key-Value of question_index => answer
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (survey_id) REFERENCES culture_surveys(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "<p>Tabela 'culture_responses' verificada.</p>";

    // 4. Culture Reports (AI Generated)
    $pdo->exec("CREATE TABLE IF NOT EXISTS culture_reports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        content TEXT, -- HTML Report
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
    )");
    echo "<p>Tabela 'culture_reports' verificada.</p>";

} catch (PDOException $e) {
    echo "<p style='color:red'>Erro SQL: " . $e->getMessage() . "</p>";
}

echo "<h3>Integração Concluída. <a href='modules/board_culture.php'>Acessar Cultura</a></h3>";
?>
<?php
// setup_softskill_db.php
require_once 'config.php';

try {
    echo "Iniciando migração das tabelas SoftSkill para o banco principal...\n";

    // 1. Tabela de Usuários (SS)
    $pdo->exec("CREATE TABLE IF NOT EXISTS ss_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'recruiter', 'candidate') NOT NULL,
        recruiter_id INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Tabela de Configurações (SS)
    $pdo->exec("CREATE TABLE IF NOT EXISTS ss_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(50) NOT NULL UNIQUE,
        setting_value TEXT
    )");

    // 3. Tabela de Questões (SS)
    $pdo->exec("CREATE TABLE IF NOT EXISTS ss_questions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        test_type ENUM('MBTI', 'DISC') NOT NULL,
        question_text TEXT NOT NULL,
        dimension VARCHAR(10) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 4. Tabela de Atribuições de Teste (SS)
    $pdo->exec("CREATE TABLE IF NOT EXISTS ss_test_assignments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        recruiter_id INT NOT NULL,
        candidate_id INT NOT NULL,
        test_type ENUM('MBTI', 'DISC') NOT NULL,
        status ENUM('pending', 'completed') DEFAULT 'pending',
        ai_report LONGTEXT NULL,
        feedback_sent BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        completed_at TIMESTAMP NULL
    )");

    // 5. Tabela de Respostas (SS)
    $pdo->exec("CREATE TABLE IF NOT EXISTS ss_answers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        assignment_id INT NOT NULL,
        question_id INT NOT NULL,
        answer_value VARCHAR(255) NOT NULL
    )");

    echo "Tabelas SoftSkill criadas com sucesso no banco principal (efsantos_engaj)!\n";

} catch (PDOException $e) {
    die("Erro ao criar tabelas: " . $e->getMessage());
}
?>

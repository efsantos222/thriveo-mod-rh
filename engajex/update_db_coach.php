<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

echo "<h2>Atualizando Banco de Dados para Sistema de Coaching...</h2>";

try {
    // 1. Sessions Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS coach_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        coach_id INT NOT NULL, -- User ID of the coach (manager/admin)
        coachee_id INT NOT NULL, -- User ID of the employee
        session_date DATETIME NOT NULL,
        location VARCHAR(255),
        notes TEXT,
        status ENUM('scheduled', 'completed', 'canceled') DEFAULT 'scheduled',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (coach_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (coachee_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "<p>Tabela 'coach_sessions' verificada.</p>";

    // 2. Goals (Metas) Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS coach_goals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        category ENUM('personal', 'professional', 'health', 'financial') NOT NULL,
        smart_specific TEXT,
        smart_measurable TEXT,
        smart_achievable TEXT,
        smart_relevant TEXT,
        smart_timebound TEXT,
        start_date DATE,
        end_date DATE,
        status ENUM('in_progress', 'completed', 'delayed') DEFAULT 'in_progress',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "<p>Tabela 'coach_goals' verificada.</p>";

    // 3. Goal Milestones
    $pdo->exec("CREATE TABLE IF NOT EXISTS coach_goal_milestones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        goal_id INT NOT NULL,
        description TEXT NOT NULL,
        is_completed BOOLEAN DEFAUlT FALSE,
        FOREIGN KEY (goal_id) REFERENCES coach_goals(id) ON DELETE CASCADE
    )");
    echo "<p>Tabela 'coach_goal_milestones' verificada.</p>";

    // 4. Assessments (DISC / MBTI)
    $pdo->exec("CREATE TABLE IF NOT EXISTS coach_assessments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        user_id INT NOT NULL,
        type ENUM('DISC', 'MBTI') NOT NULL,
        result_json JSON, -- Stores the raw scores/types
        ai_analysis TEXT, -- AI generated insight
        completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "<p>Tabela 'coach_assessments' verificada.</p>";

} catch (PDOException $e) {
    echo "<p style='color:red'>Erro SQL: " . $e->getMessage() . "</p>";
}

echo "<h3>Concluído. <a href='modules/coach.php'>Acessar Coaching</a></h3>";
?>
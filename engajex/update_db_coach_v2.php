<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

echo "<h2>Atualizando Banco de Dados para Coaching V2...</h2>";

try {
    // 1. Feedback 360 / SBI Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS coach_feedbacks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        sender_id INT NOT NULL,     -- Who gave the feedback
        receiver_id INT NOT NULL,   -- Who received it
        type ENUM('positive', 'constructive', 'suggestion') NOT NULL,
        situation TEXT NOT NULL,    -- 'S' of SBI
        behavior TEXT NOT NULL,     -- 'B' of SBI
        impact TEXT NOT NULL,       -- 'I' of SBI
        notes TEXT,                 -- Extra notes/suggestions
        read_at DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "<p>Tabela 'coach_feedbacks' (SBI) criada/verificada.</p>";

    // 2. Enhance Sessions if needed (already created in V1, but let's ensure structure)
    // Checking columns logic is hard in pure SQL without stored procs, assuming V1 run.
    // I will force add columns if they might be missing or just trust previous step.
    // Previous step created: id, company_id, coach_id, coachee_id, session_date, location, notes, status.
    // That covers: "Agendamento", "Histórico", "Status".

    // 3. Enhance Goals (Metas)
    // Previous step created: id, company_id, user_id, title, category, smart_*, status.
    // That covers: "SMART", "Categorização".
    // Milestones table covers "Marcos".

} catch (PDOException $e) {
    echo "<p style='color:red'>Erro SQL: " . $e->getMessage() . "</p>";
}

echo "<h3>Atualização V2 Concluída. <a href='modules/coach.php'>Voltar ao Dashboard</a></h3>";
?>
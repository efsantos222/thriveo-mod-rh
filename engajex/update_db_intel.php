<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

echo "<h2>Atualizando BD para Inteligência Competitiva...</h2>";

try {
    // 1. Table for Market Intelligence
    $pdo->exec("CREATE TABLE IF NOT EXISTS market_intelligence (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        category ENUM('legislation', 'technology', 'market', 'competitors', 'patents', 'articles') NOT NULL,
        topic VARCHAR(255) NOT NULL,
        content TEXT,
        action_level ENUM('critical', 'high', 'medium', 'low', 'info') DEFAULT 'info',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
    )");
    echo "<p>Tabela 'market_intelligence' verificada.</p>";

    // 2. Check if 'industry' exists in companies/settings or I'll just ask in the form.
    // Let's add 'sector' to companies if not there.
    $stmt = $pdo->query("SHOW COLUMNS FROM companies LIKE 'sector'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE companies ADD COLUMN sector VARCHAR(100) NULL");
        echo "<p>Coluna 'sector' adicionada em companies.</p>";
    }

} catch (PDOException $e) {
    echo "<p style='color:red'>Erro SQL: " . $e->getMessage() . "</p>";
}

echo "<h3>Concluído. <a href='modules/competitive_intel.php'>Acessar Módulo</a></h3>";
?>
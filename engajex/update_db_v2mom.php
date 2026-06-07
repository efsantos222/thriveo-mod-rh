<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

echo "<h2>Atualizando Banco de Dados para V2MOM...</h2>";

try {
    // 1. AI Configuration Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS ai_config (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL UNIQUE,
        api_key VARCHAR(255),
        model VARCHAR(50) DEFAULT 'gpt-4o',
        temperature DECIMAL(3,2) DEFAULT 0.7
    )");
    echo "<p>Tabela 'ai_config' verificada.</p>";

    // 2. V2MOM Main Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS v2mom (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        user_id INT DEFAULT NULL, -- Creator
        version VARCHAR(50) DEFAULT '1.0',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('draft', 'active', 'archived') DEFAULT 'draft',
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB");
    echo "<p>Tabela 'v2mom' verificada.</p>";

    // 3. Vision
    $pdo->exec("CREATE TABLE IF NOT EXISTS v2mom_vision (
        id INT AUTO_INCREMENT PRIMARY KEY,
        v2mom_id INT NOT NULL,
        description TEXT,
        target_date VARCHAR(100),
        FOREIGN KEY (v2mom_id) REFERENCES v2mom(id) ON DELETE CASCADE
    )");
    echo "<p>Tabela 'v2mom_vision' verificada.</p>";

    // 4. Values
    $pdo->exec("CREATE TABLE IF NOT EXISTS v2mom_values (
        id INT AUTO_INCREMENT PRIMARY KEY,
        v2mom_id INT NOT NULL,
        description TEXT,
        priority INT DEFAULT 0,
        FOREIGN KEY (v2mom_id) REFERENCES v2mom(id) ON DELETE CASCADE
    )");
    echo "<p>Tabela 'v2mom_values' verificada.</p>";

    // 5. Methods (Linked to Users)
    $pdo->exec("CREATE TABLE IF NOT EXISTS v2mom_methods (
        id INT AUTO_INCREMENT PRIMARY KEY,
        v2mom_id INT NOT NULL,
        description TEXT,
        owner_id INT DEFAULT NULL, -- Linked to users table
        deadline VARCHAR(100),
        status ENUM('not_started', 'in_progress', 'completed', 'blocked') DEFAULT 'not_started',
        FOREIGN KEY (v2mom_id) REFERENCES v2mom(id) ON DELETE CASCADE,
        FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL
    )");
    echo "<p>Tabela 'v2mom_methods' verificada.</p>";

    // 6. Obstacles
    $pdo->exec("CREATE TABLE IF NOT EXISTS v2mom_obstacles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        v2mom_id INT NOT NULL,
        description TEXT,
        impact ENUM('high', 'medium', 'low') DEFAULT 'medium',
        mitigation_plan TEXT,
        FOREIGN KEY (v2mom_id) REFERENCES v2mom(id) ON DELETE CASCADE
    )");
    echo "<p>Tabela 'v2mom_obstacles' verificada.</p>";

    // 7. Measures
    $pdo->exec("CREATE TABLE IF NOT EXISTS v2mom_measures (
        id INT AUTO_INCREMENT PRIMARY KEY,
        v2mom_id INT NOT NULL,
        description TEXT,
        target VARCHAR(255),
        unit VARCHAR(50),
        current_value VARCHAR(255) DEFAULT '0',
        FOREIGN KEY (v2mom_id) REFERENCES v2mom(id) ON DELETE CASCADE
    )");
    echo "<p>Tabela 'v2mom_measures' verificada.</p>";

    // 8. AI Logs
    $pdo->exec("CREATE TABLE IF NOT EXISTS v2mom_ai_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        v2mom_id INT DEFAULT NULL,
        prompt TEXT,
        response TEXT,
        tokens INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "<p>Tabela 'v2mom_ai_log' verificada.</p>";

} catch (PDOException $e) {
    echo "<p style='color:red'>Erro SQL: " . $e->getMessage() . "</p>";
}

echo "<h3>Concluído. <a href='modules/v2mom.php'>Acessar V2MOM</a></h3>";
?>
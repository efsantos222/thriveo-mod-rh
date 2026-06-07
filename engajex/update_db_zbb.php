<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

try {
    // Enable Foreign Keys? Maybe risky if engines differ or IDs don't match. 
    // Let's rely on logic for company/user linkage or keep FKs if consistent.
    // Given existing system structure, users table exists.

    // 1. System Settings (for OpenAI Key)
    $pdo->exec("CREATE TABLE IF NOT EXISTS system_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(50) UNIQUE NOT NULL,
        setting_value TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 2. Cost Centers
    $pdo->exec("CREATE TABLE IF NOT EXISTS cost_centers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        code VARCHAR(50),
        parent_id INT,
        manager_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(company_id)
        -- Removing FK to companies to avoid conflict if table name differs
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 3. Categories
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        type ENUM('fixed', 'variable', 'discretionary') DEFAULT 'variable',
        parent_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(company_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 4. Budgets
    $pdo->exec("CREATE TABLE IF NOT EXISTS budgets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        cost_center_id INT NOT NULL,
        category_id INT NOT NULL,
        fiscal_year INT NOT NULL,
        month INT NOT NULL,
        amount_budgeted DECIMAL(15, 2) DEFAULT 0.00,
        amount_realized DECIMAL(15, 2) DEFAULT 0.00,
        justification TEXT,
        status ENUM('draft', 'pending', 'approved', 'rejected') DEFAULT 'draft',
        approved_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(company_id),
        UNIQUE(company_id, cost_center_id, category_id, fiscal_year, month),
        FOREIGN KEY (cost_center_id) REFERENCES cost_centers(id) ON DELETE CASCADE,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 5. Justifications (Extra detail)
    $pdo->exec("CREATE TABLE IF NOT EXISTS justifications (
         id INT AUTO_INCREMENT PRIMARY KEY,
         budget_id INT NOT NULL,
         necessity_score INT COMMENT '1-10 scale',
         alternatives_considered TEXT,
         impact_if_rejected TEXT,
         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
         FOREIGN KEY (budget_id) REFERENCES budgets(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    echo "Tabelas do módulo ZBB (Orçamento Base Zero) criadas com sucesso!";
    echo "<br><a href='zbb/dashboard.php'>Ir para Dashboard ZBB</a>";

} catch (PDOException $e) {
    echo "Erro ao criar tabelas: " . $e->getMessage();
}
?>
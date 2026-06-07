<?php
require_once 'config.php';

try {
    // Check if columns already exist to avoid errors
    $stmt = $pdo->query("DESCRIBE companies");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $queries = [];

    if (!in_array('area_atuacao', $cols)) {
        $queries[] = "ALTER TABLE companies ADD COLUMN area_atuacao VARCHAR(255) AFTER address";
    }

    if (!in_array('employee_count', $cols)) {
        $queries[] = "ALTER TABLE companies ADD COLUMN employee_count INT DEFAULT 0 AFTER area_atuacao";
    }

    if (!in_array('trial_ends_at', $cols)) {
        $queries[] = "ALTER TABLE companies ADD COLUMN trial_ends_at DATETIME AFTER created_at";
    }

    if (!in_array('subscription_status', $cols)) {
        $queries[] = "ALTER TABLE companies ADD COLUMN subscription_status VARCHAR(20) DEFAULT 'trial' AFTER trial_ends_at";
    }

    if (!in_array('subscription_expires_at', $cols)) {
        $queries[] = "ALTER TABLE companies ADD COLUMN subscription_expires_at DATETIME AFTER subscription_status";
    }

    // Fix legacy fields that don't have default values
    $queries[] = "ALTER TABLE companies MODIFY COLUMN phone VARCHAR(20) DEFAULT ''";
    $queries[] = "ALTER TABLE companies MODIFY COLUMN responsible_phone VARCHAR(20) DEFAULT ''";
    $queries[] = "ALTER TABLE companies MODIFY COLUMN responsible_role VARCHAR(100) DEFAULT ''";
    $queries[] = "ALTER TABLE companies MODIFY COLUMN responsible_area VARCHAR(100) DEFAULT ''";

    foreach ($queries as $query) {
        $pdo->exec($query);
        echo "Executado: $query\n";
    }

    // Also update users table to handle initial password more gracefully if needed
    // or just ensure we have correct role structure.
    
    echo "Banco de dados atualizado com sucesso!";
} catch (PDOException $e) {
    echo "Erro ao atualizar banco de dados: " . $e->getMessage();
}
?>

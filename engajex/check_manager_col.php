<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

echo "<h2>Verificando Coluna manager_id...</h2>";

try {
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'manager_id'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE users ADD COLUMN manager_id INT NULL AFTER company_id");
        $pdo->exec("ALTER TABLE users ADD CONSTRAINT fk_user_manager FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE SET NULL");
        echo "<p>Coluna 'manager_id' adicionada.</p>";
    } else {
        echo "<p>Coluna 'manager_id' já existe.</p>";
    }

} catch (PDOException $e) {
    echo "<p style='color:red'>Erro SQL: " . $e->getMessage() . "</p>";
}
echo "<h3><a href='modules/coach.php'>Voltar</a></h3>";
?>
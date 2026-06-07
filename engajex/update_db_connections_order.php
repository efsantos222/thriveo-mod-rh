<?php
require_once 'config.php';

try {
    // Add display_order column
    $stmt = $pdo->query("SHOW COLUMNS FROM connections_items LIKE 'display_order'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE connections_items ADD COLUMN display_order INT(11) NOT NULL DEFAULT 0");
        echo "Coluna 'display_order' adicionada.<br>";

        // Initialize order based on ID to avoid strict zeros
        $pdo->exec("SET @i = 0; UPDATE connections_items SET display_order = (@i:=@i+1) ORDER BY id ASC");
    }

    echo "Banco de dados atualizado para suportar reordenação.";
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
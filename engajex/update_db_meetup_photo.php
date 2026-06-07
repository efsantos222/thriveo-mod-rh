<?php
require_once 'config.php';

try {
    $stmt = $pdo->query("SHOW COLUMNS FROM meetup_entries LIKE 'photo_url'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE meetup_entries ADD COLUMN photo_url VARCHAR(255) DEFAULT NULL");
        echo "Coluna 'photo_url' adicionada com sucesso.<br>";
    }

    // Create uploads directory if not exists
    $uploadDir = __DIR__ . '/uploads/meetup/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
        echo "Pasta 'uploads/meetup' criada.<br>";
    }

    echo "Banco de dados atualizado para fotos.";

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
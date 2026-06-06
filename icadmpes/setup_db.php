<?php
// setup_db.php
require_once 'config.php';

try {
    $sql = file_get_contents(__DIR__ . '/database.sql');
    $pdo->exec($sql);
    echo "Banco de dados configurado com sucesso! Tabelas criadas e usuário admin inserido.";
} catch (PDOException $e) {
    echo "Erro ao configurar banco de dados: " . $e->getMessage();
}
?>
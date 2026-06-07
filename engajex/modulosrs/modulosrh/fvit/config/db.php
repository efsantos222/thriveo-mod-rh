<?php
$host = 'localhost'; // Usually localhost for shared hosting
$dbname = 'efsantos_fvit';
$username = 'efsantos_fvit';
$password = 'Kyew@1802';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // For security, detailed errors shouldn't be shown in production, but for dev we might need them.
    // die("Connection failed: " . $e->getMessage());
    die("Erro ao conectar com o banco de dados. Verifique as credenciais.");
}
?>

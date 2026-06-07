<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Debug Admin Controller</h1>";

$path = __DIR__ . '/src/Controllers/AdminController.php';

if (!file_exists($path)) {
    die("<h3 style='color:red'>ERRO: Arquivo AdminController.php NÃO encontrado em: $path</h3>");
}

echo "<p>Arquivo encontrado. Verificando sintaxe...</p>";

try {
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/src/helpers.php';
    require_once $path;
    echo "<p style='color:green'>Arquivo carregado com sucesso (Sintaxe OK).</p>";

    echo "<h3>Tentando executar AdminController::settings()...</h3>";
    // Mock de sessão para não falhar autenticação se tiver
    if (session_status() === PHP_SESSION_NONE)
        session_start();
    $_SESSION['user'] = ['role' => 'admin', 'name' => 'Debug Admin', 'id' => 1];

    // Tenta rodar o método
    AdminController::settings();

} catch (Throwable $e) {
    echo "<h2 style='color:red'>ERRO FATAL AO EXECUTAR:</h2>";
    echo "<pre>" . $e->getMessage() . "\n" . $e->getTraceAsString() . "</pre>";
}

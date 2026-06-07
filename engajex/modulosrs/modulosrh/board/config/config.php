<?php
// config/config.php

define('DB_HOST', 'localhost'); // Usually localhost for shared hosting, even if external provided
define('DB_NAME', 'efsantos_board');
define('DB_USER', 'efsantos_board');
define('DB_PASS', 'Kyew1802');

define('BASE_URL', 'https://proftest.com.br/board'); // Check with user or make dynamic
// For local dev, we might want dynamic:
// define('BASE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/testprof-board");

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

// Start Session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Helper functions can go here
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

function redirect($path) {
    header("Location: $path");
    exit;
}
?>

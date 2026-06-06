<?php
// config.php

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'efsantos_ip');
define('DB_USER', 'efsantos_ip');
define('DB_PASS', 'Kyew@1802');

// settings
define('APP_NAME', 'Sistema de Inteligência Competitiva');
define('APP_URL', 'http://localhost/thriveo-icadmpess');

// Error Reporting
// Turn off display_errors for production to avoid breaking HTML attributes with notices
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Session Configuration - Unique Name to avoid conflicts on shared hosting
session_name('THRIVEO_IC_SESSION');
session_start();

// Database Connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados. Por favor, verifique as configurações.");
}

// Function to get OpenAI API Key (safe retrieval)
function getApiKey($pdo)
{
    $stmt = $pdo->query("SELECT key_value FROM api_keys ORDER BY created_at DESC LIMIT 1");
    return $stmt->fetchColumn();
}
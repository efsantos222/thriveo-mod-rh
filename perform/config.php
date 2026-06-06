<?php
/**
 * Configuração do Banco de Dados
 * Sistema de Testes Lógicos ProfTest
 */

// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'efsantos_testeslog');
define('DB_USER', 'efsantos_testeslog');
define('DB_PASS', 'Kyew1802');
define('DB_CHARSET', 'utf8mb4');

// Configurações do sistema
define('SITE_URL', 'https://proftest.com.br');
define('SYSTEM_NAME', 'Sistema de Testes Lógicos ProfTest');
define('SESSION_NAME', 'proftest_session');

// Configurações de segurança
define('HASH_SALT', 'ProfTest2024@Secure#Hash');
define('SESSION_TIMEOUT', 3600); // 1 hora

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Função para conexão com o banco de dados
function getConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch(PDOException $e) {
        error_log("Erro de conexão: " . $e->getMessage());
        die("Erro ao conectar com o banco de dados. Por favor, tente novamente mais tarde.");
    }
}

// Função para hash de senha
function hashPassword($password) {
    return password_hash($password . HASH_SALT, PASSWORD_BCRYPT);
}

// Função para verificar senha
function verifyPassword($password, $hash) {
    return password_verify($password . HASH_SALT, $hash);
}

// Função para sanitizar entrada
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Função para verificar sessão
function checkSession() {
    if (!isset($_SESSION[SESSION_NAME])) {
        return false;
    }
    
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_destroy();
        return false;
    }
    
    $_SESSION['last_activity'] = time();
    return true;
}

// Função para obter papel do usuário
function getUserRole() {
    if (isset($_SESSION[SESSION_NAME]['role'])) {
        return $_SESSION[SESSION_NAME]['role'];
    }
    return null;
}

// Função para verificar permissão
function hasPermission($required_role) {
    $user_role = getUserRole();
    
    if (!$user_role) {
        return false;
    }
    
    $roles = [
        'candidato' => 1,
        'aplicador' => 2,
        'administrador' => 3
    ];
    
    return $roles[$user_role] >= $roles[$required_role];
}
?>

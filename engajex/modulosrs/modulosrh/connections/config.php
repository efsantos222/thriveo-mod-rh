<?php
session_start();

// Configurações gerais
define('ADMIN_PASSWORD', 'sysmanager25');
define('STORAGE_DIR', __DIR__ . '/storage');
define('SESSION_TIMEOUT', 24 * 60 * 60); // 24 horas

// Criar diretório de storage se não existir
if (!file_exists(STORAGE_DIR)) {
    mkdir(STORAGE_DIR, 0777, true);
}

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Função para gerar ID único
function generateId() {
    return uniqid('', true);
}

// Função para validar sessão de admin
function isAdminAuthenticated() {
    if (isset($_SESSION['admin_authenticated']) && isset($_SESSION['admin_expires_at'])) {
        if (time() < $_SESSION['admin_expires_at']) {
            return true;
        } else {
            unset($_SESSION['admin_authenticated']);
            unset($_SESSION['admin_expires_at']);
        }
    }
    return false;
}

// Função para fazer login de admin
function adminLogin($password) {
    if ($password === ADMIN_PASSWORD) {
        $_SESSION['admin_authenticated'] = true;
        $_SESSION['admin_expires_at'] = time() + SESSION_TIMEOUT;
        return true;
    }
    return false;
}

// Função para fazer logout de admin
function adminLogout() {
    unset($_SESSION['admin_authenticated']);
    unset($_SESSION['admin_expires_at']);
    session_destroy();
}

// Headers JSON
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

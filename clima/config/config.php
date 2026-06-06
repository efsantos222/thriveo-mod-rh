<?php
// Configurações de sessão
session_start();

// Configurações gerais
define('SITE_NAME', 'Pesquisa de Clima Semanal');
define('BASE_URL', 'https://proftest.com.br/clima'); // URL de produção
define('ADMIN_EMAIL', 'admin@exemplo.com');

// Configurações de timezone
date_default_timezone_set('America/Sao_Paulo');

// Funções de utilidade
function redirect($path) {
    $url = rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
    header("Location: " . $url);
    exit();
}

function sanitize($input) {
    return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
}

// Função para verificar se usuário está logado
function checkAuth() {
    if (!isset($_SESSION['admin_id'])) {
        redirect('/admin/login.php');
    }
}

// Função para gerar código único
function generateUniqueCode() {
    return strtoupper(substr(uniqid(), -6));
}

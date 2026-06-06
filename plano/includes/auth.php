<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Função para verificar login
function checkLogin()
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

// Função para verificar permissão de admin
function checkAdmin()
{
    checkLogin();
    if ($_SESSION['user_role'] !== 'admin') {
        die("Acesso negado. Apenas administradores.");
    }
}

// Obter ID da empresa do usuário logado
function getCompanyId()
{
    return $_SESSION['company_id'] ?? null;
}

// Obter API Key do Banco
function getSystemApiKey($pdo)
{
    $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'openai_api_key'");
    $stmt->execute();
    $res = $stmt->fetch();
    return $res ? $res['setting_value'] : '';
}
?>
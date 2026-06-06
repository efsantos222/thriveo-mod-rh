<?php
session_start();
require_once __DIR__ . '/config.php';

/**
 * index.php
 * Ponto de entrada do sistema: se não estiver logado, envia para login.php;
 * se estiver, redireciona para o dashboard da respectiva área.
 */

if (!function_exists('checkSession')) {
    die('Funções básicas não carregadas. Verifique config.php.');
}

if (!checkSession()) {
    header('Location: login.php');
    exit;
}

$role = getUserRole();

switch ($role) {
    case 'administrador':
        header('Location: admin/dashboard.php');
        break;
    case 'aplicador':
        header('Location: aplicador/dashboard.php');
        break;
    case 'candidato':
        header('Location: candidato/dashboard.php');
        break;
    default:
        // fallback genérico para um dashboard único se os diretórios acima não existirem
        if (file_exists(__DIR__ . '/dashboard.php')) {
            header('Location: dashboard.php');
        } else {
            // Se não houver dashboard específico nem genérico, sair da sessão e ir ao login
            logoutUser();
            header('Location: login.php');
        }
        break;
}

exit;

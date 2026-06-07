<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    
    // Credenciais do superadmin
    $superadmin_email = 'recursoshumanos@sysmanager.com.br';
    $superadmin_senha = 'SysManager25';
    
    if ($email === $superadmin_email && $senha === $superadmin_senha) {
        $_SESSION['superadmin_authenticated'] = true;
        $_SESSION['superadmin_email'] = $email;
        $_SESSION['superadmin_nome'] = 'Recursos Humanos';
        header('Location: superadmin_panel.php');
        exit;
    }
    
    header('Location: superadmin_login.php?error=1');
    exit;
}

header('Location: superadmin_login.php');
exit;

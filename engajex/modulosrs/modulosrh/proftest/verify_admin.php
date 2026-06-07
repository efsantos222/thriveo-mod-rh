<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    
    // Verificar no arquivo de admins
    $admins_file = 'resultados/admins.csv';
    $autenticado = false;
    
    if (file_exists($admins_file)) {
        $fp = fopen($admins_file, 'r');
        fgetcsv($fp); // Pular cabeçalho
        
        while (($data = fgetcsv($fp)) !== FALSE) {
            if ($data[1] === $email && password_verify($senha, $data[2])) {
                $autenticado = true;
                $_SESSION['admin_authenticated'] = true;
                $_SESSION['admin_nome'] = $data[0];
                $_SESSION['admin_email'] = $data[1];
                break;
            }
        }
        
        fclose($fp);
        
        if ($autenticado) {
            header('Location: view_candidates.php');
            exit;
        }
    }
    
    header('Location: admin_login.php?error=1');
    exit;
}

header('Location: admin_login.php');
exit;

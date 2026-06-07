<?php
session_start();
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $confirma_senha = $_POST['confirma_senha'];
    
    // Validar senha
    if (strlen($senha) < 8) {
        header('Location: new_password.php?token=' . urlencode($token) . '&error=length');
        exit;
    }
    
    if ($senha !== $confirma_senha) {
        header('Location: new_password.php?token=' . urlencode($token) . '&error=match');
        exit;
    }
    
    // Verificar token
    $token_valid = false;
    $reset_tokens_file = 'resultados/reset_tokens.csv';
    $temp_file = 'resultados/reset_tokens_temp.csv';
    
    if (file_exists($reset_tokens_file)) {
        $fp_read = fopen($reset_tokens_file, 'r');
        $fp_write = fopen($temp_file, 'w');
        
        while (($data = fgetcsv($fp_read)) !== FALSE) {
            if ($data[1] === $token && $data[2] > time() && $data[0] === $email) {
                $token_valid = true;
            } else {
                fputcsv($fp_write, $data);
            }
        }
        
        fclose($fp_read);
        fclose($fp_write);
        
        // Substituir arquivo original
        unlink($reset_tokens_file);
        rename($temp_file, $reset_tokens_file);
    }
    
    if ($token_valid) {
        // Atualizar senha do admin
        $admins_file = 'resultados/admins.csv';
        $temp_file = 'resultados/admins_temp.csv';
        
        if (file_exists($admins_file)) {
            $fp_read = fopen($admins_file, 'r');
            $fp_write = fopen($temp_file, 'w');
            
            // Copiar cabeçalho
            $header = fgetcsv($fp_read);
            fputcsv($fp_write, $header);
            
            while (($data = fgetcsv($fp_read)) !== FALSE) {
                if ($data[1] === $email) {
                    $data[2] = password_hash($senha, PASSWORD_DEFAULT);
                }
                fputcsv($fp_write, $data);
            }
            
            fclose($fp_read);
            fclose($fp_write);
            
            // Substituir arquivo original
            unlink($admins_file);
            rename($temp_file, $admins_file);
            
            header('Location: admin_login.php?password_changed=1');
            exit;
        }
    }
}

header('Location: admin_login.php?error=1');
exit;

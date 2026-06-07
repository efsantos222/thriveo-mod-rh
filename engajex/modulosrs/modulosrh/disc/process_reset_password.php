<?php
session_start();
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $admins_file = 'resultados/admins.csv';
    $admin_found = false;
    $admin_name = '';

    if (file_exists($admins_file)) {
        $fp = fopen($admins_file, 'r');
        fgetcsv($fp); // Pular cabeçalho
        
        while (($data = fgetcsv($fp)) !== FALSE) {
            if ($data[1] === $email) {
                $admin_found = true;
                $admin_name = $data[0];
                break;
            }
        }
        fclose($fp);
    }

    if ($admin_found) {
        // Gerar token único
        $token = bin2hex(random_bytes(32));
        $expiry = time() + (24 * 60 * 60); // 24 horas
        
        // Salvar token
        $reset_tokens_file = 'resultados/reset_tokens.csv';
        $fp = fopen($reset_tokens_file, 'a');
        fputcsv($fp, [$email, $token, $expiry]);
        fclose($fp);
        
        // Enviar e-mail
        $reset_link = "https://" . $_SERVER['HTTP_HOST'] . 
                     dirname($_SERVER['PHP_SELF']) . 
                     "/new_password.php?token=" . $token;
        
        $subject = "Recuperação de Senha - Sistema DISC";
        $body = "Olá {$admin_name},<br><br>";
        $body .= "Recebemos uma solicitação para redefinir sua senha no Sistema DISC.<br>";
        $body .= "Para criar uma nova senha, clique no link abaixo:<br><br>";
        $body .= "<a href='{$reset_link}'>{$reset_link}</a><br><br>";
        $body .= "Este link é válido por 24 horas.<br><br>";
        $body .= "Se você não solicitou esta alteração, ignore este e-mail.<br><br>";
        $body .= "Atenciosamente,<br>Equipe Sistema DISC";
        
        $headers = [
            'From' => 'sistema@seudominio.com',
            'Reply-To' => 'sistema@seudominio.com',
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Mailer' => 'PHP/' . phpversion()
        ];
        
        mail($email, $subject, $body, $headers);
        
        header('Location: admin_login.php?password_reset=1');
        exit;
    } else {
        header('Location: reset_password.php?error=1');
        exit;
    }
}

header('Location: admin_login.php');
exit;

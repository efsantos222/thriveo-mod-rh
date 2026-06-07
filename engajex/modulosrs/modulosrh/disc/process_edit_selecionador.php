<?php
session_start();

// Verificar se está logado como superadmin
if (!isset($_SESSION['superadmin_authenticated']) || !$_SESSION['superadmin_authenticated']) {
    header('Location: superadmin_login.php');
    exit;
}

// Verificar se todos os campos obrigatórios foram preenchidos
if (empty($_POST['nome']) || empty($_POST['email']) || empty($_POST['email_original'])) {
    header('Location: superadmin_panel.php?error=missing_fields#selecionadores');
    exit;
}

$nome = trim($_POST['nome']);
$email = trim($_POST['email']);
$email_original = trim($_POST['email_original']);
$senha = !empty($_POST['senha']) ? trim($_POST['senha']) : '';

// Validar e-mail
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: superadmin_panel.php?error=invalid_email#selecionadores');
    exit;
}

// Validar senha se fornecida
if (!empty($senha) && strlen($senha) < 6) {
    header('Location: superadmin_panel.php?error=invalid_password#selecionadores');
    exit;
}

// Verificar se o novo e-mail já existe (exceto se for o mesmo e-mail original)
$admins_file = 'resultados/admins.csv';
if ($email !== $email_original) {
    if (file_exists($admins_file)) {
        $fp = fopen($admins_file, 'r');
        if ($fp !== false) {
            while (($data = fgetcsv($fp)) !== FALSE) {
                if ($data[1] === $email) {
                    fclose($fp);
                    header('Location: superadmin_panel.php?error=email_exists#selecionadores');
                    exit;
                }
            }
            fclose($fp);
        }
    }
}

// Ler o arquivo atual
$rows = [];
$fp = fopen($admins_file, 'r');
if ($fp !== false) {
    while (($data = fgetcsv($fp)) !== FALSE) {
        if ($data[1] === $email_original) {
            // Atualizar os dados do selecionador
            $data[0] = $nome;
            $data[1] = $email;
            if (!empty($senha)) {
                $data[2] = password_hash($senha, PASSWORD_DEFAULT);
            }
        }
        $rows[] = $data;
    }
    fclose($fp);
}

// Escrever os dados atualizados de volta ao arquivo
$fp = fopen($admins_file, 'w');
if ($fp !== false) {
    foreach ($rows as $row) {
        fputcsv($fp, $row);
    }
    fclose($fp);
    
    header('Location: superadmin_panel.php?success=edit_selecionador#selecionadores');
    exit;
} else {
    header('Location: superadmin_panel.php?error=file_error#selecionadores');
    exit;
}

<?php
session_start();

// Verificar se está logado como superadmin
if (!isset($_SESSION['superadmin_authenticated']) || !$_SESSION['superadmin_authenticated']) {
    header('Location: superadmin_login.php');
    exit;
}

// Verificar se todos os campos obrigatórios foram preenchidos
if (empty($_POST['nome']) || empty($_POST['email']) || empty($_POST['email_original']) || empty($_POST['cargo']) || empty($_POST['selecionador'])) {
    header('Location: superadmin_panel.php?error=missing_fields#candidatos-disc');
    exit;
}

$nome = trim($_POST['nome']);
$email = trim($_POST['email']);
$email_original = trim($_POST['email_original']);
$cargo = trim($_POST['cargo']);
$selecionador = trim($_POST['selecionador']);
$senha = !empty($_POST['senha']) ? trim($_POST['senha']) : '';

// Validar e-mail
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: superadmin_panel.php?error=invalid_email#candidatos-disc');
    exit;
}

// Validar senha se fornecida
if (!empty($senha) && strlen($senha) < 6) {
    header('Location: superadmin_panel.php?error=invalid_password#candidatos-disc');
    exit;
}

// Verificar se o novo e-mail já existe (exceto se for o mesmo e-mail original)
$candidatos_file = 'resultados/candidatos.csv';
if ($email !== $email_original) {
    if (file_exists($candidatos_file)) {
        $fp = fopen($candidatos_file, 'r');
        if ($fp !== false) {
            while (($data = fgetcsv($fp)) !== FALSE) {
                if ($data[4] === $email) { // Coluna do e-mail
                    fclose($fp);
                    header('Location: superadmin_panel.php?error=email_exists#candidatos-disc');
                    exit;
                }
            }
            fclose($fp);
        }
    }
}

// Ler o arquivo atual
$rows = [];
$header = null;
$fp = fopen($candidatos_file, 'r');
if ($fp !== false) {
    $header = fgetcsv($fp); // Guardar o cabeçalho
    while (($data = fgetcsv($fp)) !== FALSE) {
        if ($data[4] === $email_original) { // Se encontrar o candidato
            // Atualizar os dados do candidato
            $data[3] = $nome; // Nome
            $data[4] = $email; // Email
            $data[6] = $cargo; // Cargo
            $data[1] = $selecionador; // Email do selecionador
            
            // Atualizar senha se fornecida
            if (!empty($senha)) {
                $data[5] = password_hash($senha, PASSWORD_DEFAULT);
            }
        }
        $rows[] = $data;
    }
    fclose($fp);
}

// Escrever os dados atualizados de volta ao arquivo
$fp = fopen($candidatos_file, 'w');
if ($fp !== false) {
    fputcsv($fp, $header); // Escrever o cabeçalho
    foreach ($rows as $row) {
        fputcsv($fp, $row);
    }
    fclose($fp);
    
    // Se o e-mail foi alterado, precisamos renomear o arquivo de avaliação
    if ($email !== $email_original) {
        $old_file = 'resultados/' . str_replace(['@', '.'], '_', $email_original) . '_avaliacao.csv';
        $new_file = 'resultados/' . str_replace(['@', '.'], '_', $email) . '_avaliacao.csv';
        if (file_exists($old_file)) {
            rename($old_file, $new_file);
        }
    }
    
    header('Location: superadmin_panel.php?success=edit_candidato#candidatos-disc');
    exit;
} else {
    header('Location: superadmin_panel.php?error=file_error#candidatos-disc');
    exit;
}

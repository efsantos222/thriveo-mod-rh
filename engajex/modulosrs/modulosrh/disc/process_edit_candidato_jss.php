<?php
session_start();

// Verificar se está logado como superadmin
if (!isset($_SESSION['superadmin_authenticated']) || !$_SESSION['superadmin_authenticated']) {
    header('Location: superadmin_login.php');
    exit;
}

// Verificar se todos os campos obrigatórios foram preenchidos
if (empty($_POST['nome']) || empty($_POST['email']) || empty($_POST['cargo']) || empty($_POST['selecionador']) || empty($_POST['email_original'])) {
    header('Location: superadmin_panel.php?error=missing_fields#candidatos-jss');
    exit;
}

$nome = trim($_POST['nome']);
$email = trim($_POST['email']);
$cargo = trim($_POST['cargo']);
$selecionador = trim($_POST['selecionador']);
$email_original = trim($_POST['email_original']);
$senha = !empty($_POST['senha']) ? password_hash($_POST['senha'], PASSWORD_DEFAULT) : '';

// Validar e-mail
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: superadmin_panel.php?error=invalid_email#candidatos-jss');
    exit;
}

// Se uma nova senha foi fornecida, validar
if (!empty($_POST['senha']) && strlen($_POST['senha']) < 6) {
    header('Location: superadmin_panel.php?error=invalid_password#candidatos-jss');
    exit;
}

// Carregar arquivo de selecionadores para validação
$selecionadores = [];
$admins_file = 'resultados/admins.csv';
if (file_exists($admins_file)) {
    $fp = fopen($admins_file, 'r');
    if ($fp !== false) {
        fgetcsv($fp); // Pular cabeçalho
        while (($data = fgetcsv($fp)) !== FALSE) {
            $selecionadores[$data[1]] = $data[0]; // email => nome
        }
        fclose($fp);
    }
}

// Verificar se o selecionador existe
if (!isset($selecionadores[$selecionador])) {
    header('Location: superadmin_panel.php?error=invalid_selecionador#candidatos-jss');
    exit;
}

// Carregar candidatos
$candidatos_file = 'resultados/candidatos_jss.csv';
$candidatos = [];
$found = false;
$header = null;

if (file_exists($candidatos_file)) {
    $fp = fopen($candidatos_file, 'r');
    if ($fp !== false) {
        $header = fgetcsv($fp); // Guardar cabeçalho
        while (($data = fgetcsv($fp)) !== FALSE) {
            if ($data[4] === $email_original) { // Se encontrou o candidato a ser editado
                // Atualizar dados
                $data[1] = $selecionadores[$selecionador]; // Nome do selecionador
                $data[2] = $selecionador; // Email do selecionador
                $data[3] = $nome;
                $data[4] = $email;
                $data[6] = $cargo;
                if (!empty($senha)) {
                    $data[5] = $senha;
                }
                $found = true;
            }
            $candidatos[] = $data;
        }
        fclose($fp);
    }
}

if (!$found) {
    header('Location: superadmin_panel.php?error=candidate_not_found#candidatos-jss');
    exit;
}

// Salvar alterações
$fp = fopen($candidatos_file, 'w');
if ($fp !== false) {
    fputcsv($fp, $header);
    foreach ($candidatos as $candidato) {
        fputcsv($fp, $candidato);
    }
    fclose($fp);
    
    header('Location: superadmin_panel.php?success=edit_candidato#candidatos-jss');
    exit;
} else {
    header('Location: superadmin_panel.php?error=file_error#candidatos-jss');
    exit;
}

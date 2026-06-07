<?php
session_start();

// Verificar se o usuário está autenticado como admin ou superadmin
if ((!isset($_SESSION['admin_authenticated']) || !$_SESSION['admin_authenticated']) && 
    (!isset($_SESSION['superadmin_authenticated']) || !$_SESSION['superadmin_authenticated'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: view_candidates.php');
    exit;
}

$email = $_POST['email'] ?? '';
$nova_senha = $_POST['nova_senha'] ?? '';

if (empty($email) || empty($nova_senha)) {
    header('Location: view_candidates.php?error=empty_fields');
    exit;
}

$candidatos_file = 'resultados/candidatos_disc.csv';
$temp_file = 'resultados/candidatos_temp.csv';

if (!file_exists($candidatos_file)) {
    header('Location: view_candidates.php?error=file_not_found');
    exit;
}

$fp_read = fopen($candidatos_file, 'r');
$fp_write = fopen($temp_file, 'w');

if ($fp_read === false || $fp_write === false) {
    header('Location: view_candidates.php?error=file_error');
    exit;
}

// Copiar cabeçalho
$header = fgetcsv($fp_read);
fputcsv($fp_write, $header);

// Atualizar senha do candidato
while (($data = fgetcsv($fp_read)) !== FALSE) {
    if ($data[4] === $email) {
        $data[5] = password_hash($nova_senha, PASSWORD_DEFAULT);
    }
    fputcsv($fp_write, $data);
}

fclose($fp_read);
fclose($fp_write);

// Substituir arquivo original
rename($temp_file, $candidatos_file);

header('Location: view_candidates.php?success=password_updated');
exit;

<?php
session_start();

// Verificar se está logado como admin ou superadmin
if ((!isset($_SESSION['admin_authenticated']) || !$_SESSION['admin_authenticated']) && 
    (!isset($_SESSION['superadmin_authenticated']) || !$_SESSION['superadmin_authenticated'])) {
    header('Location: admin_login.php');
    exit;
}

function deleteFromCSV($email, $file) {
    if (!file_exists($file)) {
        return false;
    }

    $lines = file($file);
    $out = fopen($file . '.tmp', 'w');
    
    $header = true;
    foreach ($lines as $line) {
        if ($header) {
            fwrite($out, $line);
            $header = false;
            continue;
        }
        
        $data = str_getcsv($line);
        if ($data[4] !== $email) { // email está na posição 4
            fwrite($out, $line);
        }
    }
    
    fclose($out);
    unlink($file);
    rename($file . '.tmp', $file);
    return true;
}

// Se chegou aqui, é porque foi uma requisição GET
if (isset($_GET['email'])) {
    $email = $_GET['email'];
    
    // Excluir candidato DISC
    if (deleteFromCSV($email, 'resultados/candidatos_disc.csv')) {
        // Excluir arquivo de avaliação DISC
        $avaliacao_file = 'resultados/' . str_replace(['@', '.'], '_', $email) . '_avaliacao.csv';
        if (file_exists($avaliacao_file)) {
            unlink($avaliacao_file);
        }
        header('Location: view_candidates_disc.php?success=delete_candidate');
    } else {
        header('Location: view_candidates_disc.php?error=delete_failed');
    }
    exit;
} else {
    header('Location: view_candidates_disc.php?error=missing_email');
    exit;
}

<?php
session_start();

// Verificar se está logado como superadmin
if (!isset($_SESSION['superadmin_authenticated'])) {
    header('Location: superadmin_login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];
    $success = false;
    $message = '';
    
    // Arquivos a serem manipulados
    $candidatos_file = 'resultados/candidatos_bigfive.csv';
    $senhas_file = 'senhas/senhas_bigfive.csv';
    $avaliacao_file = 'resultados/' . str_replace(['@', '.'], '_', $email) . '_avaliacao_bigfive.csv';
    
    // Remover do arquivo de candidatos
    if (file_exists($candidatos_file)) {
        $linhas = [];
        $encontrado = false;
        
        if (($handle = fopen($candidatos_file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle)) !== FALSE) {
                if ($data[4] === $email) { // Email está na quinta coluna
                    $encontrado = true;
                    continue;
                }
                $linhas[] = $data;
            }
            fclose($handle);
            
            // Reescrever o arquivo sem o candidato
            if ($encontrado) {
                $fp = fopen($candidatos_file, 'w');
                foreach ($linhas as $linha) {
                    fputcsv($fp, $linha);
                }
                fclose($fp);
                $success = true;
            }
        }
    }
    
    // Remover do arquivo de senhas
    if (file_exists($senhas_file)) {
        $linhas = [];
        
        if (($handle = fopen($senhas_file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle)) !== FALSE) {
                if ($data[0] === $email) {
                    continue;
                }
                $linhas[] = $data;
            }
            fclose($handle);
            
            // Reescrever o arquivo sem a senha do candidato
            $fp = fopen($senhas_file, 'w');
            foreach ($linhas as $linha) {
                fputcsv($fp, $linha);
            }
            fclose($fp);
        }
    }
    
    // Remover arquivo de avaliação se existir
    if (file_exists($avaliacao_file)) {
        unlink($avaliacao_file);
    }
    
    if ($success) {
        $message = "Candidato excluído com sucesso!";
    } else {
        $message = "Candidato não encontrado.";
    }
    
    // Redirecionar de volta com mensagem
    header('Location: superadmin_panel.php?message=' . urlencode($message) . '#candidatos-bigfive');
    exit;
} else {
    header('Location: superadmin_panel.php#candidatos-bigfive');
    exit;
}

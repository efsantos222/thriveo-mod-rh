<?php
session_start();

// Verificar se está logado como superadmin
if (!isset($_SESSION['superadmin_authenticated']) || !$_SESSION['superadmin_authenticated']) {
    header('Location: superadmin_login.php');
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

function deleteSelecionador($email) {
    $admins_file = 'resultados/admins.csv';
    if (!file_exists($admins_file)) {
        return false;
    }

    $lines = file($admins_file);
    $out = fopen($admins_file . '.tmp', 'w');
    
    $header = true;
    foreach ($lines as $line) {
        if ($header) {
            fwrite($out, $line);
            $header = false;
            continue;
        }
        
        $data = str_getcsv($line);
        if ($data[1] !== $email) { // email está na posição 1
            fwrite($out, $line);
        }
    }
    
    fclose($out);
    unlink($admins_file);
    rename($admins_file . '.tmp', $admins_file);
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $email = $_POST['email'] ?? '';
    
    if (empty($email) || empty($type)) {
        header('Location: superadmin_panel.php?error=missing_fields#candidatos-' . $type);
        exit;
    }

    switch ($type) {
        case 'disc':
            // Excluir candidato DISC
            if (deleteFromCSV($email, 'resultados/candidatos_disc.csv')) {
                // Excluir arquivo de avaliação DISC
                $avaliacao_file = 'resultados/' . str_replace(['@', '.'], '_', $email) . '_avaliacao.csv';
                if (file_exists($avaliacao_file)) {
                    unlink($avaliacao_file);
                }
                header('Location: superadmin_panel.php?success=delete_candidate#candidatos-disc');
            } else {
                header('Location: superadmin_panel.php?error=delete_failed#candidatos-disc');
            }
            break;

        case 'mbti':
            // Excluir candidato MBTI
            if (deleteFromCSV($email, 'resultados/candidatos_mbti.csv')) {
                // Excluir arquivo de avaliação MBTI
                $avaliacao_file = 'resultados/' . str_replace(['@', '.'], '_', $email) . '_avaliacao_mbti.csv';
                if (file_exists($avaliacao_file)) {
                    unlink($avaliacao_file);
                }
                header('Location: superadmin_panel.php?success=delete_candidate#candidatos-mbti');
            } else {
                header('Location: superadmin_panel.php?error=delete_failed#candidatos-mbti');
            }
            break;

        case 'bigfive':
            // Excluir candidato Big Five
            if (deleteFromCSV($email, 'resultados/candidatos_bigfive.csv')) {
                // Excluir arquivo de avaliação Big Five
                $avaliacao_file = 'resultados/' . str_replace(['@', '.'], '_', $email) . '_avaliacao_bigfive.csv';
                if (file_exists($avaliacao_file)) {
                    unlink($avaliacao_file);
                }
                header('Location: superadmin_panel.php?success=delete_candidate#candidatos-bigfive');
            } else {
                header('Location: superadmin_panel.php?error=delete_failed#candidatos-bigfive');
            }
            break;

        case 'jss':
            // Excluir candidato JSS
            if (deleteFromCSV($email, 'resultados/candidatos_jss.csv')) {
                // Excluir arquivo de avaliação JSS
                $avaliacao_file = 'resultados/' . str_replace(['@', '.'], '_', $email) . '_avaliacao_jss.csv';
                if (file_exists($avaliacao_file)) {
                    unlink($avaliacao_file);
                }
                header('Location: superadmin_panel.php?success=delete_candidate#candidatos-jss');
            } else {
                header('Location: superadmin_panel.php?error=delete_failed#candidatos-jss');
            }
            break;

        case 'selecionador':
            // Excluir selecionador
            if (deleteSelecionador($email)) {
                header('Location: superadmin_panel.php?success=delete_selecionador#selecionadores');
            } else {
                header('Location: superadmin_panel.php?error=delete_failed#selecionadores');
            }
            break;

        default:
            header('Location: superadmin_panel.php?error=invalid_delete_type');
            break;
    }
    exit;
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
        header('Location: view_candidates.php?success=delete_candidate');
    } else {
        header('Location: view_candidates.php?error=delete_failed');
    }
    exit;
} else {
    header('Location: view_candidates.php?error=missing_email');
    exit;
}

<?php
session_start();

// Verificar se está logado como superadmin
if (!isset($_SESSION['superadmin_authenticated']) || !$_SESSION['superadmin_authenticated']) {
    header('Location: superadmin_login.php');
    exit;
}

// Verificar se o diretório questoes existe, se não, criar
if (!file_exists('questoes')) {
    mkdir('questoes', 0777, true);
}

$questoes_file = 'questoes/questoes_disc.csv';
$action = $_POST['action'] ?? '';

// Função para obter o próximo ID
function getNextId($file) {
    if (!file_exists($file)) {
        return 1;
    }
    
    $fp = fopen($file, 'r');
    fgetcsv($fp); // Pular cabeçalho
    $max_id = 0;
    
    while (($data = fgetcsv($fp)) !== FALSE) {
        if ((int)$data[0] > $max_id) {
            $max_id = (int)$data[0];
        }
    }
    fclose($fp);
    
    return $max_id + 1;
}

// Verificar se todos os campos necessários foram preenchidos
if (!isset($_POST['questao']) || !isset($_POST['opcao_d']) || !isset($_POST['opcao_i']) || 
    !isset($_POST['opcao_s']) || !isset($_POST['opcao_c'])) {
    header('Location: manage_questions.php?error=missing_fields');
    exit;
}

switch ($action) {
    case 'add':
        // Criar arquivo se não existir
        $is_new_file = !file_exists($questoes_file);
        $fp = fopen($questoes_file, 'a');
        
        // Adicionar cabeçalho se for novo arquivo
        if ($is_new_file) {
            fputcsv($fp, ['ID', 'Questao', 'Opcao_D', 'Opcao_I', 'Opcao_S', 'Opcao_C']);
        }
        
        // Adicionar nova questão
        $new_id = getNextId($questoes_file);
        fputcsv($fp, [
            $new_id,
            $_POST['questao'],
            $_POST['opcao_d'],
            $_POST['opcao_i'],
            $_POST['opcao_s'],
            $_POST['opcao_c']
        ]);
        
        fclose($fp);
        header('Location: manage_questions.php?success=add');
        break;
        
    case 'edit':
        if (!isset($_POST['id'])) {
            header('Location: manage_questions.php?error=invalid_id');
            exit;
        }
        
        // Ler todas as questões
        $questoes = [];
        $fp = fopen($questoes_file, 'r');
        $header = fgetcsv($fp);
        while (($data = fgetcsv($fp)) !== FALSE) {
            if ($data[0] == $_POST['id']) {
                $questoes[] = [
                    $_POST['id'],
                    $_POST['questao'],
                    $_POST['opcao_d'],
                    $_POST['opcao_i'],
                    $_POST['opcao_s'],
                    $_POST['opcao_c']
                ];
            } else {
                $questoes[] = $data;
            }
        }
        fclose($fp);
        
        // Reescrever arquivo
        $fp = fopen($questoes_file, 'w');
        fputcsv($fp, $header);
        foreach ($questoes as $questao) {
            fputcsv($fp, $questao);
        }
        fclose($fp);
        
        header('Location: manage_questions.php?success=edit');
        break;
        
    case 'delete':
        if (!isset($_POST['id'])) {
            header('Location: manage_questions.php?error=invalid_id');
            exit;
        }
        
        // Ler todas as questões exceto a que será excluída
        $questoes = [];
        $fp = fopen($questoes_file, 'r');
        $header = fgetcsv($fp);
        while (($data = fgetcsv($fp)) !== FALSE) {
            if ($data[0] != $_POST['id']) {
                $questoes[] = $data;
            }
        }
        fclose($fp);
        
        // Reescrever arquivo
        $fp = fopen($questoes_file, 'w');
        fputcsv($fp, $header);
        foreach ($questoes as $questao) {
            fputcsv($fp, $questao);
        }
        fclose($fp);
        
        header('Location: manage_questions.php?success=delete');
        break;
        
    default:
        header('Location: manage_questions.php?error=invalid_action');
        break;
}

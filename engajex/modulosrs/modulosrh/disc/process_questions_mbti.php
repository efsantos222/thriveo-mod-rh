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

$questoes_file = 'questoes/questoes_mbti.csv';
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
if (!isset($_POST['questao']) || !isset($_POST['opcao_1']) || !isset($_POST['opcao_2']) || 
    !isset($_POST['tipo_1']) || !isset($_POST['tipo_2']) || !isset($_POST['dimensao'])) {
    header('Location: manage_questions_mbti.php?error=missing_fields');
    exit;
}

switch ($action) {
    case 'add':
        // Criar arquivo se não existir
        $is_new_file = !file_exists($questoes_file);
        $fp = fopen($questoes_file, 'a');
        
        // Adicionar cabeçalho se for novo arquivo
        if ($is_new_file) {
            fputcsv($fp, ['ID', 'Questao', 'Opcao_1', 'Tipo_1', 'Opcao_2', 'Tipo_2', 'Dimensao']);
        }
        
        // Adicionar nova questão
        $new_id = getNextId($questoes_file);
        fputcsv($fp, [
            $new_id,
            $_POST['questao'],
            $_POST['opcao_1'],
            $_POST['tipo_1'],
            $_POST['opcao_2'],
            $_POST['tipo_2'],
            $_POST['dimensao']
        ]);
        
        fclose($fp);
        header('Location: manage_questions_mbti.php?success=add');
        break;
        
    case 'edit':
        if (!isset($_POST['id'])) {
            header('Location: manage_questions_mbti.php?error=invalid_id');
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
                    $_POST['opcao_1'],
                    $_POST['tipo_1'],
                    $_POST['opcao_2'],
                    $_POST['tipo_2'],
                    $_POST['dimensao']
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
        
        header('Location: manage_questions_mbti.php?success=edit');
        break;
        
    case 'delete':
        if (!isset($_POST['id'])) {
            header('Location: manage_questions_mbti.php?error=invalid_id');
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
        
        header('Location: manage_questions_mbti.php?success=delete');
        break;
        
    default:
        header('Location: manage_questions_mbti.php?error=invalid_action');
        break;
}

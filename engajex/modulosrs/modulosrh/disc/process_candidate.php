<?php
session_start();
require_once 'functions.php';

// Verificar se está logado como admin ou superadmin
if ((!isset($_SESSION['admin_authenticated']) || !$_SESSION['admin_authenticated']) && 
    (!isset($_SESSION['superadmin_authenticated']) || !$_SESSION['superadmin_authenticated'])) {
    header('Location: admin_login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar se todos os campos obrigatórios foram preenchidos
    if (empty($_POST['nome']) || empty($_POST['email']) || empty($_POST['cargo']) || 
        empty($_POST['solicitante']) || empty($_POST['senha'])) {
        header('Location: register_candidate.php?error=missing_fields');
        exit;
    }

    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']); // Garantir que não há espaços extras
    $cargo = trim($_POST['cargo']);
    $solicitante = trim($_POST['solicitante']);
    $observacoes = isset($_POST['observacoes']) ? trim($_POST['observacoes']) : '';
    $admin_email = $_SESSION['admin_email'] ?? $_SESSION['superadmin_email'];
    $data_cadastro = date('Y-m-d H:i:s');
    
    // Verificar se o e-mail é válido
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: register_candidate.php?error=invalid_email');
        exit;
    }

    // Verificar se a senha tem pelo menos 6 caracteres
    if (strlen($senha) < 6) {
        header('Location: register_candidate.php?error=invalid_password');
        exit;
    }
    
    // Verificar se o e-mail já existe
    $candidatos_file = 'resultados/candidatos_disc.csv';
    $email_exists = false;
    
    // Criar diretório resultados se não existir
    if (!file_exists('resultados')) {
        mkdir('resultados', 0777, true);
        error_log("Diretório resultados criado");
    }
    
    if (file_exists($candidatos_file)) {
        $fp = fopen($candidatos_file, 'r');
        if ($fp !== false) {
            fgetcsv($fp); // Pular cabeçalho
            
            while (($data = fgetcsv($fp)) !== FALSE) {
                if ($data[4] === $email) {
                    $email_exists = true;
                    break;
                }
            }
            fclose($fp);
        }
    }
    
    if ($email_exists) {
        header('Location: register_candidate.php?error=email_exists');
        exit;
    }
    
    // Hash da senha
    error_log("DEBUG - Processando senha para " . $email);
    error_log("Senha original (length: " . strlen($senha) . "): " . $senha);
    
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    error_log("Hash gerado: " . $senha_hash);
    
    // Teste de verificação
    $verify_test = password_verify($senha, $senha_hash);
    error_log("Teste de verificação: " . ($verify_test ? "Sucesso" : "Falha"));
    
    // Criar arquivo se não existir
    if (!file_exists($candidatos_file)) {
        error_log("Criando arquivo candidatos_disc.csv");
        $fp = fopen($candidatos_file, 'w');
        if ($fp !== false) {
            fputcsv($fp, ['data_cadastro', 'solicitante', 'selecionador_email', 'nome', 'email', 'senha', 'cargo', 'observacoes', 'status']);
            fclose($fp);
            error_log("Cabeçalho do arquivo criado");
        } else {
            error_log("Erro ao criar arquivo candidatos_disc.csv");
            header('Location: register_candidate.php?error=file_error');
            exit;
        }
    }
    
    // Adicionar candidato
    $fp = fopen($candidatos_file, 'a');
    if ($fp !== false) {
        $dados = [
            $data_cadastro,
            $solicitante,
            $admin_email,
            $nome,
            $email,
            $senha_hash,
            $cargo,
            $observacoes,
            'pendente'
        ];
        error_log("Salvando dados do candidato:");
        error_log("Email: " . $email);
        error_log("Hash final: " . $senha_hash);
        
        if (fputcsv($fp, $dados)) {
            error_log("Dados salvos com sucesso");
            fclose($fp);
            header('Location: register_candidate.php?success=1');
            exit;
        } else {
            error_log("Erro ao salvar dados no arquivo");
            fclose($fp);
            header('Location: register_candidate.php?error=file_error');
            exit;
        }
    } else {
        error_log("Erro ao abrir arquivo para escrita");
        header('Location: register_candidate.php?error=file_error');
        exit;
    }
}

header('Location: register_candidate.php');
exit;

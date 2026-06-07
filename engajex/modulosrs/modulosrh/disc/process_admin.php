<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $confirma_senha = $_POST['confirma_senha'];
    
    // Validar senhas
    if ($senha !== $confirma_senha) {
        header('Location: register_admin.php?error=2');
        exit;
    }
    
    // Criar diretório se não existir
    if (!file_exists('resultados')) {
        mkdir('resultados', 0777, true);
    }
    
    $admin_file = 'resultados/admins.csv';
    $is_new_file = !file_exists($admin_file);
    
    // Verificar se o e-mail já existe
    if (!$is_new_file) {
        $fp = fopen($admin_file, 'r');
        fgetcsv($fp); // Pular cabeçalho
        
        while (($data = fgetcsv($fp)) !== FALSE) {
            if ($data[1] === $email) {
                fclose($fp);
                header('Location: register_admin.php?error=1');
                exit;
            }
        }
        fclose($fp);
    }
    
    // Salvar novo selecionador
    $fp = fopen($admin_file, 'a');
    
    // Se for arquivo novo, adicionar cabeçalho
    if ($is_new_file) {
        fputcsv($fp, ['nome', 'email', 'senha']);
    }
    
    // Criptografar a senha
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    
    // Adicionar novo selecionador
    fputcsv($fp, [
        $nome,
        $email,
        $senha_hash
    ]);
    
    fclose($fp);
    
    header('Location: register_admin.php?success=1');
    exit;
}

header('Location: register_admin.php');
exit;

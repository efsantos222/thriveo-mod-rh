<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);
    
    // Log file for debugging
    $log_file = 'debug_login.log';
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Login attempt for email: $email\n", FILE_APPEND);
    
    // Verificar credenciais do superadmin
    if ($email === 'recursoshumanos@sysmanager.com.br' && $senha === 'SysManager25') {
        // Redirecionar para o login de superadmin
        header('Location: superadmin_login.php');
        exit;
    }
    
    $pending_tests = [];
    $nome_candidato = '';
    
    // Verificar cada tipo de teste
    $test_files = [
        'disc' => 'resultados/candidatos.csv',
        'mbti' => 'resultados/candidatos_mbti.csv',
        'bigfive' => 'resultados/candidatos_bigfive.csv',
        'jss' => 'resultados/candidatos_jss.csv'
    ];
    
    foreach ($test_files as $test_type => $file_path) {
        if (file_exists($file_path)) {
            $fp = fopen($file_path, 'r');
            if ($fp !== false) {
                $header = fgetcsv($fp); // Pular cabeçalho
                while (($data = fgetcsv($fp)) !== FALSE) {
                    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Checking $test_type file, found email: {$data[4]}\n", FILE_APPEND);
                    if ($data[4] === $email && password_verify($senha, $data[5])) {
                        $nome_candidato = $data[3]; // Guardar o nome do candidato
                        if (strtolower(trim($data[8])) === 'pendente') {
                            $pending_tests[] = $test_type;
                            file_put_contents($log_file, date('Y-m-d H:i:s') . " - Found pending $test_type test\n", FILE_APPEND);
                        }
                    }
                }
                fclose($fp);
            }
        }
    }
    
    if (!empty($nome_candidato)) {
        $_SESSION['authenticated'] = true;
        $_SESSION['email'] = $email;
        $_SESSION['nome'] = $nome_candidato;
        
        if (!empty($pending_tests)) {
            // Redirecionar para o primeiro teste pendente
            $first_test = $pending_tests[0];
            file_put_contents($log_file, date('Y-m-d H:i:s') . " - Redirecting to $first_test test\n", FILE_APPEND);
            header('Location: test_' . $first_test . '.php');
        } else {
            // Se não houver testes pendentes, redirecionar para a página de agradecimento
            header('Location: thank_you.php');
        }
        exit;
    }
    
    // Se chegou aqui, as credenciais são inválidas
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Invalid credentials\n", FILE_APPEND);
    header('Location: login.php?error=1');
    exit;
}

// Se não for POST, redirecionar para a página de login
header('Location: login.php');
exit;

<?php
session_start();

function checkTestStatus($email, $test_type) {
    $avaliacao_file = 'resultados/' . str_replace(['@', '.'], '_', $email) . '_avaliacao' . 
                      ($test_type === 'disc' ? '' : '_' . $test_type) . '.csv';
    return file_exists($avaliacao_file);
}

function getTestInfo($email) {
    $tests = [];
    
    // Verificar DISC
    $disc_file = 'resultados/candidatos_disc.csv';
    if (file_exists($disc_file)) {
        $fp = fopen($disc_file, 'r');
        if ($fp !== false) {
            fgetcsv($fp); // Pular cabeçalho
            while (($data = fgetcsv($fp)) !== FALSE) {
                if ($data[4] === $email) {
                    $tests[] = [
                        'type' => 'DISC',
                        'status' => checkTestStatus($email, 'disc') ? 'completed' : 'pending',
                        'url' => 'test_disc.php'
                    ];
                }
            }
            fclose($fp);
        }
    }
    
    // Verificar MBTI
    $mbti_file = 'resultados/candidatos_mbti.csv';
    if (file_exists($mbti_file)) {
        $fp = fopen($mbti_file, 'r');
        if ($fp !== false) {
            fgetcsv($fp); // Pular cabeçalho
            while (($data = fgetcsv($fp)) !== FALSE) {
                if ($data[4] === $email) {
                    $tests[] = [
                        'type' => 'MBTI',
                        'status' => checkTestStatus($email, 'mbti') ? 'completed' : 'pending',
                        'url' => 'test_mbti.php'
                    ];
                }
            }
            fclose($fp);
        }
    }
    
    // Verificar Big Five
    $bigfive_file = 'resultados/candidatos_bigfive.csv';
    if (file_exists($bigfive_file)) {
        $fp = fopen($bigfive_file, 'r');
        if ($fp !== false) {
            fgetcsv($fp); // Pular cabeçalho
            while (($data = fgetcsv($fp)) !== FALSE) {
                if ($data[4] === $email) {
                    $tests[] = [
                        'type' => 'Big Five',
                        'status' => checkTestStatus($email, 'bigfive') ? 'completed' : 'pending',
                        'url' => 'test_bigfive.php'
                    ];
                }
            }
            fclose($fp);
        }
    }
    
    // Verificar JSS
    $jss_file = 'resultados/candidatos_jss.csv';
    if (file_exists($jss_file)) {
        $fp = fopen($jss_file, 'r');
        if ($fp !== false) {
            fgetcsv($fp); // Pular cabeçalho
            while (($data = fgetcsv($fp)) !== FALSE) {
                if ($data[4] === $email) {
                    $tests[] = [
                        'type' => 'JSS',
                        'status' => checkTestStatus($email, 'jss') ? 'completed' : 'pending',
                        'url' => 'test_jss.php'
                    ];
                }
            }
            fclose($fp);
        }
    }
    
    return $tests;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    
    if (empty($email) || empty($senha)) {
        header('Location: login.php?error=empty_fields');
        exit;
    }
    
    $tests = getTestInfo($email);
    $user_found = false;
    $user_name = '';
    
    // Verificar em todos os arquivos de candidatos
    $candidate_files = [
        'resultados/candidatos_disc.csv',
        'resultados/candidatos_mbti.csv',
        'resultados/candidatos_bigfive.csv',
        'resultados/candidatos_jss.csv'
    ];
    
    foreach ($candidate_files as $file) {
        if (file_exists($file)) {
            $fp = fopen($file, 'r');
            if ($fp !== false) {
                fgetcsv($fp); // Pular cabeçalho
                while (($data = fgetcsv($fp)) !== FALSE) {
                    if ($data[4] === $email) {
                        if (password_verify($senha, $data[5])) {
                            $user_found = true;
                            $user_name = $data[3];
                            break 2;
                        }
                    }
                }
                fclose($fp);
            }
        }
    }
    
    if (!$user_found) {
        header('Location: login.php?error=invalid_credentials');
        exit;
    }
    
    // Se não houver testes disponíveis
    if (empty($tests)) {
        header('Location: login.php?error=no_tests');
        exit;
    }
    
    // Se todos os testes já foram completados
    $all_completed = true;
    foreach ($tests as $test) {
        if ($test['status'] === 'pending') {
            $all_completed = false;
            break;
        }
    }
    
    if ($all_completed) {
        header('Location: login.php?error=all_completed');
        exit;
    }
    
    // Login bem sucedido
    $_SESSION['user_authenticated'] = true;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name'] = $user_name;
    $_SESSION['available_tests'] = $tests;
    
    // Redirecionar para a página de seleção de teste
    header('Location: select_test.php');
    exit;
} else {
    header('Location: login.php');
    exit;
}

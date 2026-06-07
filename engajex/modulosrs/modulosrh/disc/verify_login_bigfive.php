<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'debug.log');

// Função para registrar mensagens de debug
function debug_log($message) {
    error_log("[" . date('d-M-Y H:i:s') . " " . date_default_timezone_get() . "] DEBUG - " . $message . "\n", 3, "debug.log");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);
    
    debug_log("Tentativa de login Big Five:");
    debug_log("Email fornecido: " . $email);
    debug_log("Senha fornecida (length: " . strlen($senha) . "): " . $senha);

    $senhas_bigfive_file = 'senhas/senhas_bigfive.csv';
    $candidatos_bigfive_file = 'resultados/candidatos_bigfive.csv';
    
    if (file_exists($senhas_bigfive_file) && file_exists($candidatos_bigfive_file)) {
        // Primeiro verificar a senha
        $senha_correta = false;
        $fp_senhas = fopen($senhas_bigfive_file, 'r');
        if ($fp_senhas !== false) {
            $header = fgetcsv($fp_senhas);
            debug_log("Cabeçalho do arquivo de senhas Big Five: " . implode(", ", $header));
            
            while (($data = fgetcsv($fp_senhas)) !== FALSE) {
                if ($data[0] === $email && $data[1] === $senha) {
                    $senha_correta = true;
                    debug_log("Senha correta encontrada para: " . $email);
                    break;
                }
            }
            fclose($fp_senhas);
        }
        
        // Se a senha estiver correta, buscar dados do candidato
        if ($senha_correta) {
            $fp_candidatos = fopen($candidatos_bigfive_file, 'r');
            if ($fp_candidatos !== false) {
                $header = fgetcsv($fp_candidatos);
                debug_log("Cabeçalho do arquivo de candidatos Big Five: " . implode(", ", $header));
                
                while (($data = fgetcsv($fp_candidatos)) !== FALSE) {
                    if ($data[4] === $email) { // Email está na quinta coluna
                        $status = isset($data[7]) ? strtolower(trim($data[7])) : 'pendente';
                        debug_log("Status do candidato: " . $status);
                        
                        if ($status === 'completed') {
                            debug_log("Teste já concluído para: " . $email);
                            header('Location: login_bigfive.php?error=test_completed');
                            exit;
                        }
                        
                        $_SESSION['bigfive_authenticated'] = true;
                        $_SESSION['bigfive_email'] = $email;
                        $_SESSION['bigfive_nome'] = $data[3];
                        
                        $_SESSION['user_data'] = [
                            'data_criacao' => $data[0],
                            'selecionador_nome' => $data[1],
                            'selecionador_email' => $data[2],
                            'candidato_nome' => $data[3],
                            'candidato_email' => $data[4],
                            'empresa' => $data[5],
                            'cargo' => $data[6],
                            'status' => $status
                        ];
                        
                        debug_log("Login bem-sucedido. Redirecionando para bigfive_test.php");
                        header('Location: bigfive_test.php');
                        exit;
                    }
                }
                fclose($fp_candidatos);
                debug_log("Candidato não encontrado no arquivo de candidatos");
            }
        } else {
            debug_log("Senha incorreta para: " . $email);
        }
    } else {
        debug_log("Arquivos não encontrados: senhas=" . file_exists($senhas_bigfive_file) . ", candidatos=" . file_exists($candidatos_bigfive_file));
    }
}

debug_log("Login falhou. Redirecionando para login_bigfive.php");
header('Location: login_bigfive.php?error=invalid');
exit;

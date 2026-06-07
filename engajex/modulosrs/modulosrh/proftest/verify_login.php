<?php
session_start();
require_once 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']); // Garantir que não há espaços extras
    
    error_log("DEBUG - Tentativa de login:");
    error_log("Email fornecido: " . $email);
    error_log("Senha fornecida (length: " . strlen($senha) . "): " . $senha);
    
    // Verificar no arquivo de candidatos DISC
    $candidatos_file = 'resultados/candidatos.csv';
    $autenticado = false;
    $user_type = '';
    
    if (file_exists($candidatos_file)) {
        $fp = fopen($candidatos_file, 'r');
        if ($fp !== false) {
            $header = fgetcsv($fp); // Pular cabeçalho
            error_log("Cabeçalho do arquivo: " . implode(", ", $header));
            
            // Encontrar o índice da coluna de status
            $status_index = 8; // Índice padrão
            foreach ($header as $index => $column) {
                if (strtolower(trim($column)) === 'status') {
                    $status_index = $index;
                    break;
                }
            }
            error_log("Índice da coluna de status: " . $status_index);
            
            while (($data = fgetcsv($fp)) !== FALSE) {
                if ($data[4] === $email) {
                    error_log("Email encontrado para: " . $email);
                    error_log("Hash armazenado: " . $data[5]);
                    error_log("Dados completos do usuário DISC: " . implode(", ", $data));
                    
                    // Verificar se a senha corresponde
                    $senha_correta = password_verify($senha, $data[5]);
                    error_log("Resultado da verificação de senha: " . ($senha_correta ? "Correta" : "Incorreta"));
                    
                    if ($senha_correta) {
                        // Verificar se o teste já foi concluído
                        $status = isset($data[$status_index]) ? strtolower(trim($data[$status_index])) : '';
                        error_log("Status do teste: " . $status);
                        
                        if ($status === 'completed') {
                            error_log("Teste DISC já foi concluído para este usuário (status: " . $status . ")");
                            header('Location: login.php?error=test_completed');
                            exit;
                        }
                        
                        error_log("Login bem sucedido!");
                        $autenticado = true;
                        $user_type = 'disc';
                        $_SESSION['authenticated'] = true;
                        $_SESSION['user_type'] = 'disc';
                        $_SESSION['questao_atual'] = 0;
                        
                        // Armazenar todos os dados do usuário na sessão
                        $_SESSION['user_data'] = [
                            'data_criacao' => $data[0],
                            'selecionador_nome' => $data[1],
                            'selecionador_email' => $data[2],
                            'candidato_nome' => $data[3],
                            'candidato_email' => $data[4],
                            'cargo' => isset($data[6]) ? $data[6] : '',
                            'observacoes' => isset($data[7]) ? $data[7] : '',
                            'status' => isset($data[8]) ? $data[8] : 'pendente'
                        ];
                        break;
                    } else {
                        error_log("Senha incorreta para o usuário");
                    }
                }
            }
            fclose($fp);
        }
    } else {
        error_log("Arquivo de candidatos não encontrado: " . $candidatos_file);
    }
    
    // Se não encontrou no DISC, verificar no MBTI
    if (!$autenticado) {
        $candidatos_mbti_file = 'resultados/candidatos_mbti.csv';
        
        if (file_exists($candidatos_mbti_file)) {
            $fp = fopen($candidatos_mbti_file, 'r');
            if ($fp !== false) {
                $header = fgetcsv($fp); // Pular cabeçalho
                error_log("Cabeçalho do arquivo MBTI: " . implode(", ", $header));
                
                // Encontrar o índice da coluna de status
                $status_index = 8; // Índice padrão
                foreach ($header as $index => $column) {
                    if (strtolower(trim($column)) === 'status') {
                        $status_index = $index;
                        break;
                    }
                }
                error_log("Índice da coluna de status: " . $status_index);
                
                while (($data = fgetcsv($fp)) !== FALSE) {
                    if ($data[4] === $email) {
                        error_log("Email encontrado para: " . $email);
                        error_log("Hash armazenado MBTI: " . $data[5]);
                        error_log("Dados completos do usuário MBTI: " . implode(", ", $data));
                        
                        // Verificar se a senha corresponde
                        if (password_verify($senha, $data[5])) {
                            // Verificar se o teste já foi concluído
                            $status = isset($data[$status_index]) ? strtolower(trim($data[$status_index])) : '';
                            error_log("Status do teste MBTI: " . $status);
                            
                            if ($status === 'completed') {
                                error_log("Teste MBTI já foi concluído para este usuário");
                                header('Location: login.php?error=test_completed');
                                exit;
                            }
                            
                            $autenticado = true;
                            $user_type = 'mbti';
                            $_SESSION['authenticated'] = true;
                            $_SESSION['user_type'] = 'mbti';
                            $_SESSION['questao_atual'] = 0;
                            
                            // Armazenar todos os dados do usuário na sessão
                            $_SESSION['user_data'] = [
                                'data_criacao' => $data[0],
                                'selecionador_nome' => $data[1],
                                'selecionador_email' => $data[2],
                                'candidato_nome' => $data[3],
                                'candidato_email' => $data[4],
                                'cargo' => isset($data[6]) ? $data[6] : '',
                                'observacoes' => isset($data[7]) ? $data[7] : '',
                                'status' => isset($data[8]) ? $data[8] : 'pendente'
                            ];
                            break;
                        }
                    }
                }
                fclose($fp);
            }
        }
    }
    
    if ($autenticado) {
        if ($user_type === 'disc') {
            header('Location: question.php');
        } else {
            header('Location: mbti_question.php');
        }
        exit;
    }
}

header('Location: login.php?error=invalid');
exit;

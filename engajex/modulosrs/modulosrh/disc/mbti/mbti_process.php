<?php
session_start();
require_once '../functions.php';

if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resposta'])) {
    if (!isset($_SESSION['mbti_questao_atual'])) {
        $_SESSION['mbti_questao_atual'] = 0;
    }
    if (!isset($_SESSION['mbti_respostas'])) {
        $_SESSION['mbti_respostas'] = [];
    }

    // Salvar resposta atual
    $_SESSION['mbti_respostas'][$_SESSION['mbti_questao_atual']] = $_POST['resposta'];
    
    // Incrementar questão atual
    $_SESSION['mbti_questao_atual']++;
    
    // Carregar todas as questões para verificar se terminou
    $questoes_file = '../questoes/questoes_mbti.csv';
    $total_questoes = 0;
    
    if (file_exists($questoes_file)) {
        if (($handle = fopen($questoes_file, 'r')) !== FALSE) {
            while (fgetcsv($handle) !== FALSE) {
                $total_questoes++;
            }
            fclose($handle);
        }
        $total_questoes--; // Descontar o cabeçalho
    }
    
    // Se completou todas as questões
    if ($_SESSION['mbti_questao_atual'] >= $total_questoes) {
        // Calcular resultado MBTI
        $resultado = calcularResultadoMBTI($_SESSION['mbti_respostas']);
        
        // Salvar resultado
        if (isset($_SESSION['user_data'])) {
            $data = date('Y-m-d H:i:s');
            $email = $_SESSION['user_data']['candidato_email'];
            
            // Salvar no arquivo de avaliações MBTI
            $avaliacao_file = '../resultados/' . str_replace(['@', '.'], '_', $email) . '_avaliacao_mbti.csv';
            
            // Criar cabeçalho se o arquivo não existir
            if (!file_exists($avaliacao_file)) {
                $fp = fopen($avaliacao_file, 'w');
                fputcsv($fp, ['data_conclusao', 'tipo_mbti', 'pontuacao_ei', 'pontuacao_sn', 'pontuacao_tf', 'pontuacao_jp', 'respostas']);
                fclose($fp);
            }
            
            // Adicionar resultado
            $fp = fopen($avaliacao_file, 'a');
            fputcsv($fp, [
                $data,
                $resultado['tipo'],
                $resultado['pontuacao']['E/I'],
                $resultado['pontuacao']['S/N'],
                $resultado['pontuacao']['T/F'],
                $resultado['pontuacao']['J/P'],
                json_encode($_SESSION['mbti_respostas'])
            ]);
            fclose($fp);
            
            // Atualizar status no arquivo de candidatos
            $candidatos_file = '../resultados/candidatos_mbti.csv';
            $candidatos_temp = [];
            $updated = false;
            
            if (file_exists($candidatos_file)) {
                if (($handle = fopen($candidatos_file, 'r')) !== FALSE) {
                    $header = fgetcsv($handle);
                    $candidatos_temp[] = $header;
                    
                    while (($data = fgetcsv($handle)) !== FALSE) {
                        if ($data[4] === $email) {
                            $data[8] = 'completed'; // Atualizar status
                            $updated = true;
                        }
                        $candidatos_temp[] = $data;
                    }
                    fclose($handle);
                }
                
                if ($updated) {
                    $fp = fopen($candidatos_file, 'w');
                    foreach ($candidatos_temp as $linha) {
                        fputcsv($fp, $linha);
                    }
                    fclose($fp);
                }
            }
        }
        
        // Limpar variáveis de sessão do questionário
        unset($_SESSION['mbti_questao_atual']);
        unset($_SESSION['mbti_respostas']);
        
        // Redirecionar para página de agradecimento
        header('Location: mbti_thank_you.php');
        exit;
    }
    
    // Se não terminou, continuar para próxima questão
    header('Location: mbti_question.php');
    exit;
}

// Se chegou aqui sem POST válido
header('Location: mbti_question.php');
exit;

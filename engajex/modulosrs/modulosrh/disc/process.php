<?php
session_start();
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['selecionador_nome'])) {
        // Processamento do formulário inicial
        $senha = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
        $_SESSION['user_data'] = [
            'selecionador_nome' => $_POST['selecionador_nome'],
            'selecionador_email' => $_POST['selecionador_email'],
            'candidato_nome' => $_POST['candidato_nome'],
            'candidato_email' => $_POST['candidato_email'],
            'senha' => $senha,
            'data_criacao' => date('Y-m-d H:i:s')
        ];
        
        // Salvar dados do candidato
        if (!file_exists('resultados')) {
            mkdir('resultados', 0777, true);
        }
        
        $candidatos_file = 'resultados/candidatos_disc.csv';
        $is_new_file = !file_exists($candidatos_file);
        
        $fp = fopen($candidatos_file, 'a');
        
        // Se for um arquivo novo, adiciona o cabeçalho
        if ($is_new_file) {
            fputcsv($fp, ['Data', 'Selecionador Nome', 'Selecionador Email', 'Candidato Nome', 'Candidato Email', 'Senha', 'Cargo', 'Observacoes', 'Status']);
        }
        
        // Adiciona os dados do novo candidato
        fputcsv($fp, [
            $_SESSION['user_data']['data_criacao'],
            $_SESSION['user_data']['selecionador_nome'],
            $_SESSION['user_data']['selecionador_email'],
            $_SESSION['user_data']['candidato_nome'],
            $_SESSION['user_data']['candidato_email'],
            password_hash($_SESSION['user_data']['senha'], PASSWORD_DEFAULT),
            $_POST['cargo'] ?? '',
            $_POST['observacoes'] ?? '',
            'pendente'
        ]);
        
        fclose($fp);
        
        // Enviar e-mail para o candidato com a senha
        $subject = 'Acesso para Avaliação DISC';
        $body = "Olá {$_POST['candidato_nome']},<br><br>";
        $body .= "Você foi convidado para participar de uma avaliação DISC.<br>";
        $body .= "Sua senha de acesso é: <strong>{$senha}</strong><br>";
        $body .= "Acesse o link: http://seudominio.com/disc/login.php<br><br>";
        $body .= "Atenciosamente,<br>Sistema de Avaliação DISC";
        
        $headers = 'From: sistema@seudominio.com' . "\r\n" .
            'Reply-To: sistema@seudominio.com' . "\r\n" .
            'Content-Type: text/html; charset=UTF-8' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
        
        mail($_POST['candidato_email'], $subject, $body, $headers);
        
        header('Location: login.php');
        exit;
    } elseif (isset($_POST['resposta'])) {
        if (!isset($_SESSION['respostas'])) {
            $_SESSION['respostas'] = [];
        }

        $questions = getQuestions();
        $questao_atual = isset($_SESSION['questao_atual']) ? $_SESSION['questao_atual'] : 0;
        
        // Salvar resposta atual
        $_SESSION['respostas'][$questao_atual] = $_POST['resposta'];
        
        // Incrementar questão atual
        $_SESSION['questao_atual'] = $questao_atual + 1;
        
        // Verificar se chegou ao fim do questionário
        if ($_SESSION['questao_atual'] >= count($questions)) {
            // Processar resultados finais
            $analise = analyzeResponses($_SESSION['respostas']);
            
            // Salvar resultados
            // Criar diretório resultados se não existir
            if (!file_exists('resultados')) {
                mkdir('resultados', 0777, true);
            }
            
            // Gerar gráfico
            $width = 500;
            $height = 400;
            $img = imagecreatetruecolor($width, $height);
            
            // Cores
            $white = imagecolorallocate($img, 255, 255, 255);
            $black = imagecolorallocate($img, 0, 0, 0);
            $colors = [
                imagecolorallocate($img, 255, 99, 71),  // Vermelho (D)
                imagecolorallocate($img, 255, 215, 0),  // Amarelo (I)
                imagecolorallocate($img, 34, 139, 34),  // Verde (S)
                imagecolorallocate($img, 0, 0, 255)     // Azul (C)
            ];
            
            // Preencher fundo
            imagefilledrectangle($img, 0, 0, $width, $height, $white);
            
            // Desenhar barras
            $bar_width = 80;
            $spacing = 40;
            $x = 60;
            
            foreach ($analise['grafico_data']['values'] as $i => $value) {
                $bar_height = ($value / 100) * 300;
                $y = $height - 50 - $bar_height;
                
                imagefilledrectangle(
                    $img,
                    $x,
                    $y,
                    $x + $bar_width,
                    $height - 50,
                    $colors[$i]
                );
                
                // Adicionar rótulos
                $label = $analise['grafico_data']['labels'][$i];
                imagestring($img, 5, $x + 30, $height - 30, $label, $black);
                imagestring($img, 3, $x + 25, $y - 20, $value.'%', $black);
                
                $x += $bar_width + $spacing;
            }
            
            // Salvar gráfico
            $grafico_path = 'resultados/' . str_replace(['@', '.'], '_', $_SESSION['user_data']['candidato_email']) . '_grafico.png';
            imagepng($img, $grafico_path);
            imagedestroy($img);
            
            // Salvar resultados em CSV
            $filename = 'resultados/' . str_replace(['@', '.'], '_', $_SESSION['user_data']['candidato_email']) . '_avaliacao.csv';
            $fp = fopen($filename, 'w');
            
            // Escrever cabeçalho
            fputcsv($fp, ['Campo', 'Valor']);
            
            // Escrever dados
            fputcsv($fp, ['Nome do Candidato', $_SESSION['user_data']['candidato_nome']]);
            fputcsv($fp, ['E-mail do Candidato', $_SESSION['user_data']['candidato_email']]);
            fputcsv($fp, ['Data da Avaliação', date('Y-m-d H:i:s')]);
            fputcsv($fp, ['Respostas', json_encode($_SESSION['respostas'])]);
            fputcsv($fp, ['Perfil D', $analise['perfil']['D']]);
            fputcsv($fp, ['Perfil I', $analise['perfil']['I']]);
            fputcsv($fp, ['Perfil S', $analise['perfil']['S']]);
            fputcsv($fp, ['Perfil C', $analise['perfil']['C']]);
            fputcsv($fp, ['Perfil Predominante', $analise['perfil_predominante']]);
            fputcsv($fp, ['Recomendação', $analise['recomendacao']]);
            
            fclose($fp);
            
            // Atualizar status no arquivo de candidatos
            $candidatos_file = 'resultados/candidatos_disc.csv';
            $temp_file = 'resultados/candidatos_temp.csv';
            
            if (file_exists($candidatos_file)) {
                $fp_read = fopen($candidatos_file, 'r');
                $fp_write = fopen($temp_file, 'w');
                
                if ($fp_read !== false && $fp_write !== false) {
                    // Copiar cabeçalho
                    $header = fgetcsv($fp_read);
                    fputcsv($fp_write, $header);
                    
                    // Atualizar dados do candidato
                    while (($data = fgetcsv($fp_read)) !== FALSE) {
                        if ($data[4] === $_SESSION['user_data']['candidato_email']) {
                            $data[8] = 'completed'; // Atualizar status
                        }
                        fputcsv($fp_write, $data);
                    }
                    
                    fclose($fp_read);
                    fclose($fp_write);
                    
                    // Substituir arquivo original
                    rename($temp_file, $candidatos_file);
                }
            }
            
            // Enviar e-mail para o selecionador
            $subject = 'Resultado da Avaliação DISC - ' . $_SESSION['user_data']['candidato_nome'];
            $body = "Olá,<br><br>";
            $body .= "A avaliação DISC do candidato {$_SESSION['user_data']['candidato_nome']} foi concluída.<br><br>";
            $body .= "<h3>Perfil DISC:</h3>";
            $body .= "Dominância (D): {$analise['perfil']['D']}%<br>";
            $body .= "Influência (I): {$analise['perfil']['I']}%<br>";
            $body .= "Estabilidade (S): {$analise['perfil']['S']}%<br>";
            $body .= "Conformidade (C): {$analise['perfil']['C']}%<br><br>";
            $body .= "<strong>Perfil Predominante:</strong> {$analise['perfil_predominante']}<br><br>";
            $body .= "<strong>Recomendação:</strong><br>{$analise['recomendacao']}<br><br>";
            $body .= "Para mais detalhes, acesse o sistema.<br><br>";
            $body .= "Atenciosamente,<br>Sistema de Avaliação DISC";
            
            $headers = 'From: sistema@seudominio.com' . "\r\n" .
                'Reply-To: sistema@seudominio.com' . "\r\n" .
                'Content-Type: text/html; charset=UTF-8' . "\r\n" .
                'X-Mailer: PHP/' . phpversion();
            
            mail($_SESSION['user_data']['selecionador_email'], $subject, $body, $headers);
            
            // Redirecionar para página de agradecimento
            header('Location: thank_you.php');
            exit;
        }
        
        // Se não chegou ao fim, continuar para próxima questão
        header('Location: question.php');
        exit;
    }
}

// Se chegou aqui sem POST válido, redirecionar para login
if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
    header('Location: login.php');
    exit;
}

<?php

function generateUniquePassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $password;
}

function saveToExcel($data, $filename) {
    $f = fopen($filename, 'w');
    
    fputcsv($f, array_keys($data));
    
    fputcsv($f, array_values($data));
    
    fclose($f);
}

function sendEmail($to, $subject, $body, $attachments = []) {
    $from = "seu-email@seudominio.com"; 
    
    $boundary = md5(time());
    
    $headers = "From: $from\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";
    
    $message = "--$boundary\r\n";
    $message .= "Content-Type: text/html; charset=UTF-8\r\n";
    $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $message .= $body . "\r\n\r\n";
    
    if (!is_array($attachments)) {
        $attachments = [$attachments];
    }
    
    foreach ($attachments as $file) {
        if (file_exists($file)) {
            $content = file_get_contents($file);
            $content = chunk_split(base64_encode($content));
            
            $message .= "--$boundary\r\n";
            $message .= "Content-Type: application/octet-stream; name=\"".basename($file)."\"\r\n";
            $message .= "Content-Transfer-Encoding: base64\r\n";
            $message .= "Content-Disposition: attachment; filename=\"".basename($file)."\"\r\n\r\n";
            $message .= $content."\r\n\r\n";
        }
    }
    
    $message .= "--$boundary--";
    
    return mail($to, $subject, $message, $headers);
}

function getQuestions() {
    $questions = [];
    $questoes_file = 'questoes/questoes_disc.csv';
    
    if (file_exists($questoes_file)) {
        $fp = fopen($questoes_file, 'r');
        fgetcsv($fp); // Pular cabeçalho
        
        while (($data = fgetcsv($fp)) !== FALSE) {
            $questions[] = [
                'id' => $data[0],
                'pergunta' => $data[1],
                'opcoes' => [
                    'a' => $data[2], // Opção D
                    'b' => $data[3], // Opção I
                    'c' => $data[4], // Opção S
                    'd' => $data[5]  // Opção C
                ],
                'disc' => [
                    'a' => 'D',
                    'b' => 'I',
                    'c' => 'S',
                    'd' => 'C'
                ]
            ];
        }
        fclose($fp);
    }
    
    // Se não houver questões no arquivo, retorna as questões padrão
    if (empty($questions)) {
        return [
            [
                'id' => 1,
                'pergunta' => 'Como você prefere trabalhar?',
                'opcoes' => [
                    'a' => 'Assumindo o controle e liderando',
                    'b' => 'Interagindo e motivando pessoas',
                    'c' => 'Cooperando e mantendo a harmonia',
                    'd' => 'Seguindo processos e regras'
                ],
                'disc' => ['a' => 'D', 'b' => 'I', 'c' => 'S', 'd' => 'C']
            ],
            [
                'id' => 2,
                'pergunta' => 'Em uma reunião de equipe, eu normalmente...',
                'opcoes' => [
                    'a' => 'Vou direto ao ponto e foco nos resultados.',
                    'b' => 'Trago energia, ideias criativas e tento contagiar o grupo.',
                    'c' => 'Escuto os colegas e ofereço ajuda para facilitar a harmonia.',
                    'd' => 'Estruturo tópicos e organizo as informações de forma lógica.'
                ],
                'disc' => ['a' => 'D', 'b' => 'I', 'c' => 'S', 'd' => 'C']
            ],
            [
                'id' => 3,
                'pergunta' => 'Se alguém me descrevesse, gostaria que dissesse que sou...',
                'opcoes' => [
                    'a' => 'Determinado, confiante e focado em resultados.',
                    'b' => 'Comunicativo, inspirador e envolvente.',
                    'c' => 'Calmo, cooperativo e sempre disposto a ajudar.',
                    'd' => 'Perfeccionista, lógico e de alta precisão.'
                ],
                'disc' => ['a' => 'D', 'b' => 'I', 'c' => 'S', 'd' => 'C']
            ]
        ];
    }
    
    return $questions;
}

function analyzeResponses($respostas) {
    $questions = getQuestions();
    $perfil = ['D' => 0, 'I' => 0, 'S' => 0, 'C' => 0];
    
    if (empty($respostas)) {
        return [
            'perfil' => $perfil,
            'perfil_predominante' => 'D',
            'recomendacao' => 'Não foi possível analisar o perfil.',
            'grafico_data' => [
                'labels' => ['D', 'I', 'S', 'C'],
                'values' => [0, 0, 0, 0]
            ]
        ];
    }
    
    $total_questoes = count($respostas);
    
    // Processar cada resposta
    foreach ($respostas as $questao_index => $resposta) {
        if (isset($questions[$questao_index]) && isset($questions[$questao_index]['disc'][$resposta])) {
            $tipo_disc = $questions[$questao_index]['disc'][$resposta];
            $perfil[$tipo_disc]++;
        }
    }
    
    // Calcular percentuais
    foreach ($perfil as $tipo => $contagem) {
        $perfil[$tipo] = round(($contagem / $total_questoes) * 100, 2);
    }
    
    // Ordenar perfil do maior para o menor
    arsort($perfil);
    $perfil_predominante = array_key_first($perfil);
    
    $recomendacoes = [
        'D' => 'Perfil dominante: Focado em resultados, direto e decisivo. Recomenda-se aproveitar sua capacidade de liderança e tomada rápida de decisões, mas atentar para incluir a equipe no processo.',
        'I' => 'Perfil influente: Comunicativo, entusiasta e motivador. Recomenda-se aproveitar sua habilidade de influenciar e motivar pessoas, mas manter foco nos objetivos e prazos.',
        'S' => 'Perfil estável: Cooperativo, paciente e confiável. Recomenda-se aproveitar sua capacidade de manter harmonia e estabilidade, mas desenvolver mais adaptabilidade a mudanças.',
        'C' => 'Perfil conforme: Analítico, preciso e sistemático. Recomenda-se aproveitar sua atenção aos detalhes e organização, mas trabalhar a flexibilidade e agilidade na tomada de decisões.'
    ];
    
    return [
        'perfil' => $perfil,
        'perfil_predominante' => $perfil_predominante,
        'recomendacao' => $recomendacoes[$perfil_predominante],
        'grafico_data' => [
            'labels' => array_keys($perfil),
            'values' => array_values($perfil)
        ]
    ];
}

?>

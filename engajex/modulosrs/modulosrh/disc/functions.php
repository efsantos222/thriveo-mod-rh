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
        if (($handle = fopen($questoes_file, 'r')) !== FALSE) {
            // Pular o cabeçalho
            fgetcsv($handle);
            
            while (($data = fgetcsv($handle)) !== FALSE) {
                if (count($data) >= 6) { // Garantir que tem todas as colunas necessárias
                    $questions[] = [
                        'id' => intval($data[0]),
                        'question' => $data[1],
                        'options' => [
                            'A' => ['text' => $data[2], 'type' => 'D'],
                            'B' => ['text' => $data[3], 'type' => 'I'],
                            'C' => ['text' => $data[4], 'type' => 'S'],
                            'D' => ['text' => $data[5], 'type' => 'C']
                        ]
                    ];
                }
            }
            fclose($handle);
            return $questions;
        }
    }
    
    return [];
}

function analyzeResponses($respostas) {
    $perfil = ['D' => 0, 'I' => 0, 'S' => 0, 'C' => 0];
    
    if (empty($respostas)) {
        return [
            'perfil' => $perfil,
            'perfil_predominante' => 'N/A',
            'recomendacao' => 'Não foi possível analisar o perfil.',
            'grafico_data' => [
                'labels' => ['D', 'I', 'S', 'C'],
                'values' => [0, 0, 0, 0]
            ]
        ];
    }
    
    foreach ($respostas as $tipo) {
        if (isset($perfil[$tipo])) {
            $perfil[$tipo]++;
        }
    }
    
    $total = array_sum($perfil);
    foreach ($perfil as &$valor) {
        $valor = ($total > 0) ? round(($valor / $total) * 100) : 0;
    }
    
    $perfil_predominante = array_search(max($perfil), $perfil);
    
    $recomendacoes = [
        'D' => 'Perfil Dominante: Focado em resultados e ação rápida. Recomenda-se delegar detalhes e desenvolver mais paciência.',
        'I' => 'Perfil Influente: Comunicativo e entusiasta. Recomenda-se focar mais em detalhes e desenvolver a escuta ativa.',
        'S' => 'Perfil Estável: Cooperativo e paciente. Recomenda-se desenvolver mais proatividade e adaptabilidade a mudanças.',
        'C' => 'Perfil Conforme: Analítico e preciso. Recomenda-se desenvolver mais flexibilidade e agilidade na tomada de decisões.'
    ];
    
    return [
        'perfil' => $perfil,
        'perfil_predominante' => $perfil_predominante,
        'recomendacao' => $recomendacoes[$perfil_predominante] ?? 'Perfil não identificado.',
        'grafico_data' => [
            'labels' => ['D', 'I', 'S', 'C'],
            'values' => array_values($perfil)
        ]
    ];
}

function calcularResultadoMBTI($respostas) {
    // Inicializar contadores para cada dimensão
    $dimensoes = [
        'E' => 0, 'I' => 0, // Extroversão vs. Introversão
        'S' => 0, 'N' => 0, // Sensação vs. Intuição
        'T' => 0, 'F' => 0, // Pensamento vs. Sentimento
        'J' => 0, 'P' => 0  // Julgamento vs. Percepção
    ];
    
    // Carregar questões do arquivo CSV
    $questoes_file = '../questoes/questoes_mbti.csv';
    $mapa_questoes = [];
    
    if (file_exists($questoes_file)) {
        if (($handle = fopen($questoes_file, 'r')) !== FALSE) {
            // Pular cabeçalho
            fgetcsv($handle);
            
            $questao_num = 0;
            while (($data = fgetcsv($handle)) !== FALSE) {
                if (count($data) >= 4) { // id, pergunta, opcao_a, opcao_b, dimensao
                    $dimensao = trim($data[4]); // E/I, S/N, T/F, ou J/P
                    $mapa_questoes[$questao_num] = [
                        'A' => substr($dimensao, 0, 1), // Primeira letra (E, S, T, J)
                        'B' => substr($dimensao, 2, 1)  // Segunda letra (I, N, F, P)
                    ];
                }
                $questao_num++;
            }
            fclose($handle);
        }
    }
    
    // Processar respostas
    foreach ($respostas as $questao => $resposta) {
        if (isset($mapa_questoes[$questao][$resposta])) {
            $tipo = $mapa_questoes[$questao][$resposta];
            $dimensoes[$tipo]++;
        }
    }
    
    // Calcular tipo MBTI
    $tipo = '';
    $pontuacao = [];
    
    // E/I
    $total_ei = $dimensoes['E'] + $dimensoes['I'];
    $pontuacao['E/I'] = $total_ei > 0 ? 
        round(($dimensoes['E'] / $total_ei) * 100) - 50 : 0;
    $tipo .= $pontuacao['E/I'] >= 0 ? 'E' : 'I';
    
    // S/N
    $total_sn = $dimensoes['S'] + $dimensoes['N'];
    $pontuacao['S/N'] = $total_sn > 0 ? 
        round(($dimensoes['S'] / $total_sn) * 100) - 50 : 0;
    $tipo .= $pontuacao['S/N'] >= 0 ? 'S' : 'N';
    
    // T/F
    $total_tf = $dimensoes['T'] + $dimensoes['F'];
    $pontuacao['T/F'] = $total_tf > 0 ? 
        round(($dimensoes['T'] / $total_tf) * 100) - 50 : 0;
    $tipo .= $pontuacao['T/F'] >= 0 ? 'T' : 'F';
    
    // J/P
    $total_jp = $dimensoes['J'] + $dimensoes['P'];
    $pontuacao['J/P'] = $total_jp > 0 ? 
        round(($dimensoes['J'] / $total_jp) * 100) - 50 : 0;
    $tipo .= $pontuacao['J/P'] >= 0 ? 'J' : 'P';
    
    return [
        'tipo' => $tipo,
        'pontuacao' => $pontuacao,
        'dimensoes' => $dimensoes
    ];
}

?>

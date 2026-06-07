<?php
// Script para atualizar o arquivo de candidatos Big Five adicionando a coluna de status

$candidatos_file = 'resultados/candidatos_bigfive.csv';
$candidatos_temp = 'resultados/candidatos_bigfive_temp.csv';

if (file_exists($candidatos_file)) {
    // Ler o arquivo atual
    $linhas = [];
    if (($handle = fopen($candidatos_file, "r")) !== FALSE) {
        // Ler o cabeçalho
        $header = fgetcsv($handle);
        
        // Verificar se já tem a coluna Status
        $tem_status = false;
        foreach ($header as $coluna) {
            if ($coluna === 'Status') {
                $tem_status = true;
                break;
            }
        }
        
        // Se não tem a coluna Status, adicionar
        if (!$tem_status) {
            $header[] = 'Status';
        }
        $linhas[] = $header;
        
        // Ler os dados
        while (($data = fgetcsv($handle)) !== FALSE) {
            // Se não tem a coluna Status, verificar se existe arquivo de avaliação
            if (!$tem_status) {
                $email = $data[4]; // Email está na quinta coluna
                $arquivo_avaliacao = 'resultados/' . str_replace(['@', '.'], '_', $email) . '_avaliacao_bigfive.csv';
                
                // Se existe arquivo de avaliação, marcar como completed
                if (file_exists($arquivo_avaliacao)) {
                    $data[] = 'completed';
                } else {
                    $data[] = 'pendente';
                }
            }
            $linhas[] = $data;
        }
        fclose($handle);
        
        // Reescrever o arquivo com a nova estrutura
        if (!$tem_status) {
            $fp = fopen($candidatos_temp, 'w');
            foreach ($linhas as $linha) {
                fputcsv($fp, $linha);
            }
            fclose($fp);
            
            // Substituir o arquivo original
            rename($candidatos_temp, $candidatos_file);
            
            echo "Arquivo atualizado com sucesso!";
        } else {
            echo "O arquivo já possui a coluna Status.";
        }
    }
} else {
    echo "Arquivo de candidatos não encontrado.";
}
?>

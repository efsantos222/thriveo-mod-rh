<?php
session_start();

if (!isset($_SESSION['email']) || !isset($_SESSION['nome'])) {
    header('Location: login.php');
    exit;
}

// Se não houver POST data, significa que é uma nova questão
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Processar resposta atual
    if (isset($_POST['frequencia']) && isset($_POST['gravidade']) && isset($_POST['question_id'])) {
        $respostas = isset($_SESSION['respostas_jss']) ? $_SESSION['respostas_jss'] : [];
        
        // Armazenar resposta
        $respostas[] = [
            'id' => $_POST['question_id'],
            'frequencia' => $_POST['frequencia'],
            'gravidade' => $_POST['gravidade']
        ];
        
        $_SESSION['respostas_jss'] = $respostas;
        $_SESSION['current_question_jss'] = count($respostas);
        
        // Redirecionar para próxima questão
        header('Location: test_jss.php');
        exit;
    }
} else {
    // Processar resultados finais
    if (isset($_SESSION['respostas_jss'])) {
        $respostas = $_SESSION['respostas_jss'];
        
        // Calcular totais
        $frequencia_total = 0;
        $gravidade_total = 0;
        
        foreach ($respostas as $resposta) {
            $frequencia_total += $resposta['frequencia'];
            $gravidade_total += $resposta['gravidade'];
        }
        
        // Calcular pontuação composta
        $pontuacao_composta = $frequencia_total * $gravidade_total;
        
        // Determinar níveis de estresse
        $nivel_frequencia = $frequencia_total <= 20 ? 'Baixo' : ($frequencia_total <= 35 ? 'Moderado' : 'Alto');
        $nivel_gravidade = $gravidade_total <= 20 ? 'Baixo' : ($gravidade_total <= 35 ? 'Moderado' : 'Alto');
        $nivel_composto = $pontuacao_composta <= 800 ? 'Baixo' : ($pontuacao_composta <= 1200 ? 'Moderado' : 'Alto');
        
        // Salvar resultados
        $email = str_replace(['@', '.'], '_', $_SESSION['email']);
        $resultado_file = "resultados/{$email}_avaliacao_jss.csv";
        
        $fp = fopen($resultado_file, 'w');
        if ($fp !== false) {
            // Cabeçalho
            fputcsv($fp, ['ID', 'Frequência', 'Gravidade']);
            
            // Respostas individuais
            foreach ($respostas as $resposta) {
                fputcsv($fp, [
                    $resposta['id'],
                    $resposta['frequencia'],
                    $resposta['gravidade']
                ]);
            }
            
            // Totais
            fputcsv($fp, ['TOTAL', $frequencia_total, $gravidade_total]);
            fputcsv($fp, ['NÍVEL', $nivel_frequencia, $nivel_gravidade]);
            fputcsv($fp, ['PONTUAÇÃO COMPOSTA', $pontuacao_composta, $nivel_composto]);
            
            fclose($fp);
        }
        
        // Atualizar status do candidato
        $candidatos_file = 'resultados/candidatos_jss.csv';
        if (file_exists($candidatos_file)) {
            $linhas = file($candidatos_file);
            $fp = fopen($candidatos_file, 'w');
            if ($fp !== false) {
                foreach ($linhas as $linha) {
                    $dados = str_getcsv($linha);
                    if ($dados[4] === $_SESSION['email']) {
                        $dados[8] = 'Concluído';
                    }
                    fputcsv($fp, $dados);
                }
                fclose($fp);
            }
        }
        
        // Limpar sessão
        unset($_SESSION['respostas_jss']);
        unset($_SESSION['current_question_jss']);
        
        // Redirecionar para página de agradecimento
        header('Location: thank_you.php');
        exit;
    } else {
        header('Location: test_jss.php');
        exit;
    }
}

<?php
session_start();

// Verificar se o usuário está autenticado
if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated'] || $_SESSION['user_type'] !== 'bigfive') {
    header('Location: login.php');
    exit;
}

// Carregar as questões do arquivo CSV
$questions = [];
$questoes_file = 'questoes/questoes_bigfive.csv';
if (file_exists($questoes_file)) {
    $fp = fopen($questoes_file, 'r');
    if ($fp !== false) {
        fgetcsv($fp); // Pular cabeçalho
        while (($data = fgetcsv($fp)) !== FALSE) {
            $questions[] = [
                'id' => $data[0],
                'questao' => $data[1],
                'dimensao' => $data[2]
            ];
        }
        fclose($fp);
    }
}

// Se não houver questões, redirecionar para login
if (empty($questions)) {
    header('Location: login.php?error=no_questions');
    exit;
}

// Inicializar questão atual se não existir
if (!isset($_SESSION['questao_atual'])) {
    $_SESSION['questao_atual'] = 0;
}

// Processar resposta anterior
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resposta'])) {
    $resposta = intval($_POST['resposta']);
    if ($resposta >= 1 && $resposta <= 5) {
        // Salvar resposta
        if (!isset($_SESSION['respostas'])) {
            $_SESSION['respostas'] = [];
        }
        $_SESSION['respostas'][] = [
            'questao_id' => $questions[$_SESSION['questao_atual']]['id'],
            'resposta' => $resposta
        ];
        
        // Avançar para próxima questão
        $_SESSION['questao_atual']++;
        
        // Se completou todas as questões
        if ($_SESSION['questao_atual'] >= count($questions)) {
            // Salvar resultados
            $results_file = 'resultados/resultados_bigfive.csv';
            $fp = fopen($results_file, 'a');
            if ($fp !== false) {
                $data = [
                    date('Y-m-d H:i:s'),
                    $_SESSION['email'],
                    $_SESSION['nome']
                ];
                foreach ($_SESSION['respostas'] as $resposta) {
                    $data[] = $resposta['resposta'];
                }
                fputcsv($fp, $data);
                fclose($fp);
            }
            
            // Atualizar status do candidato
            $candidatos_file = 'resultados/candidatos_bigfive.csv';
            if (file_exists($candidatos_file)) {
                $candidatos = [];
                $fp = fopen($candidatos_file, 'r');
                if ($fp !== false) {
                    while (($data = fgetcsv($fp)) !== FALSE) {
                        if ($data[4] === $_SESSION['email']) {
                            $data[8] = 'Concluído';
                        }
                        $candidatos[] = $data;
                    }
                    fclose($fp);
                }
                
                // Reescrever arquivo com status atualizado
                $fp = fopen($candidatos_file, 'w');
                if ($fp !== false) {
                    foreach ($candidatos as $data) {
                        fputcsv($fp, $data);
                    }
                    fclose($fp);
                }
            }
            
            // Redirecionar para página de conclusão
            header('Location: completion.php');
            exit;
        }
    }
}

// Obter questão atual
$current_question = $questions[$_SESSION['questao_atual']];
$total_questions = count($questions);
$progress = ($_SESSION['questao_atual'] / $total_questions) * 100;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Big Five - Questão <?php echo $_SESSION['questao_atual'] + 1; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .question-card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .progress {
            height: 10px;
            border-radius: 5px;
        }
        .btn-answer {
            width: 100%;
            margin: 5px 0;
            border-radius: 25px;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }
        .btn-answer:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .question-number {
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="progress mb-4">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $progress; ?>%" aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                
                <div class="card question-card">
                    <div class="card-body p-4">
                        <p class="question-number text-center mb-2">Questão <?php echo $_SESSION['questao_atual'] + 1; ?> de <?php echo $total_questions; ?></p>
                        <h4 class="card-title text-center mb-4"><?php echo htmlspecialchars($current_question['questao']); ?></h4>
                        
                        <form method="POST" class="mt-4">
                            <div class="d-grid gap-2">
                                <button type="submit" name="resposta" value="5" class="btn btn-outline-warning btn-answer">Concordo Totalmente</button>
                                <button type="submit" name="resposta" value="4" class="btn btn-outline-warning btn-answer">Concordo Parcialmente</button>
                                <button type="submit" name="resposta" value="3" class="btn btn-outline-warning btn-answer">Neutro</button>
                                <button type="submit" name="resposta" value="2" class="btn btn-outline-warning btn-answer">Discordo Parcialmente</button>
                                <button type="submit" name="resposta" value="1" class="btn btn-outline-warning btn-answer">Discordo Totalmente</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
session_start();
require_once 'questions.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_email'])) {
    header('Location: ../login.php');
    exit;
}

// Configurar caminhos
$user_email = $_SESSION['user_email'];
$progress_dir = '../resultados/progress';
$progress_file = $progress_dir . '/' . str_replace(['@', '.'], '_', $user_email) . '_mbti_progress.json';

// Criar diretório de progresso se não existir
if (!file_exists($progress_dir)) {
    mkdir($progress_dir, 0777, true);
}

// Inicializar ou carregar progresso
$current_question = 0;
$answers = [];

if (file_exists($progress_file)) {
    $progress_data = json_decode(file_get_contents($progress_file), true);
    if ($progress_data) {
        $current_question = $progress_data['current_question'];
        $answers = $progress_data['answers'];
    }
}

// Processar resposta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer'])) {
    $answers[$current_question] = $_POST['answer'];
    $current_question++;
    
    // Salvar progresso
    $progress_data = [
        'current_question' => $current_question,
        'answers' => $answers
    ];
    
    // Tentar salvar o progresso
    $save_result = file_put_contents($progress_file, json_encode($progress_data));
    if ($save_result === false) {
        error_log("Erro ao salvar progresso MBTI para {$user_email} em {$progress_file}");
    } else {
        error_log("Progresso MBTI salvo com sucesso para {$user_email}. Questão atual: {$current_question}");
    }
    
    // Se completou o teste
    if ($current_question >= count($mbti_questions)) {
        // Calcular resultado
        $result = [
            'E' => 0, 'I' => 0,
            'S' => 0, 'N' => 0,
            'T' => 0, 'F' => 0,
            'J' => 0, 'P' => 0
        ];
        
        foreach ($answers as $q_index => $answer) {
            $question = $mbti_questions[$q_index];
            $type = $question['options'][$answer]['type'];
            $result[$type]++;
        }
        
        // Determinar o tipo MBTI
        $mbti_type = '';
        $mbti_type .= $result['E'] > $result['I'] ? 'E' : 'I';
        $mbti_type .= $result['S'] > $result['N'] ? 'S' : 'N';
        $mbti_type .= $result['T'] > $result['F'] ? 'T' : 'F';
        $mbti_type .= $result['J'] > $result['P'] ? 'J' : 'P';
        
        // Salvar resultado
        $results_file = '../resultados/' . str_replace(['@', '.'], '_', $user_email) . '_avaliacao_mbti.csv';
        $fp = fopen($results_file, 'w');
        if ($fp !== false) {
            fputcsv($fp, ['data', 'tipo']);
            fputcsv($fp, [date('Y-m-d H:i:s'), $mbti_type]);
            fclose($fp);
            error_log("Resultado MBTI salvo com sucesso para {$user_email}: {$mbti_type}");
        } else {
            error_log("Erro ao salvar resultado MBTI para {$user_email} em {$results_file}");
        }
        
        // Limpar arquivo de progresso
        if (file_exists($progress_file)) {
            unlink($progress_file);
        }
        
        // Redirecionar para resultados
        header('Location: candidate_results.php');
        exit;
    }
    
    // Recarregar a página para mostrar a próxima questão
    header('Location: test.php');
    exit;
}

// Se já completou o teste, redirecionar para resultados
$results_file = '../resultados/' . str_replace(['@', '.'], '_', $user_email) . '_avaliacao_mbti.csv';
if (file_exists($results_file)) {
    header('Location: candidate_results.php');
    exit;
}

$total_questions = count($mbti_questions);
$progress = ($current_question / $total_questions) * 100;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste MBTI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
            padding: 20px;
        }
        .question-card {
            max-width: 800px;
            margin: 0 auto;
        }
        .card {
            border: none;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .option-button {
            display: block;
            width: 100%;
            padding: 15px;
            margin-bottom: 10px;
            text-align: left;
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            transition: all 0.3s;
        }
        .option-button:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
        }
        .progress {
            height: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <?php include '../includes/candidate_header.php'; ?>
        
        <div class="question-card">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Questão <?php echo $current_question + 1; ?> de <?php echo $total_questions; ?></h5>
                </div>
                <div class="card-body">
                    <div class="progress mb-4">
                        <div class="progress-bar" role="progressbar" style="width: <?php echo $progress; ?>%" 
                             aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    
                    <h5 class="card-title mb-4"><?php echo $mbti_questions[$current_question]['question']; ?></h5>
                    
                    <form method="POST">
                        <?php foreach ($mbti_questions[$current_question]['options'] as $key => $option): ?>
                            <button type="submit" name="answer" value="<?php echo $key; ?>" class="option-button">
                                <?php echo $option['text']; ?>
                            </button>
                        <?php endforeach; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

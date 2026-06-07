<?php
session_start();

// Verificar autenticação
if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
    header('Location: ../login.php');
    exit;
}

require_once '../functions.php';

// Inicializar variáveis de sessão se necessário
if (!isset($_SESSION['mbti_questao_atual'])) {
    $_SESSION['mbti_questao_atual'] = 0;
    $_SESSION['mbti_respostas'] = [];
}

// Carregar questões MBTI
$questoes_file = '../questoes/questoes_mbti.csv';
$questions = [];

if (file_exists($questoes_file)) {
    if (($handle = fopen($questoes_file, 'r')) !== FALSE) {
        // Pular o cabeçalho
        fgetcsv($handle);
        
        while (($data = fgetcsv($handle)) !== FALSE) {
            if (count($data) >= 3) { // ID, Pergunta, Opção A, Opção B
                $questions[] = [
                    'id' => intval($data[0]),
                    'question' => $data[1],
                    'options' => [
                        'A' => $data[2],
                        'B' => $data[3]
                    ]
                ];
            }
        }
        fclose($handle);
    }
}

// Verificar se há questões disponíveis
if (empty($questions)) {
    die("Erro: Não foi possível carregar as questões do teste MBTI. Por favor, contate o administrador.");
}

// Verificar se já completou todas as questões
if ($_SESSION['mbti_questao_atual'] >= count($questions)) {
    header('Location: mbti_thank_you.php');
    exit;
}

// Obter questão atual
$questao_atual = $questions[$_SESSION['mbti_questao_atual']];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Questionário MBTI - Questão <?php echo $questao_atual['id']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <?php include '../includes/candidate_header.php'; ?>
        
        <div class="question-container">
            <div class="progress mb-4">
                <div class="progress-bar" role="progressbar" 
                     style="width: <?php echo ($_SESSION['mbti_questao_atual'] / count($questions)) * 100; ?>%"
                     aria-valuenow="<?php echo ($_SESSION['mbti_questao_atual'] / count($questions)) * 100; ?>"
                     aria-valuemin="0" aria-valuemax="100">
                    Questão <?php echo $_SESSION['mbti_questao_atual'] + 1; ?> de <?php echo count($questions); ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Questão <?php echo $questao_atual['id']; ?></h4>
                    <p class="lead mb-4"><?php echo $questao_atual['question']; ?></p>
                    
                    <form action="mbti_process.php" method="post" class="options-form">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" 
                                   name="resposta" value="A" 
                                   id="optionA" required>
                            <label class="form-check-label" for="optionA">
                                <?php echo $questao_atual['options']['A']; ?>
                            </label>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" 
                                   name="resposta" value="B" 
                                   id="optionB" required>
                            <label class="form-check-label" for="optionB">
                                <?php echo $questao_atual['options']['B']; ?>
                            </label>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Próxima Questão</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

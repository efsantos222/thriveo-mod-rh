<?php
session_start();

// Verificar autenticação
if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
    header('Location: login.php');
    exit;
}

require_once 'functions.php';

// Inicializar variáveis de sessão se necessário
if (!isset($_SESSION['questao_atual'])) {
    $_SESSION['questao_atual'] = 0;
    $_SESSION['respostas'] = [];
}

// Carregar todas as questões
$questions = getQuestions();

// Verificar se há questões disponíveis
if (empty($questions)) {
    die("Erro: Não foi possível carregar as questões do teste. Por favor, contate o administrador.");
}

// Verificar se já completou todas as questões
if ($_SESSION['questao_atual'] >= count($questions)) {
    header('Location: thank_you.php');
    exit;
}

// Obter questão atual
$questao_atual = $questions[$_SESSION['questao_atual']];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Questionário DISC - Questão <?php echo $questao_atual['id']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding-top: 50px; }
        .container { max-width: 800px; }
        .question-container { background: #f8f9fa; padding: 20px; border-radius: 10px; }
        .options-container { margin-top: 20px; }
        .option-item { margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <?php include 'includes/candidate_header.php'; ?>
        
        <div class="question-container">
            <div class="progress mb-4">
                <div class="progress-bar" role="progressbar" 
                     style="width: <?php echo ($_SESSION['questao_atual'] / count($questions)) * 100; ?>%"
                     aria-valuenow="<?php echo ($_SESSION['questao_atual'] / count($questions)) * 100; ?>"
                     aria-valuemin="0" aria-valuemax="100">
                    Questão <?php echo $_SESSION['questao_atual'] + 1; ?> de <?php echo count($questions); ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Questão <?php echo $questao_atual['id']; ?></h4>
                    <p class="lead mb-4"><?php echo $questao_atual['question']; ?></p>
                    
                    <form action="process.php" method="post" class="options-form">
                        <?php foreach ($questao_atual['options'] as $key => $option): ?>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" 
                                       name="resposta" value="<?php echo $option['type']; ?>" 
                                       id="option<?php echo $key; ?>" required>
                                <label class="form-check-label" for="option<?php echo $key; ?>">
                                    <?php echo $option['text']; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                        
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

<?php
require_once 'includes/auth.php';

$auth = new Auth();
$auth->requireRole('candidate');

$db = getDbConnection();

// Verificar o tipo de teste
$testType = $_GET['type'] ?? '';
if (!in_array($testType, ['disc', 'rac'])) {
    header('Location: candidate_panel.php');
    exit;
}

// Buscar informações do candidato e verificar se o teste está atribuído
$stmt = $db->prepare("
    SELECT c.*, ta.id as assignment_id
    FROM candidates c
    INNER JOIN test_assignments ta ON c.id = ta.candidate_id
    WHERE c.email = ? AND ta.test_type = ? AND NOT EXISTS (
        SELECT 1 FROM test_results tr 
        WHERE tr.candidate_id = c.id AND tr.test_type = ta.test_type
    )
");
$stmt->execute([$auth->getCurrentUserEmail(), $testType]);
$candidate = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$candidate) {
    header('Location: candidate_panel.php');
    exit;
}

// Buscar questões do teste
$stmt = $db->prepare("SELECT * FROM test_questions WHERE test_type = ? ORDER BY id");
$stmt->execute([$testType]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Processar submissão do teste
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_test'])) {
    $answers = $_POST['answers'] ?? [];
    $results = [];
    
    try {
        $db->beginTransaction();
        
        // Calcular resultados baseado no tipo de teste
        switch ($testType) {
            case 'disc':
                $results = calculateDiscResults($answers);
                break;
            case 'rac':
                $results = calculateRacResults($answers);
                break;
        }
        
        // Salvar resultados
        $stmt = $db->prepare("
            INSERT INTO test_results (candidate_id, test_type, results) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            $candidate['id'],
            $testType,
            json_encode($results)
        ]);
        
        $db->commit();
        header('Location: candidate_panel.php?completed=' . $testType);
        exit;
    } catch (Exception $e) {
        $db->rollBack();
        $error = "Erro ao salvar resultados: " . $e->getMessage();
    }
}

// Funções para calcular resultados
function calculateDiscResults($answers) {
    $scores = ['D' => 0, 'I' => 0, 'S' => 0, 'C' => 0];
    foreach ($answers as $answer) {
        $scores[$answer]++;
    }
    // Converter para porcentagem
    $total = array_sum($scores);
    foreach ($scores as &$score) {
        $score = round(($score / $total) * 100);
    }
    return $scores;
}

function calculateRacResults($answers) {
    $scores = [
        'logico' => 0,
        'verbal' => 0,
        'numerico' => 0,
        'espacial' => 0
    ];
    
    foreach ($answers as $type => $isCorrect) {
        if ($isCorrect) {
            $scores[$type]++;
        }
    }
    
    // Converter para porcentagem (assumindo 5 questões por tipo)
    foreach ($scores as &$score) {
        $score = round(($score / 5) * 100);
    }
    
    return $scores;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste <?php echo strtoupper($testType); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .question-card {
            margin-bottom: 20px;
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .question-card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .question-number {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        .form-check {
            margin: 10px 0;
            padding: 10px;
            border-radius: 8px;
            transition: background-color 0.2s;
        }
        .form-check:hover {
            background-color: #f8f9fa;
        }
        .form-check-input:checked + .form-check-label {
            color: #0d6efd;
            font-weight: 500;
        }
        .progress {
            height: 8px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">
                <?php if ($testType === 'disc'): ?>
                    <i class="bi bi-pie-chart-fill"></i>
                <?php else: ?>
                    <i class="bi bi-lightbulb-fill"></i>
                <?php endif; ?>
                Teste <?php echo strtoupper($testType); ?>
            </a>
            <div class="navbar-text text-white">
                <i class="bi bi-person-circle"></i>
                <?php echo htmlspecialchars($auth->getCurrentUserName()); ?>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <h4 class="card-title">
                    <?php if ($testType === 'disc'): ?>
                        Avaliação de Perfil Comportamental DISC
                    <?php else: ?>
                        Avaliação de Raciocínio
                    <?php endif; ?>
                </h4>
                <p class="card-text">
                    <?php if ($testType === 'disc'): ?>
                        Este teste avalia seu perfil comportamental através de quatro dimensões principais:
                        <strong>Dominância</strong> (como você lida com problemas),
                        <strong>Influência</strong> (como você lida com pessoas),
                        <strong>Estabilidade</strong> (como você lida com ritmo) e
                        <strong>Conformidade</strong> (como você lida com procedimentos).
                    <?php else: ?>
                        Este teste avalia diferentes aspectos do seu raciocínio:
                        <strong>Lógico</strong> (capacidade de resolver problemas),
                        <strong>Verbal</strong> (compreensão e uso da linguagem),
                        <strong>Numérico</strong> (habilidade com números) e
                        <strong>Espacial</strong> (visualização e manipulação de formas).
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <form method="POST" id="testForm">
            <div class="progress mb-4">
                <div class="progress-bar" role="progressbar" style="width: 0%" id="progressBar"></div>
            </div>

            <?php foreach ($questions as $index => $question): ?>
                <div class="card question-card">
                    <div class="card-body">
                        <div class="question-number">Questão <?php echo $index + 1; ?> de <?php echo count($questions); ?></div>
                        <h5 class="card-title"><?php echo htmlspecialchars($question['question']); ?></h5>
                        
                        <?php if ($testType === 'disc'): ?>
                            <div class="row">
                                <?php 
                                $options = json_decode($question['options'], true);
                                foreach ($options as $key => $option): 
                                ?>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" 
                                                   name="answers[<?php echo $question['id']; ?>]" 
                                                   value="<?php echo $key; ?>" 
                                                   id="q<?php echo $question['id']; ?>_<?php echo $key; ?>"
                                                   required>
                                            <label class="form-check-label" for="q<?php echo $question['id']; ?>_<?php echo $key; ?>">
                                                <?php echo htmlspecialchars($option); ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <?php 
                            $options = json_decode($question['options'], true);
                            foreach ($options as $key => $option): 
                            ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" 
                                           name="answers[<?php echo $question['type']; ?>][<?php echo $question['id']; ?>]" 
                                           value="<?php echo $key; ?>" 
                                           id="q<?php echo $question['id']; ?>_<?php echo $key; ?>"
                                           required>
                                    <label class="form-check-label" for="q<?php echo $question['id']; ?>_<?php echo $key; ?>">
                                        <?php echo htmlspecialchars($option); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="d-grid gap-2 col-md-6 mx-auto mb-4">
                <button type="submit" name="submit_test" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-circle"></i> Finalizar Teste
                </button>
                <a href="candidate_panel.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancelar
                </a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Atualizar barra de progresso
    document.querySelectorAll('input[type="radio"]').forEach(input => {
        input.addEventListener('change', updateProgress);
    });

    function updateProgress() {
        const total = document.querySelectorAll('.question-card').length;
        const answered = document.querySelectorAll('input[type="radio"]:checked').length;
        const progress = (answered / total) * 100;
        document.getElementById('progressBar').style.width = progress + '%';
    }

    // Confirmar antes de sair da página
    window.addEventListener('beforeunload', function (e) {
        const answered = document.querySelectorAll('input[type="radio"]:checked').length;
        if (answered > 0 && !document.querySelector('button[name="submit_test"]').disabled) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
    </script>
</body>
</html>

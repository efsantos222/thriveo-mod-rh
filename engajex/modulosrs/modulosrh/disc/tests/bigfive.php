<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

$auth = new Auth();
$auth->requireRole('candidate');

// Buscar informações do candidato
$db = getDbConnection();
$stmt = $db->prepare("
    SELECT c.*, u.name as selector_name
    FROM candidates c
    LEFT JOIN users u ON c.selector_id = u.id
    WHERE c.id = ?
");
$stmt->execute([$auth->getCurrentUserId()]);
$candidate = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$candidate) {
    header('Location: ../login.php');
    exit;
}

// Verificar se o teste já foi concluído
$stmt = $db->prepare("
    SELECT * FROM test_results 
    WHERE candidate_id = ? AND test_type = 'bigfive'
");
$stmt->execute([$candidate['id']]);
$testResult = $stmt->fetch(PDO::FETCH_ASSOC);

if ($testResult) {
    header('Location: ../candidate_panel.php?error=test_completed');
    exit;
}

// Verificar se o teste está atribuído
$stmt = $db->prepare("
    SELECT * FROM test_assignments 
    WHERE candidate_id = ? AND test_type = 'bigfive'
");
$stmt->execute([$candidate['id']]);
$testAssignment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$testAssignment) {
    header('Location: ../candidate_panel.php?error=test_not_assigned');
    exit;
}

// Processar submissão do teste
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answers = $_POST['answers'] ?? [];
    if (!empty($answers)) {
        try {
            // Calcular pontuações por dimensão
            $dimensions = [
                'Abertura' => 0,
                'Conscienciosidade' => 0,
                'Extroversao' => 0,
                'Amabilidade' => 0,
                'Neuroticismo' => 0
            ];

            // Buscar informações das questões para cálculo
            $stmt = $db->prepare("SELECT id, dimension, is_inverse FROM bigfive_questions");
            $stmt->execute();
            $questionInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $questionMap = [];
            foreach ($questionInfo as $info) {
                $questionMap[$info['id']] = $info;
            }

            // Calcular pontuação para cada dimensão
            foreach ($answers as $questionId => $value) {
                if (isset($questionMap[$questionId])) {
                    $info = $questionMap[$questionId];
                    $score = (int)$value;
                    
                    // Inverter pontuação se necessário
                    if ($info['is_inverse']) {
                        $score = 6 - $score; // Escala de 1-5, então 6 - score inverte
                    }
                    
                    $dimensions[$info['dimension']] += $score;
                }
            }

            // Calcular médias
            $questionCounts = array_count_values(array_column($questionInfo, 'dimension'));
            foreach ($dimensions as $dimension => &$score) {
                if (isset($questionCounts[$dimension]) && $questionCounts[$dimension] > 0) {
                    $score = round($score / $questionCounts[$dimension], 2);
                }
            }

            $result = [
                'dimensions' => $dimensions,
                'answers' => $answers
            ];

            // Salvar resultado
            $stmt = $db->prepare("
                INSERT INTO test_results (
                    candidate_id, 
                    test_type, 
                    results, 
                    completed_at
                ) VALUES (?, 'bigfive', ?, NOW())
            ");
            $stmt->execute([
                $candidate['id'],
                json_encode($result)
            ]);

            header('Location: ../candidate_panel.php?success=test_completed');
            exit;
        } catch (PDOException $e) {
            $error = 'Erro ao salvar o teste. Por favor, tente novamente.';
        }
    }
}

// Buscar questões do teste Big Five do banco de dados
$stmt = $db->prepare("SELECT * FROM bigfive_questions ORDER BY id");
$stmt->execute();
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Big Five - Sistema de Testes</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        .question-card {
            margin-bottom: 2rem;
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .question-header {
            background-color: #0d6efd;
            color: white;
            border-radius: 10px 10px 0 0;
            padding: 1rem;
        }
        .likert-scale {
            display: flex;
            justify-content: space-between;
            margin: 1rem 0;
            padding: 0 1rem;
        }
        .likert-option {
            text-align: center;
            flex: 1;
        }
        .likert-label {
            display: block;
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: #6c757d;
        }
        .progress {
            height: 10px;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../candidate_panel.php">Sistema de Testes</a>
            <span class="navbar-text">
                Olá, <?php echo htmlspecialchars($candidate['name']); ?>
            </span>
        </div>
    </nav>

    <div class="container my-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h2 class="text-center mb-4">Teste de Personalidade Big Five</h2>
                
                <div class="alert alert-info mb-4">
                    <h5><i class="bi bi-info-circle me-2"></i>Instruções:</h5>
                    <p class="mb-0">
                        Para cada afirmação, indique o quanto você concorda ou discorda usando a escala de 1 a 5:
                        <br>1 = Discordo totalmente
                        <br>2 = Discordo parcialmente
                        <br>3 = Nem concordo nem discordo
                        <br>4 = Concordo parcialmente
                        <br>5 = Concordo totalmente
                    </p>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="post" id="bigFiveForm">
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>

                    <?php foreach ($questions as $index => $question): ?>
                        <div class="card question-card" data-question="<?php echo $index + 1; ?>">
                            <div class="question-header">
                                <h5 class="mb-0">
                                    Questão <?php echo $index + 1; ?> de <?php echo count($questions); ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="card-text"><?php echo htmlspecialchars($question['question_text']); ?></p>
                                
                                <div class="likert-scale">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <div class="likert-option">
                                            <input type="radio" 
                                                   name="answers[<?php echo $question['id']; ?>]" 
                                                   value="<?php echo $i; ?>" 
                                                   id="q<?php echo $question['id']; ?>_<?php echo $i; ?>" 
                                                   class="form-check-input" 
                                                   required>
                                            <label class="likert-label" for="q<?php echo $question['id']; ?>_<?php echo $i; ?>">
                                                <?php
                                                switch ($i) {
                                                    case 1: echo "Discordo totalmente"; break;
                                                    case 2: echo "Discordo parcialmente"; break;
                                                    case 3: echo "Neutro"; break;
                                                    case 4: echo "Concordo parcialmente"; break;
                                                    case 5: echo "Concordo totalmente"; break;
                                                }
                                                ?>
                                            </label>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle me-2"></i>
                            Finalizar Teste
                        </button>
                        <a href="../candidate_panel.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>
                            Voltar ao Painel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('bigFiveForm');
        const progressBar = document.querySelector('.progress-bar');
        const questions = document.querySelectorAll('.question-card');
        const totalQuestions = questions.length;

        // Atualizar barra de progresso
        function updateProgress() {
            const answered = form.querySelectorAll('input[type="radio"]:checked').length;
            const progress = (answered / totalQuestions) * 100;
            progressBar.style.width = progress + '%';
            progressBar.setAttribute('aria-valuenow', progress);
        }

        // Adicionar listener para todas as opções
        form.querySelectorAll('input[type="radio"]').forEach(input => {
            input.addEventListener('change', updateProgress);
        });

        // Validar formulário antes de enviar
        form.addEventListener('submit', function(e) {
            const answered = form.querySelectorAll('input[type="radio"]:checked').length;
            if (answered < totalQuestions) {
                e.preventDefault();
                alert('Por favor, responda todas as questões antes de finalizar o teste.');
            }
        });
    });
    </script>
</body>
</html>

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
    WHERE candidate_id = ? AND test_type = 'disc'
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
    WHERE candidate_id = ? AND test_type = 'disc'
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
            // Salvar resultado
            $stmt = $db->prepare("
                INSERT INTO test_results (
                    candidate_id, 
                    test_type, 
                    results, 
                    completed_at
                ) VALUES (?, 'disc', ?, NOW())
            ");
            $stmt->execute([
                $candidate['id'],
                json_encode($answers)
            ]);

            header('Location: ../candidate_panel.php?success=test_completed');
            exit;
        } catch (PDOException $e) {
            $error = 'Erro ao salvar o teste. Por favor, tente novamente.';
        }
    }
}

// Buscar questões do teste DISC do banco de dados
$stmt = $db->prepare("SELECT * FROM disc_questions ORDER BY id");
$stmt->execute();
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste DISC - Sistema de Testes</title>
    
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
        .option-label {
            display: block;
            padding: 1rem;
            margin: 0.5rem 0;
            border: 2px solid #dee2e6;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .option-label:hover {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }
        .option-input:checked + .option-label {
            border-color: #0d6efd;
            background-color: #e7f1ff;
        }
        .option-input {
            display: none;
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
                <h2 class="text-center mb-4">Teste DISC</h2>
                
                <div class="alert alert-info mb-4">
                    <h5><i class="bi bi-info-circle me-2"></i>Instruções:</h5>
                    <p class="mb-0">
                        Para cada pergunta, selecione a opção que melhor descreve seu comportamento natural.
                        Não existe resposta certa ou errada. Seja honesto em suas escolhas.
                    </p>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="post" id="discForm">
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
                                <p class="card-text mb-4"><?php echo htmlspecialchars($question['question_text']); ?></p>
                                
                                <?php
                                $options = [
                                    'D' => $question['option_d'],
                                    'I' => $question['option_i'],
                                    'S' => $question['option_s'],
                                    'C' => $question['option_c']
                                ];
                                foreach ($options as $type => $text): ?>
                                    <div class="option">
                                        <input type="radio" 
                                               name="answers[<?php echo $question['id']; ?>]" 
                                               value="<?php echo $type; ?>" 
                                               id="q<?php echo $question['id']; ?>_<?php echo $type; ?>" 
                                               class="option-input"
                                               required>
                                        <label for="q<?php echo $question['id']; ?>_<?php echo $type; ?>" 
                                               class="option-label">
                                            <?php echo htmlspecialchars($text); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
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
        const form = document.getElementById('discForm');
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

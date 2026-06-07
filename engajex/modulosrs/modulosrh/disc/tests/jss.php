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
    WHERE candidate_id = ? AND test_type = 'jss'
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
    WHERE candidate_id = ? AND test_type = 'jss'
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
            // Calcular pontuações por categoria
            $stmt = $db->prepare("SELECT DISTINCT category FROM jss_questions");
            $stmt->execute();
            $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $scores = [];
            foreach ($categories as $category) {
                $scores[$category] = [
                    'total' => 0,
                    'count' => 0,
                    'average' => 0
                ];
            }

            // Buscar categorias das questões
            $stmt = $db->prepare("SELECT id, category FROM jss_questions");
            $stmt->execute();
            $questionCategories = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $questionCategories[$row['id']] = $row['category'];
            }

            // Calcular pontuações
            foreach ($answers as $questionId => $answer) {
                if (isset($questionCategories[$questionId])) {
                    $category = $questionCategories[$questionId];
                    $scores[$category]['total'] += (
                        ((int)$answer['frequency'] + (int)$answer['severity']) / 2
                    );
                    $scores[$category]['count']++;
                }
            }

            // Calcular médias
            foreach ($scores as $category => &$data) {
                if ($data['count'] > 0) {
                    $data['average'] = round($data['total'] / $data['count'], 2);
                }
            }

            $result = [
                'scores' => $scores,
                'answers' => $answers
            ];

            // Buscar o batch mais recente do candidato
            $stmt = $db->prepare("
                SELECT id 
                FROM test_batches 
                WHERE candidate_id = ? 
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $stmt->execute([$candidate['id']]);
            $batchId = $stmt->fetchColumn();

            error_log("Salvando resultados JSS para candidato {$candidate['id']}, batch $batchId");

            // Salvar resultado
            $stmt = $db->prepare("
                INSERT INTO test_results (
                    candidate_id, 
                    test_type, 
                    results, 
                    completed_at,
                    batch_id
                ) VALUES (?, 'jss', ?, NOW(), ?)
            ");
            $stmt->execute([$candidate['id'], json_encode($result), $batchId]);

            error_log("Resultado JSS salvo com sucesso!");

            header('Location: ../candidate_panel.php?success=test_completed');
            exit;
        } catch (PDOException $e) {
            $error = 'Erro ao salvar o teste. Por favor, tente novamente.';
        }
    }
}

// Buscar questões do teste JSS do banco de dados
$stmt = $db->prepare("SELECT * FROM jss_questions ORDER BY category, id");
$stmt->execute();
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar questões por categoria
$questionsByCategory = [];
foreach ($questions as $question) {
    $questionsByCategory[$question['category']][] = $question;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesquisa de Satisfação no Trabalho (JSS) - Sistema de Testes</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        .category-section {
            margin-bottom: 3rem;
        }
        .category-header {
            background-color: #0d6efd;
            color: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }
        .question-card {
            margin-bottom: 1.5rem;
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
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
                <h2 class="text-center mb-4">Pesquisa de Satisfação no Trabalho (JSS)</h2>
                
                <div class="alert alert-info mb-4">
                    <h5><i class="bi bi-info-circle me-2"></i>Instruções:</h5>
                    <p class="mb-0">
                        Para cada situação, avalie:
                        <br><strong>Frequência:</strong> Com que frequência você experimenta esta situação? (1 = Nunca, 2 = Raramente, 3 = Às vezes, 4 = Frequentemente, 5 = Sempre)
                        <br><strong>Gravidade:</strong> Qual o nível de estresse que esta situação causa? (1 = Nada, 2 = Pouco, 3 = Moderado, 4 = Muito, 5 = Extremo)
                    </p>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="post" id="jssForm">
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>

                    <?php foreach ($questionsByCategory as $category => $categoryQuestions): ?>
                        <div class="category-section">
                            <div class="category-header">
                                <h4 class="mb-0"><?php echo htmlspecialchars($category); ?></h4>
                            </div>

                            <?php foreach ($categoryQuestions as $question): ?>
                                <div class="card question-card">
                                    <div class="card-body">
                                        <p class="card-text"><?php echo htmlspecialchars($question['question_text']); ?></p>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="mb-3">Frequência:</h6>
                                                <div class="likert-scale">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <div class="likert-option">
                                                            <input type="radio" 
                                                                   name="answers[<?php echo $question['id']; ?>][frequency]" 
                                                                   value="<?php echo $i; ?>" 
                                                                   id="q<?php echo $question['id']; ?>_freq_<?php echo $i; ?>" 
                                                                   class="form-check-input" 
                                                                   required>
                                                            <label class="likert-label" for="q<?php echo $question['id']; ?>_freq_<?php echo $i; ?>">
                                                                <?php
                                                                switch ($i) {
                                                                    case 1: echo "Nunca"; break;
                                                                    case 2: echo "Raramente"; break;
                                                                    case 3: echo "Às vezes"; break;
                                                                    case 4: echo "Frequentemente"; break;
                                                                    case 5: echo "Sempre"; break;
                                                                }
                                                                ?>
                                                            </label>
                                                        </div>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="mb-3">Gravidade:</h6>
                                                <div class="likert-scale">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <div class="likert-option">
                                                            <input type="radio" 
                                                                   name="answers[<?php echo $question['id']; ?>][severity]" 
                                                                   value="<?php echo $i; ?>" 
                                                                   id="q<?php echo $question['id']; ?>_sev_<?php echo $i; ?>" 
                                                                   class="form-check-input" 
                                                                   required>
                                                            <label class="likert-label" for="q<?php echo $question['id']; ?>_sev_<?php echo $i; ?>">
                                                                <?php
                                                                switch ($i) {
                                                                    case 1: echo "Nada"; break;
                                                                    case 2: echo "Pouco"; break;
                                                                    case 3: echo "Moderado"; break;
                                                                    case 4: echo "Muito"; break;
                                                                    case 5: echo "Extremo"; break;
                                                                }
                                                                ?>
                                                            </label>
                                                        </div>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
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
        const form = document.getElementById('jssForm');
        const progressBar = document.querySelector('.progress-bar');
        const questions = document.querySelectorAll('.question-card');
        const totalQuestions = questions.length;
        const totalInputs = totalQuestions * 2; // Dois inputs por questão (frequência e gravidade)

        // Atualizar barra de progresso
        function updateProgress() {
            const answered = form.querySelectorAll('input[type="radio"]:checked').length;
            const progress = (answered / totalInputs) * 100;
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
            if (answered < totalInputs) {
                e.preventDefault();
                alert('Por favor, responda todas as questões (frequência e gravidade) antes de finalizar o teste.');
            }
        });
    });
    </script>
</body>
</html>

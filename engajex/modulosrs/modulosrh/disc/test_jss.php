<?php
session_start();

if (!isset($_SESSION['email']) || !isset($_SESSION['nome'])) {
    header('Location: login.php');
    exit;
}

$questions_file = 'questoes/questoes_jss.csv';
$questions = [];

if (file_exists($questions_file)) {
    $fp = fopen($questions_file, 'r');
    if ($fp !== false) {
        // Pular o cabeçalho
        fgetcsv($fp);
        while (($data = fgetcsv($fp)) !== FALSE) {
            $questions[] = [
                'id' => $data[0],
                'texto' => $data[1],
                'categoria' => $data[2]
            ];
        }
        fclose($fp);
    }
}

$total_questions = count($questions);
$current_question = isset($_SESSION['current_question_jss']) ? $_SESSION['current_question_jss'] : 0;
$progress = ($current_question / $total_questions) * 100;

// Se todas as questões foram respondidas, redirecionar para processamento
if ($current_question >= $total_questions) {
    header('Location: process_jss.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste JSS - Job Stress Survey</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .question-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .progress {
            height: 10px;
            margin-bottom: 20px;
        }
        .rating-container {
            margin: 20px 0;
        }
        .rating-label {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .rating-description {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 15px;
        }
        .btn-rating {
            width: 50px;
            height: 50px;
            margin: 0 5px;
            border-radius: 25px;
            font-weight: bold;
        }
        .btn-rating:hover {
            transform: scale(1.1);
            transition: transform 0.2s;
        }
        .btn-rating.selected {
            background-color: #0d6efd;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: <?php echo $progress; ?>%" 
                 aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100"></div>
        </div>

        <div class="question-container">
            <h4 class="text-center mb-4">Questão <?php echo ($current_question + 1); ?> de <?php echo $total_questions; ?></h4>
            
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4"><?php echo htmlspecialchars($questions[$current_question]['texto']); ?></h5>

                    <form action="process_jss.php" method="POST" id="questionForm">
                        <input type="hidden" name="question_id" value="<?php echo $questions[$current_question]['id']; ?>">
                        
                        <!-- Frequência -->
                        <div class="rating-container">
                            <div class="rating-label">Frequência (F)</div>
                            <div class="rating-description">Com que frequência você enfrenta esta situação no trabalho?</div>
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <span>Nunca</span>
                                <div class="btn-group" role="group" aria-label="Frequência">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <input type="radio" class="btn-check" name="frequencia" id="f<?php echo $i; ?>" 
                                               value="<?php echo $i; ?>" required>
                                        <label class="btn btn-outline-primary btn-rating" for="f<?php echo $i; ?>">
                                            <?php echo $i; ?>
                                        </label>
                                    <?php endfor; ?>
                                </div>
                                <span>Sempre</span>
                            </div>
                        </div>

                        <!-- Gravidade -->
                        <div class="rating-container">
                            <div class="rating-label">Gravidade (G)</div>
                            <div class="rating-description">Quanto estresse emocional ou físico esta situação causa em você?</div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Pouco</span>
                                <div class="btn-group" role="group" aria-label="Gravidade">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <input type="radio" class="btn-check" name="gravidade" id="g<?php echo $i; ?>" 
                                               value="<?php echo $i; ?>" required>
                                        <label class="btn btn-outline-primary btn-rating" for="g<?php echo $i; ?>">
                                            <?php echo $i; ?>
                                        </label>
                                    <?php endfor; ?>
                                </div>
                                <span>Muito</span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <?php if ($current_question > 0): ?>
                                <button type="button" class="btn btn-secondary" onclick="history.back()">
                                    <i class="bi bi-arrow-left"></i> Anterior
                                </button>
                            <?php else: ?>
                                <div></div>
                            <?php endif; ?>
                            
                            <button type="submit" class="btn btn-primary">
                                <?php echo ($current_question == $total_questions - 1) ? 'Finalizar' : 'Próxima'; ?>
                                <?php if ($current_question < $total_questions - 1): ?>
                                    <i class="bi bi-arrow-right"></i>
                                <?php endif; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Highlight selected ratings
        document.querySelectorAll('.btn-check').forEach(input => {
            input.addEventListener('change', function() {
                // Remove selected class from all buttons in the same group
                const group = this.name;
                document.querySelectorAll(`[name="${group}"]`).forEach(radio => {
                    radio.nextElementSibling.classList.remove('selected');
                });
                // Add selected class to the chosen button
                this.nextElementSibling.classList.add('selected');
            });
        });

        // Form validation
        document.getElementById('questionForm').addEventListener('submit', function(e) {
            const frequencia = document.querySelector('input[name="frequencia"]:checked');
            const gravidade = document.querySelector('input[name="gravidade"]:checked');
            
            if (!frequencia || !gravidade) {
                e.preventDefault();
                alert('Por favor, avalie tanto a Frequência quanto a Gravidade antes de continuar.');
            }
        });
    </script>
</body>
</html>

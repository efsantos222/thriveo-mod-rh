<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../config.php';
require_once 'coach_ai.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$companyId = $_SESSION['company_id'] ?? 1;
$userId = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answers = json_decode($_POST['answers_json'], true);

    if ($answers) {
        // Calculate basic DISC Logic (Simplified for Demo)
        // In a real scenario, each word maps to D, I, S, or C scores.
        // We'll trust the AI to interpret the raw choices or just save the raw data.

        try {
            // Save Assessment
            $stmt = $pdo->prepare("INSERT INTO coach_assessments (company_id, user_id, type, result_json) VALUES (?, ?, 'DISC', ?)");
            $stmt->execute([$companyId, $userId, json_encode($answers)]);

            // Trigger AI Analysis
            $analysis = analyzeAssessment($pdo, $companyId, 'DISC', $answers, $userId);

            // Redirect to dashboard
            header("Location: coach.php?view=dashboard");
            exit;
        } catch (Exception $e) {
            $message = "Erro ao salvar: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Avaliação DISC</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .question-card {
            background: rgba(255, 255, 255, 0.05);
            padding: 2rem;
            border-radius: 1rem;
            margin-top: 2rem;
            border: 1px solid var(--glass-border);
            text-align: center;
        }

        .options-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-top: 2rem;
        }

        .word-item {
            background: rgba(0, 0, 0, 0.2);
            padding: 1rem;
            border-radius: 0.5rem;
            text-align: center;
            border: 1px solid transparent;
        }

        .word-item:hover {
            border-color: var(--primary-color);
        }

        .radio-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        label {
            cursor: pointer;
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .progress-bar {
            width: 100%;
            height: 6px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
            margin: 2rem 0;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: var(--primary-color);
            width: 0%;
            transition: width 0.3s;
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content">
            <h1>Avaliação DISC</h1>

            <?php if ($message): ?>
                <div style="color:red; margin-bottom:1rem;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div id="intro">
                <div class="mlpt-card">
                    <h3>Instruções</h3>
                    <p>Para cada grupo de palavras abaixo, escolha:</p>
                    <ul>
                        <li>1 palavra que <strong>Mais</strong> te descreve (+)</li>
                        <li>1 palavra que <strong>Menos</strong> te descreve (-)</li>
                    </ul>
                    <p>Não pense muito, siga sua intuição.</p>
                    <button class="btn btn-primary" onclick="startTest()">Começar</button>
                </div>
            </div>

            <div id="test-area" style="display:none;">
                <div class="progress-bar">
                    <div id="progress" class="progress-fill"></div>
                </div>
                <div id="question-container"></div>

                <form method="POST" id="submit-form">
                    <input type="hidden" name="answers_json" id="answers_input">
                </form>
            </div>

        </main>
    </div>

    <script>
        const discGroups = [
            { id: 1, words: ['Enérgico', 'Discreto', 'Encorajador', 'Considerado'] },
            { id: 2, words: ['Destemido', 'Amigável', 'Cuidadoso', 'Expressivo'] },
            { id: 3, words: ['Agradável', 'Preciso', 'Franco', 'Bem-Humorado'] },
            { id: 4, words: ['Ousado', 'Calmo', 'Animado', 'Lógico'] },
            { id: 5, words: ['Convincente', 'Equilibrado', 'Original', 'Pacífico'] },
            // Shortened for demo purpose, normally 24
        ];

        let currentIndex = 0;
        let results = [];

        function startTest() {
            document.getElementById('intro').style.display = 'none';
            document.getElementById('test-area').style.display = 'block';
            renderQuestion();
        }

        function renderQuestion() {
            const group = discGroups[currentIndex];
            const percentage = ((currentIndex) / discGroups.length) * 100;
            document.getElementById('progress').style.width = percentage + '%';

            const html = `
        <div class="question-card">
            <h3>Grupo ${currentIndex + 1} de ${discGroups.length}</h3>
            <div class="options-grid">
                ${group.words.map((word, i) => `
                    <div class="word-item">
                        <strong style="font-size:1.1rem; display:block; margin-bottom:0.5rem;">${word}</strong>
                        <div style="display:flex; justify-content:center; gap:1rem;">
                            <label>
                                <input type="radio" name="plus" value="${word}" onclick="checkNext('${word}', 'plus')">
                                MAIS (+)
                            </label>
                            <label>
                                <input type="radio" name="minus" value="${word}" onclick="checkNext('${word}', 'minus')">
                                MENOS (-)
                            </label>
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
            document.getElementById('question-container').innerHTML = html;
        }

        function checkNext(word, type) {
            // Basic UI logic: ensure only one Plus and one Minus selected across the group
            // For simplicity here, we wait for user to click one of each?
            // Or simpler: just let them select and validate on "Next"?
            // I'll add a "Next" button that appears or activates.

            // Actually, let's re-render the button below
            if (!document.getElementById('btn-next')) {
                const btn = document.createElement('button');
                btn.id = 'btn-next';
                btn.className = 'btn btn-primary';
                btn.style.marginTop = '2rem';
                btn.innerText = 'Próximo >';
                btn.onclick = goNext;
                document.querySelector('.question-card').appendChild(btn);
            }
        }

        function goNext() {
            // Validate
            const plus = document.querySelector('input[name="plus"]:checked');
            const minus = document.querySelector('input[name="minus"]:checked');

            if (!plus || !minus) {
                alert("Selecione uma opção MAIS e uma MENOS.");
                return;
            }
            if (plus.value === minus.value) {
                alert("A mesma palavra não pode ser Mais e Menos ao mesmo tempo.");
                return;
            }

            results.push({
                group: currentIndex + 1,
                plus: plus.value,
                minus: minus.value
            });

            currentIndex++;
            if (currentIndex < discGroups.length) {
                renderQuestion();
            } else {
                finishTest();
            }
        }

        function finishTest() {
            document.getElementById('answers_input').value = JSON.stringify(results);
            document.getElementById('submit-form').submit();
        }
    </script>
</body>

</html>
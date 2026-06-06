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
$testId = $_GET['id'] ?? null;

if (!$testId) {
    die("ID do teste não especificado.");
}

// Load Template
$stmt = $pdo->prepare("SELECT * FROM coach_test_templates WHERE id = ?");
$stmt->execute([$testId]);
$template = $stmt->fetch();

if (!$template) {
    die("Teste não encontrado.");
}

// --- HANDLE SUBMISSION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answers = json_decode($_POST['answers_json'], true);

    if ($answers) {
        try {
            // Save Assessment with template_id
            $stmt = $pdo->prepare("INSERT INTO coach_assessments (company_id, user_id, type, template_id, result_json) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$companyId, $userId, $template['type'], $testId, json_encode($answers)]);

            // Custom Analysis Logic
            // We use the prompt from the template
            $prompt = $template['ai_prompt'] . " Respostas do usuário: " . json_encode($answers);

            // Reusing local function but adapting prompt manually
            // We'll effectively bypass the hardcoded prompts in coach_ai.php by passing a custom 'type' or just modifying this block.
            // Let's call OpenAI directly here reusing configuration getter from coach_ai.php

            $config = getCoachAIConfig($pdo, $companyId);
            if ($config && !empty($config['api_key'])) {
                $apiKey = $config['api_key'];
                $model = $config['model'] ?? 'gpt-4o';

                $ch = curl_init('https://api.openai.com/v1/chat/completions');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'Você é um Master Coach especialista.'],
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'temperature' => 0.7
                ]));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $apiKey
                ]);
                $result = curl_exec($ch);
                curl_close($ch);
                $resp = json_decode($result, true);
                $analysis = $resp['choices'][0]['message']['content'] ?? 'Erro na análise.';

                // Update
                $pdo->prepare("UPDATE coach_assessments SET ai_analysis = ? WHERE user_id = ? AND template_id = ? ORDER BY completed_at DESC LIMIT 1")
                    ->execute([$analysis, $userId, $testId]);
            }

            header("Location: coach.php?view=dashboard");
            exit;
        } catch (Exception $e) {
            $error = "Erro ao salvar: " . $e->getMessage();
        }
    }
}

// Prepare Questions JSON for JS
$questionsJson = $template['questions_json']; // Already JSON string from DB
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Avaliação:
        <?php echo htmlspecialchars($template['title']); ?>
    </title>
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

        .options-container {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 2rem;
        }

        .option-btn {
            background: rgba(0, 0, 0, 0.3);
            padding: 1rem;
            border: 1px solid var(--glass-border);
            border-radius: 0.5rem;
            cursor: pointer;
            text-align: left;
            transition: all 0.2s;
            color: var(--text-color);
            width: 100%;
        }

        .option-btn:hover {
            border-color: var(--primary-color);
            background: rgba(59, 130, 246, 0.1);
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

        .disc-word-item {
            padding: 1rem;
            background: rgba(0, 0, 0, 0.2);
            margin-bottom: 1rem;
            border-radius: 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content">
            <h1>
                <?php echo htmlspecialchars($template['title']); ?>
            </h1>
            <p style="color:var(--text-muted);">
                <?php echo htmlspecialchars($template['description']); ?>
            </p>

            <div id="test-area">
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
        const type = "<?php echo $template['type']; ?>";
        const questions = <?php echo $questionsJson ?: '[]'; ?>;
        let currentIndex = 0;
        let results = [];

        function init() {
            if (questions.length === 0) {
                document.getElementById('question-container').innerHTML = "<p>Erro: Nenhuma questão configurada neste teste.</p>";
                return;
            }
            renderQuestion();
        }

        function renderQuestion() {
            const q = questions[currentIndex];
            const percentage = ((currentIndex) / questions.length) * 100;
            document.getElementById('progress').style.width = percentage + '%';

            let html = '';

            if (type === 'DISC') {
                // q.words is expected
                html = `
            <div class="question-card">
                <h3>Grupo ${currentIndex + 1} de ${questions.length}</h3>
                <p>Escolha uma palavra MAIS e uma MENOS que te descreve.</p>
                <div style="text-align:left; margin-top:2rem;">
                    ${q.words.map(word => `
                        <div class="disc-word-item">
                            <strong>${word}</strong>
                            <div style="display:flex; gap:1rem;">
                                <label><input type="radio" name="plus" value="${word}"> +</label>
                                <label><input type="radio" name="minus" value="${word}"> -</label>
                            </div>
                        </div>
                    `).join('')}
                </div>
                <button class="btn btn-primary" onclick="nextDisc()" style="margin-top:1rem;">Próximo ></button>
            </div>
        `;
            } else if (type === 'MBTI' || type === 'CUSTOM') {
                const text = q.question || q.text || "Questão " + (currentIndex + 1);
                const options = q.options || [];

                html = `
            <div class="question-card">
                <h3>Questão ${currentIndex + 1}</h3>
                <p style="font-size:1.2rem; margin:1.5rem 0;">${text}</p>
                <div class="options-container">
                    ${options.map(opt => {
                    const val = (typeof opt === 'object') ? opt.value : opt;
                    const label = (typeof opt === 'object') ? opt.text : opt;
                    return `<button class="option-btn" onclick="selectOption('${val}')">${label}</button>`;
                }).join('')}
                </div>
            </div>
        `;
            }

            document.getElementById('question-container').innerHTML = html;
        }

        function selectOption(val) {
            results.push({ q: currentIndex, a: val });
            nextStep();
        }

        function nextDisc() {
            const plus = document.querySelector('input[name="plus"]:checked');
            const minus = document.querySelector('input[name="minus"]:checked');
            if (!plus || !minus) { alert("Selecione Mais e Menos"); return; }
            if (plus.value === minus.value) { alert("Não pode ser a mesma palavra"); return; }

            results.push({ group: currentIndex, plus: plus.value, minus: minus.value });
            nextStep();
        }

        function nextStep() {
            currentIndex++;
            if (currentIndex < questions.length) {
                renderQuestion();
            } else {
                finishTest();
            }
        }

        function finishTest() {
            document.getElementById('answers_input').value = JSON.stringify(results);
            document.getElementById('submit-form').submit();
        }

        window.onload = init;
    </script>
</body>

</html>
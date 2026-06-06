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
        try {
            $stmt = $pdo->prepare("INSERT INTO coach_assessments (company_id, user_id, type, result_json) VALUES (?, ?, 'MBTI', ?)");
            $stmt->execute([$companyId, $userId, json_encode($answers)]);

            $analysis = analyzeAssessment($pdo, $companyId, 'MBTI', $answers, $userId);

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
    <title>Avaliação MBTI</title>
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
            padding: 1.5rem;
            border: 1px solid var(--glass-border);
            border-radius: 0.5rem;
            cursor: pointer;
            text-align: left;
            transition: all 0.2s;
            color: var(--text-color);
        }

        .option-btn:hover {
            background: rgba(59, 130, 246, 0.1);
            border-color: var(--primary-color);
            transform: translateX(5px);
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
            background: #a855f7;
            width: 0%;
            transition: width 0.3s;
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content">
            <h1>Avaliação MBTI (Simu)</h1>
            <?php if ($message): ?>
                <div style="color:red; margin-bottom:1rem;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div id="intro">
                <div class="mlpt-card">
                    <h3>Descubra seu Tipo de Personalidade</h3>
                    <p>Responda como você geralmente age ou se sente, não como você "deveria" agir.</p>
                    <div
                        style="margin: 1rem 0; padding: 1rem; background: rgba(255,255,255,0.05); border-radius: 0.5rem;">
                        <strong>Dica:</strong> Não pense muito. A primeira resposta geralmente é a mais honesta.
                    </div>
                    <button class="btn btn-primary" onclick="startTest()">Iniciar Teste</button>
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
        const mbtiQuestions = [
            {
                question: "Em festas e eventos sociais, você geralmente:",
                options: [
                    { text: "Interage com muitas pessoas, incluindo desconhecidos (E)", value: "E" },
                    { text: "Interage com poucas pessoas conhecidas (I)", value: "I" }
                ]
            },
            {
                question: "Você prefere lidar com:",
                options: [
                    { text: "Fatos e detalhes concretos (S)", value: "S" },
                    { text: "Ideias e conceitos abstratos (N)", value: "N" }
                ]
            },
            {
                question: "Ao tomar decisões, o que pesa mais?",
                options: [
                    { text: "Lógica e consistência (T)", value: "T" },
                    { text: "Valores pessoais e harmonia (F)", value: "F" }
                ]
            },
            {
                question: "Como você prefere seu estilo de vida?",
                options: [
                    { text: "Organizado, planejado e decisivo (J)", value: "J" },
                    { text: "Flexível, espontâneo e adaptável (P)", value: "P" }
                ]
            },
            {
                question: "Quando você tem um problema para resolver, você:",
                options: [
                    { text: "Fala sobre ele com outras pessoas (E)", value: "E" },
                    { text: "Pensa sobre ele sozinho (I)", value: "I" }
                ]
            },
            {
                question: "Você se considera mais:",
                options: [
                    { text: "Realista (S)", value: "S" },
                    { text: "Visionário (N)", value: "N" }
                ]
            }
            // Expanded for demo
        ];

        let currentIndex = 0;
        let results = [];

        function startTest() {
            document.getElementById('intro').style.display = 'none';
            document.getElementById('test-area').style.display = 'block';
            renderQuestion();
        }

        function renderQuestion() {
            const q = mbtiQuestions[currentIndex];
            const percentage = ((currentIndex) / mbtiQuestions.length) * 100;
            document.getElementById('progress').style.width = percentage + '%';

            const html = `
        <div class="question-card">
            <h3>Questão ${currentIndex + 1} de ${mbtiQuestions.length}</h3>
            <p style="font-size:1.2rem; margin: 1.5rem 0;">${q.question}</p>
            <div class="options-container">
                ${q.options.map(opt => `
                    <button class="option-btn" onclick="selectOption('${opt.value}')">
                        ${opt.text}
                    </button>
                `).join('')}
            </div>
        </div>
    `;
            document.getElementById('question-container').innerHTML = html;
        }

        function selectOption(value) {
            results.push({ q: currentIndex, val: value });
            currentIndex++;
            if (currentIndex < mbtiQuestions.length) {
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
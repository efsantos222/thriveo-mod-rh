<?php
require_once '../../config.php';
require_once '../../includes/storage.php';

$storage = new Storage(STORAGE_DIR);
$sessionId = $_GET['session'] ?? '';
$session = $storage->getSession($sessionId);

if (!$session) {
    header('Location: ../../index.php');
    exit;
}

$configs = $storage->getGameConfigs();
$questions = $configs['mysteryBox']['questions'] ?? [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caixa Misteriosa - <?= htmlspecialchars($session['name']) ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎁 Caixa Misteriosa</h1>
            <p><?= htmlspecialchars($session['name']) ?></p>
        </div>

        <div class="glass-effect-strong" style="padding: 48px; max-width: 800px; margin: 0 auto;">
            <div style="text-align: center; margin-bottom: 32px;">
                <div id="current-participant" style="color: white; font-size: 28px; font-weight: 700; margin-bottom: 16px;" data-testid="text-current-participant">
                    <?= !empty($session['participants']) ? htmlspecialchars($session['participants'][0]) : 'Aguardando...' ?>
                </div>
                <p style="color: rgba(255, 255, 255, 0.9); font-size: 16px;">
                    Abra a caixa misteriosa e responda a pergunta surpresa!
                </p>
            </div>

            <div id="mystery-box" style="text-align: center; margin: 40px 0;">
                <div id="box-closed" style="font-size: 120px; margin-bottom: 24px; cursor: pointer; transition: transform 0.3s ease;" onclick="openBox()" data-testid="box-closed">
                    🎁
                </div>
                <div id="box-open" style="display: none;">
                    <div style="font-size: 80px; margin-bottom: 24px;" data-testid="box-open">✨</div>
                    <div id="question-display" style="background: white; border-radius: 16px; padding: 32px; color: #1f2937; font-size: 20px; line-height: 1.6; min-height: 120px; display: flex; align-items: center; justify-content: center;" data-testid="text-question">
                    </div>
                </div>
            </div>

            <div style="text-align: center; margin-top: 48px; display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
                <button id="open-btn" class="btn btn-primary" onclick="openBox()" data-testid="button-open">
                    🎁 Abrir Caixa
                </button>
                <button id="next-btn" class="btn btn-success" onclick="nextParticipant()" style="display: none;" data-testid="button-next">
                    Próximo Participante →
                </button>
                <button class="btn btn-secondary" onclick="resetGame()" data-testid="button-reset">
                    🔄 Reiniciar
                </button>
                <a href="../manage-session.php?id=<?= $sessionId ?>" class="btn btn-secondary" data-testid="button-back">
                    ← Voltar
                </a>
            </div>

            <div style="margin-top: 32px; color: white; text-align: center;">
                <p style="font-size: 18px;">
                    Participante: <span id="participant-counter" data-testid="text-participant-counter">1</span> de <?= count($session['participants']) ?>
                </p>
            </div>
        </div>
    </div>

    <script>
        const participants = <?= json_encode($session['participants']) ?>;
        const questions = <?= json_encode($questions) ?>;
        
        let currentParticipantIndex = 0;
        let usedQuestions = [];

        function updateDisplay() {
            document.getElementById('current-participant').textContent = 
                participants.length > 0 ? participants[currentParticipantIndex] : 'Nenhum participante';
            document.getElementById('participant-counter').textContent = currentParticipantIndex + 1;
        }

        function getRandomQuestion() {
            let availableQuestions = questions.filter(q => !usedQuestions.includes(q));
            
            if (availableQuestions.length === 0) {
                usedQuestions = [];
                availableQuestions = [...questions];
            }
            
            const question = availableQuestions[Math.floor(Math.random() * availableQuestions.length)];
            usedQuestions.push(question);
            return question;
        }

        function openBox() {
            const boxClosed = document.getElementById('box-closed');
            const boxOpen = document.getElementById('box-open');
            const openBtn = document.getElementById('open-btn');
            const nextBtn = document.getElementById('next-btn');
            const questionDisplay = document.getElementById('question-display');
            
            boxClosed.style.display = 'none';
            boxOpen.style.display = 'block';
            openBtn.style.display = 'none';
            nextBtn.style.display = 'inline-flex';
            
            questionDisplay.textContent = getRandomQuestion();
        }

        function closeBox() {
            const boxClosed = document.getElementById('box-closed');
            const boxOpen = document.getElementById('box-open');
            const openBtn = document.getElementById('open-btn');
            const nextBtn = document.getElementById('next-btn');
            
            boxClosed.style.display = 'block';
            boxOpen.style.display = 'none';
            openBtn.style.display = 'inline-flex';
            nextBtn.style.display = 'none';
        }

        function nextParticipant() {
            if (participants.length > 0) {
                currentParticipantIndex = (currentParticipantIndex + 1) % participants.length;
            }
            closeBox();
            updateDisplay();
        }

        function resetGame() {
            currentParticipantIndex = 0;
            usedQuestions = [];
            closeBox();
            updateDisplay();
        }

        // Hover effect on closed box
        document.getElementById('box-closed').addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1) rotate(5deg)';
        });
        
        document.getElementById('box-closed').addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1) rotate(0deg)';
        });

        updateDisplay();
    </script>
</body>
</html>

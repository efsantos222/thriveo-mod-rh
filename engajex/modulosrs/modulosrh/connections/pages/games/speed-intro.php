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
$items = $configs['speedIntro']['items'] ?? [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apresentação Relâmpago - <?= htmlspecialchars($session['name']) ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚡ Apresentação Relâmpago</h1>
            <p><?= htmlspecialchars($session['name']) ?></p>
        </div>

        <div class="glass-effect-strong" style="padding: 48px; max-width: 800px; margin: 0 auto;">
            <div id="game-container">
                <div id="timer-display" class="timer" data-testid="timer-display">60</div>
                
                <div style="text-align: center; margin-bottom: 32px;">
                    <div id="current-participant" style="color: white; font-size: 28px; font-weight: 700; margin-bottom: 16px;" data-testid="text-current-participant">
                        <?= !empty($session['participants']) ? htmlspecialchars($session['participants'][0]) : 'Aguardando...' ?>
                    </div>
                    <div id="current-challenge" style="color: white; font-size: 20px; background: rgba(255, 255, 255, 0.1); padding: 24px; border-radius: 16px; min-height: 80px; display: flex; align-items: center; justify-content: center;" data-testid="text-current-challenge">
                        Clique em "Iniciar" para começar
                    </div>
                </div>

                <div class="progress-bar">
                    <div id="progress" class="progress-fill" style="width: 0%"></div>
                </div>

                <div style="text-align: center; margin-top: 32px; display: flex; gap: 16px; justify-content: center;">
                    <button id="start-btn" class="btn btn-primary" onclick="startTimer()" data-testid="button-start">
                        ▶ Iniciar
                    </button>
                    <button id="next-btn" class="btn btn-success" onclick="nextChallenge()" style="display: none;" data-testid="button-next">
                        Próximo →
                    </button>
                    <button id="reset-btn" class="btn btn-secondary" onclick="resetGame()" data-testid="button-reset">
                        🔄 Reiniciar
                    </button>
                    <a href="../manage-session.php?id=<?= $sessionId ?>" class="btn btn-secondary" data-testid="button-back">
                        ← Voltar
                    </a>
                </div>

                <div style="margin-top: 32px; color: white; text-align: center;">
                    <p style="font-size: 18px; margin-bottom: 8px;">
                        Participante: <span id="participant-counter" data-testid="text-participant-counter">1</span> de <?= count($session['participants']) ?>
                    </p>
                    <p style="font-size: 18px;">
                        Desafio: <span id="challenge-counter" data-testid="text-challenge-counter">1</span> de <?= count($items) ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        const participants = <?= json_encode($session['participants']) ?>;
        const challenges = <?= json_encode($items) ?>;
        const TIME_LIMIT = 60;
        
        let currentParticipantIndex = 0;
        let currentChallengeIndex = 0;
        let timeRemaining = TIME_LIMIT;
        let timerInterval = null;
        let isRunning = false;

        function updateDisplay() {
            document.getElementById('current-participant').textContent = 
                participants.length > 0 ? participants[currentParticipantIndex] : 'Nenhum participante';
            document.getElementById('current-challenge').textContent = 
                challenges[currentChallengeIndex];
            document.getElementById('participant-counter').textContent = currentParticipantIndex + 1;
            document.getElementById('challenge-counter').textContent = currentChallengeIndex + 1;
        }

        function startTimer() {
            if (isRunning) return;
            
            isRunning = true;
            document.getElementById('start-btn').style.display = 'none';
            document.getElementById('next-btn').style.display = 'inline-flex';
            
            timerInterval = setInterval(() => {
                timeRemaining--;
                updateTimerDisplay();
                
                if (timeRemaining <= 0) {
                    clearInterval(timerInterval);
                    isRunning = false;
                    playSound();
                }
            }, 1000);
        }

        function updateTimerDisplay() {
            const timerEl = document.getElementById('timer-display');
            const progressEl = document.getElementById('progress');
            
            timerEl.textContent = timeRemaining;
            progressEl.style.width = ((TIME_LIMIT - timeRemaining) / TIME_LIMIT * 100) + '%';
            
            timerEl.className = 'timer';
            if (timeRemaining <= 10) {
                timerEl.className = 'timer danger';
            } else if (timeRemaining <= 20) {
                timerEl.className = 'timer warning';
            }
        }

        function playSound() {
            // Simple beep using Web Audio API
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                oscillator.frequency.value = 800;
                oscillator.type = 'sine';
                
                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
                
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.5);
            } catch (e) {
                console.log('Audio not supported');
            }
        }

        function nextChallenge() {
            clearInterval(timerInterval);
            isRunning = false;
            
            // Próximo desafio
            currentChallengeIndex = (currentChallengeIndex + 1) % challenges.length;
            
            // Se voltou ao início dos desafios, próximo participante
            if (currentChallengeIndex === 0 && participants.length > 0) {
                currentParticipantIndex = (currentParticipantIndex + 1) % participants.length;
            }
            
            timeRemaining = TIME_LIMIT;
            updateTimerDisplay();
            updateDisplay();
            
            document.getElementById('start-btn').style.display = 'inline-flex';
            document.getElementById('next-btn').style.display = 'none';
        }

        function resetGame() {
            clearInterval(timerInterval);
            isRunning = false;
            currentParticipantIndex = 0;
            currentChallengeIndex = 0;
            timeRemaining = TIME_LIMIT;
            
            updateTimerDisplay();
            updateDisplay();
            
            document.getElementById('start-btn').style.display = 'inline-flex';
            document.getElementById('next-btn').style.display = 'none';
        }

        // Initialize
        updateDisplay();
        updateTimerDisplay();
    </script>
</body>
</html>

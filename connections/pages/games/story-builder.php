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
$starters = $configs['storyBuilder']['starters'] ?? [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Construtor de Histórias - <?= htmlspecialchars($session['name']) ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📖 Construtor de Histórias</h1>
            <p><?= htmlspecialchars($session['name']) ?></p>
        </div>

        <div class="glass-effect-strong" style="padding: 48px; max-width: 900px; margin: 0 auto;">
            <div style="text-align: center; margin-bottom: 32px;">
                <div id="current-participant" style="color: white; font-size: 28px; font-weight: 700; margin-bottom: 16px;" data-testid="text-current-participant">
                    <?= !empty($session['participants']) ? htmlspecialchars($session['participants'][0]) : 'Aguardando...' ?>
                </div>
                <p style="color: rgba(255, 255, 255, 0.9); font-size: 16px;">
                    Cada participante adiciona uma parte à história
                </p>
            </div>

            <div id="story-container" style="background: white; border-radius: 16px; padding: 32px; margin-bottom: 32px; min-height: 200px;" data-testid="container-story">
                <div id="story-text" style="font-size: 18px; line-height: 1.8; color: #1f2937;">
                    <p id="story-starter" style="font-weight: 600; margin-bottom: 16px;"></p>
                    <div id="story-parts"></div>
                </div>
            </div>

            <div class="input-group">
                <label for="story-input">Adicione sua parte da história:</label>
                <textarea 
                    id="story-input" 
                    class="input" 
                    rows="3" 
                    placeholder="Continue a história..."
                    data-testid="input-story-part"
                ></textarea>
            </div>

            <div style="text-align: center; margin-top: 32px; display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
                <button class="btn btn-primary" onclick="addStoryPart()" data-testid="button-add-part">
                    ✓ Adicionar e Próximo
                </button>
                <button class="btn btn-secondary" onclick="newStory()" data-testid="button-new-story">
                    Nova História
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
        const starters = <?= json_encode($starters) ?>;
        
        let currentParticipantIndex = 0;
        let storyParts = [];
        let currentStarter = '';

        function initStory() {
            currentStarter = starters[Math.floor(Math.random() * starters.length)];
            document.getElementById('story-starter').textContent = currentStarter;
            storyParts = [];
            updateStoryDisplay();
        }

        function updateDisplay() {
            document.getElementById('current-participant').textContent = 
                participants.length > 0 ? participants[currentParticipantIndex] : 'Nenhum participante';
            document.getElementById('participant-counter').textContent = currentParticipantIndex + 1;
        }

        function updateStoryDisplay() {
            const partsContainer = document.getElementById('story-parts');
            
            if (storyParts.length === 0) {
                partsContainer.innerHTML = '<p style="color: #6b7280; font-style: italic;">Aguardando primeira contribuição...</p>';
            } else {
                partsContainer.innerHTML = storyParts.map((part, index) => 
                    `<p style="margin-bottom: 12px;"><strong>${participants[index % participants.length]}:</strong> ${part}</p>`
                ).join('');
            }
        }

        function addStoryPart() {
            const input = document.getElementById('story-input');
            const part = input.value.trim();
            
            if (part) {
                storyParts.push(part);
                input.value = '';
                updateStoryDisplay();
                
                if (participants.length > 0) {
                    currentParticipantIndex = (currentParticipantIndex + 1) % participants.length;
                    updateDisplay();
                }
            }
        }

        function newStory() {
            if (storyParts.length > 0) {
                if (!confirm('Deseja realmente começar uma nova história? A história atual será perdida.')) {
                    return;
                }
            }
            
            currentParticipantIndex = 0;
            initStory();
            updateDisplay();
            document.getElementById('story-input').value = '';
        }

        // Initialize
        initStory();
        updateDisplay();
    </script>
</body>
</html>

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
$emojis = $configs['emojiIntro']['emojis'] ?? [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apresentação com Emoji - <?= htmlspecialchars($session['name']) ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>😀 Apresentação com Emoji</h1>
            <p><?= htmlspecialchars($session['name']) ?></p>
        </div>

        <div class="glass-effect-strong" style="padding: 48px; max-width: 900px; margin: 0 auto;">
            <div style="text-align: center; margin-bottom: 32px;">
                <div id="current-participant" style="color: white; font-size: 28px; font-weight: 700; margin-bottom: 16px;" data-testid="text-current-participant">
                    <?= !empty($session['participants']) ? htmlspecialchars($session['participants'][0]) : 'Aguardando...' ?>
                </div>
                <p style="color: rgba(255, 255, 255, 0.9); font-size: 16px;">
                    Selecione 3-5 emojis que representam você e conte sua história
                </p>
            </div>

            <div id="selected-emojis" style="min-height: 100px; background: rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 24px; margin-bottom: 32px; text-align: center; display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; align-items: center;" data-testid="container-selected-emojis">
                <p style="color: rgba(255, 255, 255, 0.7);">Nenhum emoji selecionado</p>
            </div>

            <div class="emoji-grid">
                <?php foreach ($emojis as $emoji): ?>
                    <button class="emoji-button" onclick="toggleEmoji('<?= $emoji ?>')" data-testid="button-emoji-<?= $emoji ?>">
                        <?= $emoji ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div style="text-align: center; margin-top: 32px; display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
                <button class="btn btn-primary" onclick="nextParticipant()" data-testid="button-next">
                    Próximo Participante →
                </button>
                <button class="btn btn-secondary" onclick="clearSelection()" data-testid="button-clear">
                    Limpar Seleção
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
        let currentParticipantIndex = 0;
        let selectedEmojis = [];

        function updateDisplay() {
            document.getElementById('current-participant').textContent = 
                participants.length > 0 ? participants[currentParticipantIndex] : 'Nenhum participante';
            document.getElementById('participant-counter').textContent = currentParticipantIndex + 1;
        }

        function toggleEmoji(emoji) {
            const index = selectedEmojis.indexOf(emoji);
            
            if (index > -1) {
                selectedEmojis.splice(index, 1);
            } else {
                selectedEmojis.push(emoji);
            }
            
            updateSelectedDisplay();
            updateButtonStates();
        }

        function updateSelectedDisplay() {
            const container = document.getElementById('selected-emojis');
            
            if (selectedEmojis.length === 0) {
                container.innerHTML = '<p style="color: rgba(255, 255, 255, 0.7);">Nenhum emoji selecionado</p>';
            } else {
                container.innerHTML = selectedEmojis.map(emoji => 
                    `<span style="font-size: 48px;">${emoji}</span>`
                ).join('');
            }
        }

        function updateButtonStates() {
            const buttons = document.querySelectorAll('.emoji-button');
            buttons.forEach(btn => {
                const emoji = btn.textContent.trim();
                if (selectedEmojis.includes(emoji)) {
                    btn.classList.add('selected');
                } else {
                    btn.classList.remove('selected');
                }
            });
        }

        function clearSelection() {
            selectedEmojis = [];
            updateSelectedDisplay();
            updateButtonStates();
        }

        function nextParticipant() {
            if (participants.length > 0) {
                currentParticipantIndex = (currentParticipantIndex + 1) % participants.length;
            }
            clearSelection();
            updateDisplay();
        }

        function resetGame() {
            currentParticipantIndex = 0;
            clearSelection();
            updateDisplay();
        }

        updateDisplay();
    </script>
</body>
</html>

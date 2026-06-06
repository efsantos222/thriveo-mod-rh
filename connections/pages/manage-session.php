<?php
require_once '../config.php';
require_once '../includes/storage.php';

$storage = new Storage(STORAGE_DIR);
$sessionId = $_GET['id'] ?? '';
$session = $storage->getSession($sessionId);

if (!$session) {
    header('Location: ../index.php');
    exit;
}

// Handle participant addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_participant'])) {
    $newParticipant = trim($_POST['participant'] ?? '');
    if (!empty($newParticipant)) {
        $session['participants'][] = $newParticipant;
        $storage->updateSession($sessionId, $session);
        header('Location: manage-session.php?id=' . $sessionId);
        exit;
    }
}

// Handle participant removal
if (isset($_GET['remove_participant'])) {
    $index = intval($_GET['remove_participant']);
    if (isset($session['participants'][$index])) {
        array_splice($session['participants'], $index, 1);
        $session['participants'] = array_values($session['participants']);
        $storage->updateSession($sessionId, $session);
        header('Location: manage-session.php?id=' . $sessionId);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($session['name']) ?> - Virtual Connections</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?= htmlspecialchars($session['name']) ?></h1>
            <p>Gerencie participantes e escolha um jogo</p>
        </div>

        <div class="grid grid-2">
            <!-- Participants Section -->
            <div class="glass-effect-strong" style="padding: 32px;">
                <h2 style="color: white; font-size: 24px; margin-bottom: 24px;">👥 Participantes (<?= count($session['participants']) ?>)</h2>
                
                <form method="POST" class="mb-4">
                    <div class="input-group">
                        <input 
                            type="text" 
                            name="participant" 
                            class="input" 
                            placeholder="Nome do participante..."
                            data-testid="input-add-participant"
                        >
                    </div>
                    <button type="submit" name="add_participant" class="btn btn-primary" style="width: 100%;" data-testid="button-add-participant">
                        + Adicionar Participante
                    </button>
                </form>

                <?php if (empty($session['participants'])): ?>
                    <p style="color: rgba(255, 255, 255, 0.8); text-align: center; padding: 20px;">
                        Nenhum participante adicionado ainda
                    </p>
                <?php else: ?>
                    <ul class="list">
                        <?php foreach ($session['participants'] as $index => $participant): ?>
                            <li class="list-item" data-testid="participant-<?= $index ?>">
                                <div class="list-item-content">
                                    <?= htmlspecialchars($participant) ?>
                                </div>
                                <a 
                                    href="?id=<?= $sessionId ?>&remove_participant=<?= $index ?>" 
                                    class="btn btn-danger btn-small"
                                    onclick="return confirm('Remover este participante?')"
                                    data-testid="button-remove-participant-<?= $index ?>"
                                >
                                    ✕
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Games Section -->
            <div class="glass-effect-strong" style="padding: 32px;">
                <h2 style="color: white; font-size: 24px; margin-bottom: 24px;">🎮 Escolha um Jogo</h2>
                
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <a href="games/speed-intro.php?session=<?= $sessionId ?>" class="card" style="text-decoration: none; margin: 0;" data-testid="link-game-speed">
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 32px;">
                                ⚡
                            </div>
                            <div style="flex: 1;">
                                <h3 style="font-size: 18px; margin-bottom: 4px;">Apresentação Relâmpago</h3>
                                <p style="color: #6b7280; font-size: 14px;">Rápido e dinâmico</p>
                            </div>
                        </div>
                    </a>

                    <a href="games/emoji-intro.php?session=<?= $sessionId ?>" class="card" style="text-decoration: none; margin: 0;" data-testid="link-game-emoji">
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #ec4899 0%, #d946ef 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 32px;">
                                😀
                            </div>
                            <div style="flex: 1;">
                                <h3 style="font-size: 18px; margin-bottom: 4px;">Apresentação com Emoji</h3>
                                <p style="color: #6b7280; font-size: 14px;">Visual e criativo</p>
                            </div>
                        </div>
                    </a>

                    <a href="games/story-builder.php?session=<?= $sessionId ?>" class="card" style="text-decoration: none; margin: 0;" data-testid="link-game-story">
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 32px;">
                                📖
                            </div>
                            <div style="flex: 1;">
                                <h3 style="font-size: 18px; margin-bottom: 4px;">Construtor de Histórias</h3>
                                <p style="color: #6b7280; font-size: 14px;">Colaborativo</p>
                            </div>
                        </div>
                    </a>

                    <a href="games/mystery-box.php?session=<?= $sessionId ?>" class="card" style="text-decoration: none; margin: 0;" data-testid="link-game-mystery">
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 32px;">
                                🎁
                            </div>
                            <div style="flex: 1;">
                                <h3 style="font-size: 18px; margin-bottom: 4px;">Caixa Misteriosa</h3>
                                <p style="color: #6b7280; font-size: 14px;">Surpreendente</p>
                            </div>
                        </div>
                    </a>
                </div>

                <a href="../index.php" class="btn btn-secondary" style="width: 100%; margin-top: 24px;" data-testid="button-home">
                    ← Voltar ao Início
                </a>
            </div>
        </div>
    </div>
</body>
</html>

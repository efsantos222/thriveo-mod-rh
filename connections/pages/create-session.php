<?php
require_once '../config.php';
require_once '../includes/storage.php';

$storage = new Storage(STORAGE_DIR);
$message = '';
$sessionId = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $participants = array_filter(array_map('trim', explode("\n", $_POST['participants'] ?? '')));
    
    if (!empty($name)) {
        $session = $storage->createSession([
            'name' => $name,
            'participants' => $participants
        ]);
        $sessionId = $session['id'];
        header('Location: manage-session.php?id=' . $sessionId);
        exit;
    } else {
        $message = 'Por favor, insira um nome para a sessão.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Sessão - Virtual Connections</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✨ Criar Nova Sessão</h1>
            <p>Configure sua sessão de apresentações</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-error"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="glass-effect-strong" style="padding: 48px; max-width: 600px; margin: 0 auto;">
            <form method="POST">
                <div class="input-group">
                    <label for="name">Nome da Sessão *</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        class="input" 
                        placeholder="Ex: Reunião de Equipe - Janeiro 2025"
                        required
                        data-testid="input-session-name"
                    >
                </div>

                <div class="input-group">
                    <label for="participants">Participantes (um por linha)</label>
                    <textarea 
                        id="participants" 
                        name="participants" 
                        class="input" 
                        rows="8" 
                        placeholder="João Silva&#10;Maria Santos&#10;Pedro Oliveira&#10;Ana Costa"
                        style="resize: vertical;"
                        data-testid="input-participants"
                    ></textarea>
                    <small style="color: rgba(255, 255, 255, 0.8); display: block; margin-top: 8px;">
                        Você pode adicionar participantes agora ou depois
                    </small>
                </div>

                <div class="flex-center gap-4" style="margin-top: 32px;">
                    <a href="../index.php" class="btn btn-secondary" data-testid="button-back">
                        ← Voltar
                    </a>
                    <button type="submit" class="btn btn-primary" data-testid="button-create">
                        Criar Sessão →
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

<?php
$pageTitle = 'Configurações';
require_once '../config.php';
require_once 'header.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $apiKey = $_POST['openai_api_key'];
    if ($apiKey) {
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'openai_api_key'");
        $stmt->execute([$apiKey]);
        // Also check if it existed, if not insert (edge case but good to have)
        if ($stmt->rowCount() == 0) {
            // Check if row actually exists
            $check = $pdo->query("SELECT count(*) FROM settings WHERE setting_key = 'openai_api_key'")->fetchColumn();
            if ($check == 0) {
                $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('openai_api_key', ?)");
                $stmt->execute([$apiKey]);
            }
        }
        $message = "Chave API atualizada com sucesso.";
    }
}

$stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'openai_api_key'");
$currentKey = $stmt->fetchColumn();
?>

<?php if ($message): ?>
    <div
        style="background: rgba(16, 185, 129, 0.2); color: #6ee7b7; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<div class="glass-card">
    <h3>Configurações de API</h3>
    <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Insira a chave da API do OpenAI para habilitar a geração
        de roteiros.</p>

    <form method="POST">
        <div class="form-group">
            <label class="form-label">OpenAI API Key</label>
            <input type="password" name="openai_api_key" class="form-control"
                value="<?= htmlspecialchars($currentKey) ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
    </form>
</div>

<?php require_once 'footer.php'; ?>
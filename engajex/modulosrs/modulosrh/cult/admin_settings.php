<?php
require_once 'config/db.php';
require_once 'includes/header.php';
requireAuth();

if ($_SESSION['role'] !== 'superadmin') {
    header("Location: index.php");
    exit;
}

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $key = $_POST['openai_key'];

    // Check if exists
    $stmt = $pdo->prepare("SELECT id FROM settings WHERE setting_key = 'openai_key'");
    $stmt->execute();
    if ($stmt->fetch()) {
        $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'openai_key'")->execute([$key]);
    } else {
        $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('openai_key', ?)")->execute([$key]);
    }
    $msg = "Chave API atualizada com sucesso.";
}

$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'openai_key'");
$stmt->execute();
$currentKey = $stmt->fetchColumn();
?>

<div class="container" style="max-width: 600px;">
    <h2>Configurações do Sistema</h2>

    <?php if ($msg): ?>
        <div
            style="background: rgba(16, 185, 129, 0.2); color: #6ee7b7; padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem;">
            <?= $msg ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h3>Integração OpenAI</h3>
        <p style="color: var(--text-muted); margin-bottom: 1rem;">Esta chave será usada para gerar os relatórios de
            cultura organizacional.</p>

        <form method="POST">
            <div class="input-group">
                <label>API Key (sk-...)</label>
                <input type="text" name="openai_key" class="form-control"
                    value="<?= htmlspecialchars($currentKey ?? '') ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Salvar Configuração</button>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
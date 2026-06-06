<?php
// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_key'])) {
    $newKey = trim($_POST['api_key']);
    if (!empty($newKey)) {
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('openai_api_key', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$newKey, $newKey]);
        echo "<div style='color: #88ff88; margin-bottom: 1rem;'>Chave API atualizada com sucesso!</div>";
    }
}

// Fetch Current Key
$stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'openai_api_key'");
$currentKey = $stmt->fetchColumn();
?>

<h2>Configurações do Sistema</h2>

<div class="glass-card" style="max-width: 600px;">
    <h3>Integração OpenAI</h3>
    <p>Insira a chave da API para habilitar as funcionalidades de IA.</p>

    <form method="POST">
        <div class="form-group">
            <label>Chave API (sk-...)</label>
            <input type="password" name="api_key" value="<?= htmlspecialchars($currentKey ?? '') ?>"
                placeholder="sk-..." required>
        </div>
        <button type="submit" name="update_key" class="btn btn-primary">Salvar Chave</button>
    </form>
</div>
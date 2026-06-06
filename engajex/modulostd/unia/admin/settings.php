<?php
require_once '../config/db.php';
require_once 'header.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $apiKey = $_POST['openai_api_key'];
    $model = $_POST['openai_model'];
    $temp = $_POST['openai_temperature'];

    // Helper function to update or insert
    function updateSetting($pdo, $key, $value)
    {
        $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$key, $value, $value]);
    }

    try {
        updateSetting($pdo, 'openai_api_key', $apiKey);
        updateSetting($pdo, 'openai_model', $model);
        updateSetting($pdo, 'openai_temperature', $temp);
        $message = "Configurações salvas com sucesso!";
    } catch (Exception $e) {
        $message = "Erro ao salvar: " . $e->getMessage();
    }
}

// Fetch current settings
$stmt = $pdo->query("SELECT * FROM system_settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Defaults
$currentKey = $settings['openai_api_key'] ?? '';
$currentModel = $settings['openai_model'] ?? 'gpt-4o';
$currentTemp = $settings['openai_temperature'] ?? '0.5';

?>

<div style="margin-bottom: 2rem;">
    <h1>Configurações do Sistema</h1>
    <p style="color: var(--text-muted);">Gerencie as chaves de API e parâmetros da Inteligência Artificial.</p>
</div>

<?php if ($message): ?>
    <div
        style="background: rgba(16, 185, 129, 0.2); color: #10b981; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
        <?= $message ?>
    </div>
<?php endif; ?>

<div class="card" style="max-width: 600px;">
    <h3>Integração OpenAI (GPT)</h3>
    <form method="POST" action="settings.php">
        <div class="form-group">
            <label>Chave da API (API Key)</label>
            <input type="password" name="openai_api_key" class="form-control"
                value="<?= htmlspecialchars($currentKey) ?>" placeholder="sk-..." required>
            <small style="color: var(--text-muted);">A chave será usada para gerar conteúdos e avaliações.</small>
        </div>

        <div class="row" style="display: flex; gap: 1rem;">
            <div class="form-group" style="flex: 1;">
                <label>Modelo</label>
                <select name="openai_model" class="form-control">
                    <option value="gpt-4o" <?= $currentModel == 'gpt-4o' ? 'selected' : '' ?>>gpt-4o (Recomendado)</option>
                    <option value="gpt-4-turbo" <?= $currentModel == 'gpt-4-turbo' ? 'selected' : '' ?>>gpt-4-turbo
                    </option>
                    <option value="gpt-3.5-turbo" <?= $currentModel == 'gpt-3.5-turbo' ? 'selected' : '' ?>>gpt-3.5-turbo
                    </option>
                </select>
            </div>
            <div class="form-group" style="flex: 1;">
                <label>Temperatura (Criatividade)</label>
                <input type="number" step="0.1" min="0" max="1" name="openai_temperature" class="form-control"
                    value="<?= htmlspecialchars($currentTemp) ?>">
                <small style="color: var(--text-muted);">0.0 (Fixo) a 1.0 (Criativo). Recomendado: 0.5</small>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Salvar Configurações</button>
    </form>
</div>

</body>

</html>
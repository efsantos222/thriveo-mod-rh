<?php
use PDI\models\SystemSetting;

// Verify access
if ($_SESSION['user_role'] !== 'superadmin') {
    die("Acesso negado");
}

$settingModel = new SystemSetting($pdo);
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $apiKey = $_POST['openai_key'] ?? '';
    if ($settingModel->set('openai_api_key', $apiKey)) {
        $success = "Configurações salvas com sucesso.";
    }
}

$currentKey = $settingModel->get('openai_api_key');

ob_start();
?>
<div class="row">
    <div class="col-md-12">
        <h2>Configurações do Sistema</h2>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Integração IA</div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="openai_key" class="form-label">OpenAI API Key</label>
                        <input type="password" class="form-control" id="openai_key" name="openai_key"
                            value="<?php echo htmlspecialchars($currentKey ?? ''); ?>">
                        <div class="form-text">Chave utilizada para gerar os PDIs automaticamente.</div>
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = "Configurações";
require_once TEMPLATES_PATH . '/base.php';
?>
<?php
session_start();
require_once __DIR__ . '/config/db.php';

// Only Super Admin can access System Settings
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'super_admin') {
    header("Location: dashboard.php");
    exit;
}

$page = 'settings';
$message = '';

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $apiKey = $_POST['openai_api_key'];
    // Update or Insert
    $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES ('openai_api_key', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    if ($stmt->execute([$apiKey, $apiKey])) {
        $message = "Configurações atualizadas!";
    }
}

// Fetch Current Settings
$stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'openai_api_key'");
$stmt->execute();
$currentKey = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Configurações do Sistema - Thriveo ZBB</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body style="background-color: var(--background-color);">

    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <h1 class="mb-4">Configurações do Sistema</h1>

            <?php if ($message): ?>
                <div
                    style="background-color: #d1fae5; color: #065f46; padding: 1rem; border-radius: var(--border-radius); margin-bottom: 2rem;">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div class="card" style="max-width: 600px;">
                <h3 class="mb-4">Integração OpenAI</h3>
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label">API Key (GPT-4o)</label>
                        <input type="password" name="openai_api_key" class="form-control"
                            value="<?= htmlspecialchars($currentKey ?? '') ?>">
                        <small style="color: var(--text-secondary); display: block; margin-top: 0.5rem;">
                            Chave utilizada para inteligência competitiva e análise de justificativas.
                        </small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Temperatura do Modelo</label>
                        <input type="number" step="0.1" min="0" max="1" value="0.5" class="form-control" disabled
                            style="background-color: #e2e8f0;">
                        <small style="color: var(--text-secondary); display: block; margin-top: 0.5rem;">
                            Configurado fixo em 0.5 conforme requisitos.
                        </small>
                    </div>

                    <button type="submit" class="btn btn-primary">Salvar Configurações</button>
                </form>
            </div>

        </main>
    </div>
</body>

</html>
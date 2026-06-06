<?php
require_once '../config/config.php';

if (!isLoggedIn() || !hasRole('superadmin')) {
    redirect('../login.php');
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $key = $_POST['openai_key'];
    // Upsert
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('openai_api_key', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->execute([$key, $key]);
    $message = "Chave API atualizada com sucesso.";
}

// Get current key
$stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'openai_api_key'");
$current_key = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Configurações - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content">
            <header class="topbar">
                <h2>Configurações do Sistema</h2>
            </header>
            <div class="page-content">
                <div class="card" style="max-width:600px;">
                    <div class="card-header">
                        <h3 class="card-title">Integração OpenAI (ChatGPT)</h3>
                    </div>
                    <?php if ($message): ?>
                        <div
                            style="padding:1rem; background:#dcfce7; color:#166534; border-radius:0.5rem; margin-bottom:1rem;">
                            <?php echo $message; ?></div><?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label>OpenAI API Key</label>
                            <input type="password" name="openai_key" class="form-control"
                                value="<?php echo htmlspecialchars($current_key ?? ''); ?>" placeholder="sk-..."
                                required>
                            <small style="color:#666; display:block; margin-top:0.5rem;">Esta chave será usada para
                                todas as funções de IA (Onboarding, Offboarding, Knowledge Transfer).</small>
                        </div>
                        <button type="submit" class="btn btn-primary">Salvar Chave</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>

</html>
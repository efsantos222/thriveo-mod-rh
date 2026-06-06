<?php
require_once '../config.php';

if (!isAdmin()) {
    header("Location: ../login.php");
    exit;
}

// Handle API Key Update
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['api_key'])) {
    $apiKey = $_POST['api_key'];
    try {
        // Update main system settings
        $stmt = $pdo->prepare("REPLACE INTO settings (setting_key, setting_value) VALUES ('openai_api_key', ?)");
        $stmt->execute([$apiKey]);

        // Sync with SoftSkill module settings
        $stmtSS = $pdo->prepare("INSERT INTO ss_settings (setting_key, setting_value) 
                                 VALUES ('openai_api_key', ?) 
                                 ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmtSS->execute([$apiKey, $apiKey]);

        $message = "Chave API atualizada com sucesso em todos os módulos!";
    } catch (Exception $e) {
        $message = "Erro ao atualizar chave.";
    }
}

// Fetch current Key
$currentKey = '';
$stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'openai_api_key'");
if ($row = $stmt->fetch()) {
    $currentKey = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Painel Admin - TestProf Engaja</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        /* Styles moved to assets/css/style.css */
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <h1>Bem-vindo, Administrador</h1>
            <p style="color: var(--text-muted); margin-bottom: 2rem;">Gerencie as configurações do sistema.</p>

            <?php if ($message): ?>
                <div
                    style="background: rgba(16, 185, 129, 0.2); color: #34d399; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="card" style="max-width: 600px;">
                <h3>Configuração da OpenAI</h3>
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Chave API (sk-...)</label>
                        <input type="text" name="api_key" class="form-control"
                            value="<?php echo htmlspecialchars($currentKey); ?>" placeholder="sk-..." required>
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar Chave</button>
                    <p style="font-size: 0.8rem; margin-top: 1rem; color: var(--text-muted);">
                        Modelo utilizado: GPT-4o, Temperature: 0.5
                    </p>
                </form>
            </div>

            <div class="features" style="margin-top: 2rem;">
                <div class="card">
                    <h3>Empresas Cadastradas</h3>
                    <p>Gerenciar cadastros de empresas e responsáveis.</p>
                    <a href="companies.php" class="btn btn-outline"
                        style="margin-top: 1rem; display:inline-block;">Acessar</a>
                </div>
                <div class="card">
                    <h3>Todos os Usuários</h3>
                    <p>Visualizar e editar todos os usuários do sistema.</p>
                    <a href="users.php" class="btn btn-outline"
                        style="margin-top: 1rem; display:inline-block;">Acessar</a>
                </div>
            </div>

        </main>
    </div>
</body>

</html>
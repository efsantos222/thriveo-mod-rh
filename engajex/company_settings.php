<?php
// company_settings.php
require_once 'config.php';

if (!isLoggedIn() || !in_array($_SESSION['role'], ['responsible', 'company_admin', 'admin'])) {
    header("Location: login.php");
    exit;
}

$company_id = $_SESSION['company_id'];
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $apiKey = $_POST['openai_api_key'] ?? '';
    
    try {
        $stmt = $pdo->prepare("UPDATE companies SET openai_api_key = ? WHERE id = ?");
        $stmt->execute([$apiKey, $company_id]);
        $message = "Configurações da empresa atualizadas com sucesso!";
    } catch (Exception $e) {
        $error = "Erro ao atualizar configurações.";
    }
}

// Fetch current data
$stmt = $pdo->prepare("SELECT name, openai_api_key FROM companies WHERE id = ?");
$stmt->execute([$company_id]);
$company = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Configurações da Empresa - TestProf Engaja</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="app-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <h1 style="margin-bottom: 2rem;">Configurações da Empresa: <?php echo htmlspecialchars($company['name']); ?></h1>

            <div class="card" style="max-width: 600px;">
                <h3 style="margin-bottom: 1.5rem;">Inteligência Artificial (OpenAI)</h3>
                
                <?php if ($message): ?>
                    <div style="background: #f0fdf4; color: #15803d; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; border: 1px solid rgba(16, 185, 129, 0.3);">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Chave API da OpenAI (Específica da Empresa)</label>
                        <input type="password" name="openai_api_key" class="form-control" 
                               value="<?php echo htmlspecialchars($company['openai_api_key'] ?? ''); ?>" 
                               placeholder="sk-..." required>
                        <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.5rem;">
                            Insira aqui a chave da sua empresa. Os relatórios de SoftSkill utilizarão esta chave para faturar diretamente na sua conta da OpenAI.
                        </p>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Salvar Configurações</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>

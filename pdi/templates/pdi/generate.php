<?php
use PDI\models\User;
use PDI\models\Company;
use PDI\models\SystemSetting;
use PDI\services\AIService;

// Guard
if (!in_array($_SESSION['user_role'], ['superadmin', 'company_admin', 'manager'])) {
    die("Acesso negado");
}

$userId = $_GET['user_id'] ?? 0;
$userModel = new User($pdo);
$companyModel = new Company($pdo);

$user = $userModel->get($userId);

if (!$user) {
    die("Usuário não encontrado.");
}

$company = $companyModel->get($user['company_id']);

$message = '';
$generatedContent = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $settings = new SystemSetting($pdo);
        $aiService = new AIService($settings);

        $generatedContent = $aiService->generatePDI($user, $company);

        // Save to Database (pdis table)
        // Assuming pdis table has: user_id, title, objective, notes (content)
        // Check schema.sql: pdis (user_id, title, objective, start_date, end_date, status, notes)

        $stmt = $pdo->prepare("INSERT INTO pdis (user_id, title, objective, start_date, end_date, notes) VALUES (?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), ?)");
        $stmt->execute([
            $userId,
            "PDI Gerado por IA",
            "Desenvolvimento Profissional Baseado em IA", // Objective
            $generatedContent // Notes/Content
        ]);

        $message = "PDI Gerado com Sucesso!";

    } catch (Exception $e) {
        $message = "Erro: " . $e->getMessage();
    }
}

ob_start();
?>
<h2>Gerar PDI com IA</h2>
<p>Colaborador: <strong><?php echo htmlspecialchars($user['name']); ?></strong></p>

<?php if ($message): ?>
    <div class="alert alert-info"><?php echo $message; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <p>Ao clicar em "Gerar", o sistema analisará os dados do colaborador e da empresa para criar uma sugestão de
            PDI.</p>

        <form method="post">
            <button type="submit" class="btn btn-primary">Gerar Suggestão de PDI</button>
            <a href="?route=company/users" class="btn btn-secondary">Voltar</a>
        </form>
    </div>
</div>

<?php if ($generatedContent): ?>
    <div class="card mt-4">
        <div class="card-header">Conteúdo Gerado</div>
        <div class="card-body">
            <pre style="white-space: pre-wrap;"><?php echo htmlspecialchars($generatedContent); ?></pre>
        </div>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
$pageTitle = 'Gerar PDI';
require_once TEMPLATES_PATH . '/base.php';
?>
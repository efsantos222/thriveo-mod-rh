<?php
use PDI\models\Company;

// Guard
if (($_SESSION['user_role'] ?? '') !== 'company_admin') {
    die('Acesso negado');
}

$companyId = $_SESSION['company_id'];
$companyModel = new Company($pdo);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($companyModel->update($companyId, $_POST)) {
        $message = "Identidade atualizada com sucesso!";
    } else {
        $message = "Erro ao atualizar.";
    }
}

$company = $companyModel->get($companyId);

ob_start();
?>
<h2>Identidade Organizacional</h2>
<?php if ($message): ?>
    <div class="alert alert-info"><?php echo $message; ?></div><?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Propósito</label>
                <textarea name="identity_purpose" class="form-control"
                    rows="3"><?php echo htmlspecialchars($company['identity_purpose'] ?? ''); ?></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Missão</label>
                    <textarea name="identity_mission" class="form-control"
                        rows="4"><?php echo htmlspecialchars($company['identity_mission'] ?? ''); ?></textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Visão</label>
                    <textarea name="identity_vision" class="form-control"
                        rows="4"><?php echo htmlspecialchars($company['identity_vision'] ?? ''); ?></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Princípios</label>
                    <textarea name="identity_principles" class="form-control"
                        rows="4"><?php echo htmlspecialchars($company['identity_principles'] ?? ''); ?></textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Valores</label>
                    <textarea name="identity_values" class="form-control"
                        rows="4"><?php echo htmlspecialchars($company['identity_values'] ?? ''); ?></textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-success">Salvar Identidade</button>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Identidade Organizacional';
require_once TEMPLATES_PATH . '/base.php';
?>
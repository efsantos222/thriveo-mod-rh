require_once 'config/db.php';
requireAuth();
require_once 'includes/header.php';

if ($_SESSION['role'] !== 'manager') {
header("Location: index.php");
exit;
}

$companyId = $_SESSION['company_id'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$purpose = $_POST['purpose'];
$mission = $_POST['mission'];
$vision = $_POST['vision'];
$principles = $_POST['principles'];
$values = $_POST['values'];

// Check if exists
$stmt = $pdo->prepare("SELECT id FROM company_identity WHERE company_id = ?");
$stmt->execute([$companyId]);
if ($stmt->fetch()) {
$sql = "UPDATE company_identity SET purpose=?, mission=?, vision=?, principles=?, values_text=? WHERE company_id=?";
$pdo->prepare($sql)->execute([$purpose, $mission, $vision, $principles, $values, $companyId]);
} else {
$sql = "INSERT INTO company_identity (company_id, purpose, mission, vision, principles, values_text) VALUES (?, ?, ?, ?,
?, ?)";
$pdo->prepare($sql)->execute([$companyId, $purpose, $mission, $vision, $principles, $values]);
}
$msg = "Identidade organizacional atualizada.";
}

$data = $pdo->prepare("SELECT * FROM company_identity WHERE company_id = ?");
$data->execute([$companyId]);
$identity = $data->fetch() ?: [];
?>

<div class="container" style="max-width: 800px;">
    <h2>Identidade Organizacional</h2>
    <p style="color: var(--text-muted); margin-bottom: 2rem;">Defina os pilares da sua organização para basear a análise
        cultural.</p>

    <?php if ($msg): ?>
        <div
            style="background: rgba(16, 185, 129, 0.2); color: #6ee7b7; padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem;">
            <?= $msg ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="card">
        <div class="input-group">
            <label>Propósito</label>
            <textarea name="purpose" class="form-control"
                rows="3"><?= htmlspecialchars($identity['purpose'] ?? '') ?></textarea>
        </div>

        <div class="grid-2">
            <div class="input-group">
                <label>Missão</label>
                <textarea name="mission" class="form-control"
                    rows="4"><?= htmlspecialchars($identity['mission'] ?? '') ?></textarea>
            </div>
            <div class="input-group">
                <label>Visão</label>
                <textarea name="vision" class="form-control"
                    rows="4"><?= htmlspecialchars($identity['vision'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="input-group">
            <label>Princípios</label>
            <textarea name="principles" class="form-control"
                rows="4"><?= htmlspecialchars($identity['principles'] ?? '') ?></textarea>
        </div>

        <div class="input-group">
            <label>Valores</label>
            <textarea name="values" class="form-control"
                rows="4"><?= htmlspecialchars($identity['values_text'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Salvar Identidade</button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
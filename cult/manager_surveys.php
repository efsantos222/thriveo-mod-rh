require_once 'config/db.php';
requireAuth();
require_once 'includes/header.php';

if ($_SESSION['role'] !== 'manager') {
header("Location: index.php");
exit;
}

$companyId = $_SESSION['company_id'];
$msg = '';

// Create Survey
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
$title = $_POST['title'];
$desc = $_POST['description'];

$stmt = $pdo->prepare("INSERT INTO surveys (company_id, title, description) VALUES (?, ?, ?)");
$stmt->execute([$companyId, $title, $desc]);
$msg = "Pesquisa criada.";
}

// Delete Survey
if (isset($_GET['delete'])) {
$id = $_GET['delete'];
$stmt = $pdo->prepare("DELETE FROM surveys WHERE id = ? AND company_id = ?");
$stmt->execute([$id, $companyId]);
header("Location: manager_surveys.php");
exit;
}

$surveys = $pdo->prepare("SELECT * FROM surveys WHERE company_id = ? ORDER BY created_at DESC");
$surveys->execute([$companyId]);
$surveys = $surveys->fetchAll();
?>

<div class="flex-between mb-4">
    <h2>Pesquisas de Clima</h2>
    <button onclick="document.getElementById('newSurveyModal').style.display='block'" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nova Pesquisa
    </button>
</div>

<?php if ($msg): ?>
    <div
        style="background: rgba(16, 185, 129, 0.2); color: #6ee7b7; padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem;">
        <?= $msg ?>
    </div>
<?php endif; ?>

<div class="grid-2">
    <?php foreach ($surveys as $survey): ?>
        <div class="card">
            <h3><?= htmlspecialchars($survey['title']) ?></h3>
            <p style="color: var(--text-muted); margin-bottom: 1rem;"><?= htmlspecialchars($survey['description']) ?></p>
            <div class="flex-between" style="border-top: 1px solid var(--border); padding-top: 1rem; margin-top: 1rem;">
                <a href="manager_questions.php?id=<?= $survey['id'] ?>" class="btn btn-outline"
                    style="font-size: 0.9rem;">Editar Perguntas</a>
                <a href="?delete=<?= $survey['id'] ?>" class="btn btn-danger" style="padding: 0.5rem; font-size: 0.8rem;"
                    onclick="return confirm('Apagar pesquisa?')">
                    <i class="fas fa-trash"></i>
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Modal -->
<div id="newSurveyModal"
    style="display:none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:1000;">
    <div class="card" style="width: 90%; max-width: 500px; margin: 5% auto;">
        <h3>Nova Pesquisa</h3>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="input-group">
                <label>Título</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="input-group">
                <label>Descrição</label>
                <input type="text" name="description" class="form-control">
            </div>
            <div class="flex-between">
                <button type="button" class="btn btn-outline"
                    onclick="document.getElementById('newSurveyModal').style.display='none'">Cancelar</button>
                <button type="submit" class="btn btn-primary">Criar</button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
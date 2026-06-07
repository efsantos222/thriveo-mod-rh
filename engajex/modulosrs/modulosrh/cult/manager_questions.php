require_once 'config/db.php';
requireAuth();
require_once 'includes/header.php';

if ($_SESSION['role'] !== 'manager') {
header("Location: index.php");
exit;
}

$surveyId = $_GET['id'] ?? 0;
// Verify ownership
$stmt = $pdo->prepare("SELECT * FROM surveys WHERE id = ? AND company_id = ?");
$stmt->execute([$surveyId, $_SESSION['company_id']]);
$survey = $stmt->fetch();

if (!$survey) {
echo "Pesquisa não encontrada.";
require_once 'includes/footer.php';
exit;
}

// Add Question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
$text = $_POST['question_text'];
$type = $_POST['question_type']; // text, scale

$stmt = $pdo->prepare("INSERT INTO questions (survey_id, question_text, question_type) VALUES (?, ?, ?)");
$stmt->execute([$surveyId, $text, $type]);
}

// Delete Question
if (isset($_GET['delete_q'])) {
$qId = $_GET['delete_q'];
$pdo->prepare("DELETE FROM questions WHERE id = ? AND survey_id = ?")->execute([$qId, $surveyId]);
header("Location: manager_questions.php?id=$surveyId");
exit;
}

$questions = $pdo->prepare("SELECT * FROM questions WHERE survey_id = ?");
$questions->execute([$surveyId]);
$questions = $questions->fetchAll();
?>

<div class="flex-between mb-4">
    <div>
        <a href="manager_surveys.php" class="btn btn-outline" style="margin-bottom: 1rem;"><i
                class="fas fa-arrow-left"></i> Voltar</a>
        <h2>Perguntas: <?= htmlspecialchars($survey['title']) ?></h2>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <h3>Adicionar Pergunta</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="input-group">
                <label>Enunciado</label>
                <textarea name="question_text" class="form-control" required rows="3"></textarea>
            </div>
            <div class="input-group">
                <label>Tipo de Resposta</label>
                <select name="question_type" class="form-control">
                    <option value="text">Texto Livre</option>
                    <option value="scale">Escala 1-5 (Concordância)</option>
                    <option value="choice">Sim/Não (Binário)</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%">Adicionar</button>
        </form>
    </div>

    <div class="card" style="max-height: 80vh; overflow-y: auto;">
        <h3>Lista de Perguntas</h3>
        <?php if (count($questions) === 0): ?>
            <p class="text-muted">Nenhuma pergunta cadastrada.</p>
        <?php else: ?>
            <ul style="list-style: none; padding: 0;">
                <?php foreach ($questions as $q): ?>
                    <li
                        style="padding: 1rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <span class="badge badge-user"><?= strtoupper($q['question_type']) ?></span>
                            <p style="margin-top: 0.5rem;"><?= htmlspecialchars($q['question_text']) ?></p>
                        </div>
                        <a href="?id=<?= $surveyId ?>&delete_q=<?= $q['id'] ?>" class="text-danger"><i
                                class="fas fa-trash"></i></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
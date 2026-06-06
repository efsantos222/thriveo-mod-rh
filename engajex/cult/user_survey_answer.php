require_once 'config/db.php';
requireAuth();
require_once 'includes/header.php';

$surveyId = $_GET['id'] ?? 0;
// Check valid survey
$stmt = $pdo->prepare("SELECT * FROM surveys WHERE id = ? AND company_id = ?");
$stmt->execute([$surveyId, $_SESSION['company_id']]);
$survey = $stmt->fetch();

if (!$survey) {
echo "Pesquisa inválida.";
require_once 'includes/footer.php';
exit;
}

// Check if already answered
$chk = $pdo->prepare("SELECT id FROM responses WHERE user_id = ? AND survey_id = ?");
$chk->execute([$_SESSION['user_id'], $surveyId]);
if ($chk->fetch()) {
echo "<div class='container card text-center'>
    <h3>Você já respondeu esta pesquisa. Obrigado!</h3><a href='user_surveys.php'
        class='btn btn-primary mt-4'>Voltar</a>
</div>";
require_once 'includes/footer.php';
exit;
}

// Handle Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$answers = $_POST['answers']; // Array of question_id => answer

// Store as JSON
$jsonAnswers = json_encode($answers);

$stmt = $pdo->prepare("INSERT INTO responses (user_id, survey_id, answers) VALUES (?, ?, ?)");
$stmt->execute([$_SESSION['user_id'], $surveyId, $jsonAnswers]);

echo "
<script>window.location = 'user_surveys.php';</script>";
exit;
}

$questions = $pdo->prepare("SELECT * FROM questions WHERE survey_id = ?");
$questions->execute([$surveyId]);
$questions = $questions->fetchAll();
?>

<div class="container" style="max-width: 700px;">
    <div class="mb-4">
        <a href="user_surveys.php" class="text-muted"><i class="fas fa-arrow-left"></i> Voltar</a>
    </div>

    <div class="card">
        <h2 class="mb-4"><?= htmlspecialchars($survey['title']) ?></h2>

        <form method="POST">
            <?php foreach ($questions as $q): ?>
                <div class="input-group" style="margin-bottom: 2rem;">
                    <label
                        style="font-size: 1.1rem; color: var(--text-main); margin-bottom: 1rem;"><?= htmlspecialchars($q['question_text']) ?></label>

                    <?php if ($q['question_type'] === 'text'): ?>
                        <textarea name="answers[<?= $q['id'] ?>]" class="form-control" rows="3" required></textarea>

                    <?php elseif ($q['question_type'] === 'scale'): ?>
                        <div style="display: flex; gap: 1rem; justify-content: space-between; padding: 1rem 0;">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <label style="text-align: center; cursor: pointer;">
                                    <input type="radio" name="answers[<?= $q['id'] ?>]" value="<?= $i ?>" required>
                                    <div style="font-size: 0.9rem; margin-top: 0.5rem;"><?= $i ?></div>
                                </label>
                            <?php endfor; ?>
                        </div>
                        <div class="flex-between text-muted" style="font-size: 0.8rem;">
                            <span>Discordo Totalmente</span>
                            <span>Concordo Totalmente</span>
                        </div>

                    <?php elseif ($q['question_type'] === 'choice'): ?>
                        <select name="answers[<?= $q['id'] ?>]" class="form-control" required>
                            <option value="">Selecione...</option>
                            <option value="Sim">Sim</option>
                            <option value="Não">Não</option>
                        </select>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Enviar
                Respostas</button>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
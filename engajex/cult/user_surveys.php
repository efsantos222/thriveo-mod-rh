require_once 'config/db.php';
requireAuth();
require_once 'includes/header.php';

$companyId = $_SESSION['company_id'];

// Get surveys
$surveys = $pdo->prepare("SELECT * FROM surveys WHERE company_id = ? ORDER BY created_at DESC");
$surveys->execute([$companyId]);
$surveys = $surveys->fetchAll();

// Check which ones are answered
// This is a simple check, ideally we use a proper JOIN
?>

<h2>Pesquisas Disponíveis</h2>
<p style="color: var(--text-muted); margin-bottom: 2rem;">Participe do desenvolvimento da nossa cultura.</p>

<div class="grid-3">
    <?php foreach ($surveys as $survey): ?>
        <?php
        // Check if already answered
        $stmt = $pdo->prepare("SELECT id FROM responses WHERE user_id = ? AND survey_id = ?");
        $stmt->execute([$_SESSION['user_id'], $survey['id']]);
        $answered = $stmt->fetch();
        ?>
        <div class="card">
            <h3 style="font-size: 1.2rem;"><?= htmlspecialchars($survey['title']) ?></h3>
            <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1rem;">
                <?= htmlspecialchars($survey['description']) ?>
            </p>

            <?php if ($answered): ?>
                <button disabled class="btn btn-outline" style="width: 100%; opacity: 0.7;">
                    <i class="fas fa-check"></i> Respondida
                </button>
            <?php else: ?>
                <a href="user_survey_answer.php?id=<?= $survey['id'] ?>" class="btn btn-primary" style="width: 100%;">
                    Responder Agora
                </a>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
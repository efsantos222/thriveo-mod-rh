<?php
$pageTitle = 'Dashboard';
require_once '../config.php';
require_once 'header.php';

$company_id = $_SESSION['company_id'];

// Stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE company_id = ?");
$stmt->execute([$company_id]);
$totalJobs = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM candidates WHERE company_id = ?");
$stmt->execute([$company_id]);
$totalCandidates = $stmt->fetchColumn();

// Recent Jobs
$stmt = $pdo->prepare("SELECT * FROM jobs WHERE company_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$company_id]);
$recentJobs = $stmt->fetchAll();
?>

<div class="glass-card" style="margin-bottom: 2rem;">
    <h3>Visão Geral</h3>
    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
        <div
            style="background: rgba(255,255,255,0.05); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--glass-border);">
            <div style="font-size: 2.5rem; font-weight: 800; color: var(--primary-color);">
                <?= $totalJobs ?>
            </div>
            <div style="color: var(--text-muted);">Vagas Abertas</div>
        </div>
        <div
            style="background: rgba(255,255,255,0.05); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--glass-border);">
            <div style="font-size: 2.5rem; font-weight: 800; color: var(--secondary-color);">
                <?= $totalCandidates ?>
            </div>
            <div style="color: var(--text-muted);">Candidatos Cadastrados</div>
        </div>
    </div>
</div>

<div class="glass-card">
    <h3>Vagas Recentes</h3>
    <div style="margin-top: 1rem;">
        <?php if (count($recentJobs) > 0): ?>
            <ul style="list-style: none;">
                <?php foreach ($recentJobs as $job): ?>
                    <li
                        style="padding: 1rem; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong>
                                <?= htmlspecialchars($job['title']) ?>
                            </strong>
                            <div style="font-size: 0.85rem; color: var(--text-muted);">
                                <?= date('d/m/Y', strtotime($job['created_at'])) ?>
                            </div>
                        </div>
                        <a href="job_details.php?id=<?= $job['id'] ?>" class="btn btn-primary"
                            style="padding: 6px 12px; font-size: 0.85rem;">Ver Detalhes</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p style="color: var(--text-muted);">Nenhuma vaga cadastrada.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>
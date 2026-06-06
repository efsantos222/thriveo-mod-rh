<?php
$pageTitle = 'Dashboard';
require_once '../config.php';
require_once 'header.php';

// Stats
$stmt = $pdo->query("SELECT COUNT(*) FROM entrev_companies");
$totalCompanies = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM entrev_users WHERE role = 'responsible'");
$totalUsers = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM entrev_interview_scripts");
$totalScripts = $stmt->fetchColumn();
?>

<div class="glass-card" style="margin-bottom: 2rem;">
    <h3>Visão Geral</h3>
    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
        <div
            style="background: rgba(255,255,255,0.05); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--glass-border);">
            <div style="font-size: 2.5rem; font-weight: 800; color: var(--primary-color);">
                <?= $totalCompanies ?>
            </div>
            <div style="color: var(--text-muted);">Empresas Cadastradas</div>
        </div>
        <div
            style="background: rgba(255,255,255,0.05); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--glass-border);">
            <div style="font-size: 2.5rem; font-weight: 800; color: var(--secondary-color);">
                <?= $totalUsers ?>
            </div>
            <div style="color: var(--text-muted);">Responsáveis Ativos</div>
        </div>
        <div
            style="background: rgba(255,255,255,0.05); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--glass-border);">
            <div style="font-size: 2.5rem; font-weight: 800; color: #10b981;">
                <?= $totalScripts ?>
            </div>
            <div style="color: var(--text-muted);">Roteiros Gerados</div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
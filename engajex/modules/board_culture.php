<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../config.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$companyId = $_SESSION['company_id'] ?? 1;
$role = $_SESSION['role'];
$isManager = in_array($role, ['admin', 'manager', 'responsible']);

// Fetch Summary Data
// 1. Identity
$stmt = $pdo->prepare("SELECT purpose, mission FROM culture_identity WHERE company_id = ?");
$stmt->execute([$companyId]);
$identity = $stmt->fetch();

// 2. Active Surveys
$sStmt = $pdo->prepare("SELECT count(*) FROM culture_surveys WHERE company_id = ? AND is_active = 1");
$sStmt->execute([$companyId]);
$activeSurveysCount = $sStmt->fetchColumn();

// 3. Last Report
$rStmt = $pdo->prepare("SELECT created_at FROM culture_reports WHERE company_id = ? ORDER BY created_at DESC LIMIT 1");
$rStmt->execute([$companyId]);
$lastReportDate = $rStmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Cultura Organizacional - Sinergy</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .culture-hub-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .hub-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            padding: 2rem;
            border-radius: 1rem;
            transition: transform 0.2s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .hub-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.08);
        }

        .hub-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .hub-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: white;
        }

        .hub-desc {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content">
            <h1>Cultura Organizacional</h1>
            <p style="color:var(--text-muted);">Gerencie a identidade, colete feedbacks e gere insights com IA.</p>

            <div class="culture-hub-grid">

                <!-- IDENTITY -->
                <div class="hub-card" style="border-left: 4px solid #3b82f6;">
                    <div>
                        <div class="hub-icon">🧬</div>
                        <div class="hub-title">Identidade & DNA</div>
                        <div class="hub-desc">
                            <?php if ($identity): ?>
                                <strong>Missão:</strong>
                                <?php echo strlen($identity['mission']) > 50 ? substr($identity['mission'], 0, 50) . '...' : $identity['mission']; ?>
                            <?php else: ?>
                                Defina o Propósito, Missão, Visão e Valores da empresa.
                            <?php endif; ?>
                        </div>
                    </div>
                    <a href="culture_identity.php" class="btn btn-primary" style="background:#2563eb;">Gerenciar
                        Identidade</a>
                </div>

                <!-- SURVEYS -->
                <div class="hub-card" style="border-left: 4px solid #10b981;">
                    <div>
                        <div class="hub-icon">📢</div>
                        <div class="hub-title">Pesquisas Pulse</div>
                        <div class="hub-desc">
                            Colete percepções reais dos colaboradores sobre a cultura.
                            <br><br>
                            <strong><?php echo $activeSurveysCount; ?></strong> Pesquisas Ativas
                        </div>
                    </div>
                    <a href="culture_surveys.php" class="btn btn-primary" style="background:#059669;">
                        <?php echo $isManager ? 'Criar / Gerenciar' : 'Responder Pesquisas'; ?>
                    </a>
                </div>

                <!-- AI REPORTS -->
                <?php if ($isManager): ?>
                    <div class="hub-card" style="border-left: 4px solid #a855f7;">
                        <div>
                            <div class="hub-icon">✨</div>
                            <div class="hub-title">Análise de Cultura AI</div>
                            <div class="hub-desc">
                                Compare a Identidade Declarada com a Percepção Real (Gaps & Ações).
                                <br><br>
                                <?php echo $lastReportDate ? 'Último: ' . date('d/m/Y', strtotime($lastReportDate)) : 'Nenhuma análise ainda.'; ?>
                            </div>
                        </div>
                        <a href="culture_ai.php" class="btn btn-primary"
                            style="background:linear-gradient(135deg, #a855f7, #ec4899); border:none;">Acessar Insights
                            IA</a>
                    </div>
                <?php endif; ?>

            </div>

        </main>
    </div>
</body>

</html>
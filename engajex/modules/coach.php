<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../config.php';
require_once 'coach_ai.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$companyId = $_SESSION['company_id'] ?? 1;
$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];
$isManager = in_array($role, ['admin', 'manager', 'responsible']);

$view = $_GET['view'] ?? 'dashboard';
$message = '';

// --- Get Recent Assessments ---
$stmt = $pdo->prepare("SELECT * FROM coach_assessments WHERE user_id = ? ORDER BY completed_at DESC");
$stmt->execute([$userId]);
$myAssessments = $stmt->fetchAll();

// --- Get Team Assessments (Manager Only) ---
$teamAssessments = [];
if ($isManager) {
    // Fetch assessments from users managed by current user, or all if admin
    $sql = "SELECT a.*, u.name as user_name FROM coach_assessments a 
            JOIN users u ON a.user_id = u.id 
            WHERE u.company_id = ?";
    if ($role === 'manager') {
        $sql .= " AND u.manager_id = ?";
        $params = [$companyId, $userId];
    } else {
        $params = [$companyId];
    }
    $sql .= " ORDER BY a.completed_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $teamAssessments = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Sinergy Coaching System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .coach-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .assessment-card {
            background: rgba(255, 255, 255, 0.05);
            padding: 1.5rem;
            border-radius: 1rem;
            border: 1px solid var(--glass-border);
            transition: transform 0.2s;
        }

        .assessment-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.08);
        }

        .btn-start {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: var(--primary-color);
            color: white;
            border-radius: 0.5rem;
            text-decoration: none;
            margin-top: 1rem;
            font-size: 0.9rem;
        }

        .badge-type {
            background: #3b82f6;
            color: white;
            padding: 0.2rem 0.6rem;
            border-radius: 1rem;
            font-size: 0.7rem;
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 2rem;">
                <h1>Sinergy Coaching</h1>
                <div style="display:flex; gap:10px;">
                    <a href="?view=dashboard" class="btn btn-outline">Dashboard</a>
                    <?php if ($isManager): ?><a href="?view=team" class="btn btn-outline">Equipe</a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($view === 'dashboard'): ?>
                <!-- MAIN NAVIGATION CARDS -->
                <div class="coach-grid" style="margin-bottom: 2rem;">
                    <?php if ($isManager): ?>
                        <div class="assessment-card" style="border-left: 4px solid #f59e0b;">
                            <h3>👥 Meus Mentorados</h3>
                            <p style="color:var(--text-muted); font-size:0.9rem; margin-top:0.5rem; margin-bottom:1rem;">
                                Gerencie sua equipe, visualize perfis e acompanhe o desenvolvimento individual.
                            </p>
                            <a href="coach_mentees.php" class="btn-start" style="background:#d97706;">Ver Equipe</a>
                        </div>
                    <?php endif; ?>

                    <div class="assessment-card" style="border-left: 4px solid #a855f7;">
                        <h3>📅 Sessões de Coaching</h3>
                        <p style="color:var(--text-muted); font-size:0.9rem; margin-top:0.5rem; margin-bottom:1rem;">
                            Agende e gerencie suas reuniões de coaching e acompanhe o histórico.
                        </p>
                        <a href="coach_sessions.php" class="btn-start" style="background:var(--primary-color);">Acessar
                            Agenda</a>
                    </div>

                    <div class="assessment-card" style="border-left: 4px solid #3b82f6;">
                        <h3>🎯 Metas SMART</h3>
                        <p style="color:var(--text-muted); font-size:0.9rem; margin-top:0.5rem; margin-bottom:1rem;">
                            Defina, acompanhe e atualize suas metas e planos de ação.
                        </p>
                        <a href="coach_goals.php" class="btn-start" style="background:#2563eb;">Gerenciar Metas</a>
                    </div>

                    <div class="assessment-card" style="border-left: 4px solid #10b981;">
                        <h3>🔄 Feedback 360°</h3>
                        <p style="color:var(--text-muted); font-size:0.9rem; margin-top:0.5rem; margin-bottom:1rem;">
                            Envie e receba feedbacks estruturados (SBI) para desenvolvimento contínuo.
                        </p>
                        <a href="coach_feedback.php" class="btn-start" style="background:#059669;">Ver Feedbacks</a>
                    </div>
                </div>

                <!-- DIAGNOSTICS -->
                <!-- DIAGNOSTICS & TESTS -->
                <div
                    style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; border-bottom:1px solid var(--glass-border); padding-bottom:0.5rem;">
                    <h3>Ferramentas de Diagnóstico</h3>
                    <?php if ($isManager): ?>
                        <a href="coach_test_builder.php" class="btn btn-outline" style="font-size:0.8rem;">+ Criar/Editar
                            Testes</a>
                    <?php endif; ?>
                </div>

                <div class="coach-grid">
                    <?php
                    // Fetch Available Tests
                    $tStmt = $pdo->prepare("SELECT id, title, description, type FROM coach_test_templates WHERE company_id IS NULL OR company_id = ? ORDER BY created_at DESC");
                    $tStmt->execute([$companyId]);
                    $availableTests = $tStmt->fetchAll();

                    if (count($availableTests) == 0) {
                        echo "<p style='color:var(--text-muted)'>Nenhum teste disponível.</p>";
                    }

                    foreach ($availableTests as $test):
                        ?>
                        <div class="assessment-card">
                            <div style="display:flex; justify-content:space-between;">
                                <h3><?php echo htmlspecialchars($test['title']); ?></h3>
                                <span class="badge-type"><?php echo $test['type']; ?></span>
                            </div>
                            <p
                                style="color:var(--text-muted); font-size:0.9rem; margin-top:0.5rem; height:40px; overflow:hidden;">
                                <?php echo htmlspecialchars($test['description']); ?>
                            </p>
                            <a href="coach_test_runner.php?id=<?php echo $test['id']; ?>" class="btn-start">Iniciar Teste</a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- HISTORY -->
                <h3
                    style="margin-top:3rem; margin-bottom:1rem; border-bottom:1px solid var(--glass-border); padding-bottom:0.5rem;">
                    Meus Resultados</h3>
                <?php if (empty($myAssessments)): ?>
                    <p style="color:var(--text-muted);">Nenhuma avaliação realizada ainda.</p>
                <?php else: ?>
                    <div class="coach-grid">
                        <?php foreach ($myAssessments as $a): ?>
                            <div class="assessment-card" style="border-left: 4px solid #10b981;">
                                <h4>
                                    <?php echo htmlspecialchars($a['type']); ?>
                                </h4>
                                <div style="font-size:0.8rem; color:var(--text-muted); margin-bottom:0.5rem;">Data:
                                    <?php echo date('d/m/Y', strtotime($a['completed_at'])); ?>
                                </div>
                                <div
                                    style="background:rgba(0,0,0,0.3); padding:0.8rem; border-radius:0.5rem; font-size:0.85rem; color:#e2e8f0; max-height:150px; overflow-y:auto;">
                                    <?php echo nl2br(htmlspecialchars($a['ai_analysis'] ?? 'Análise Pendente...')); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($view === 'team' && $isManager): ?>
                <h3>Avaliações da Equipe</h3>
                <div class="coach-grid">
                    <?php foreach ($teamAssessments as $ta): ?>
                        <div class="assessment-card">
                            <div style="font-weight:bold; color:var(--primary-color);">
                                <?php echo htmlspecialchars($ta['user_name']); ?>
                            </div>
                            <div style="display:flex; justify-content:space-between; margin:0.5rem 0;">
                                <span class="badge-type">
                                    <?php echo $ta['type']; ?>
                                </span>
                                <span style="font-size:0.8rem; color:var(--text-muted);">
                                    <?php echo date('d/m/Y', strtotime($ta['completed_at'])); ?>
                                </span>
                            </div>
                            <div
                                style="background:rgba(0,0,0,0.3); padding:0.8rem; border-radius:0.5rem; font-size:0.85rem; color:#e2e8f0; max-height:200px; overflow-y:auto;">
                                <strong>Análise IA:</strong><br>
                                <?php echo nl2br(htmlspecialchars($ta['ai_analysis'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </main>
    </div>
</body>

</html>
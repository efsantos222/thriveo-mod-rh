<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../config.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$companyId = $_SESSION['company_id'] ?? 1;
$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];
$isManager = in_array($role, ['admin', 'manager', 'responsible']);
$view = $_GET['view'] ?? 'my_goals';
$message = '';

// --- ACTIONS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'create_goal') {
        $stmt = $pdo->prepare("INSERT INTO coach_goals (company_id, user_id, title, category, smart_specific, smart_measurable, smart_achievable, smart_relevant, smart_timebound, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $companyId,
            $userId,
            $_POST['title'],
            $_POST['category'],
            $_POST['specific'],
            $_POST['measurable'],
            $_POST['achievable'],
            $_POST['relevant'],
            $_POST['timebound'],
            $_POST['start_date'],
            $_POST['end_date']
        ]);
        $message = "Meta criada com sucesso!";
    }

    if ($_POST['action'] === 'update_status') {
        $stmt = $pdo->prepare("UPDATE coach_goals SET status = ? WHERE id = ? AND (user_id = ? OR EXISTS(SELECT id FROM users WHERE id = coach_goals.user_id AND company_id = ? AND manager_id = ?))"); // Allow owner or manager
        // Complex permission check simplified:
        // For now restricting to owner to update status, or manager logic later. 
        // Let's allow simple owner update.
        $check = $pdo->prepare("SELECT user_id FROM coach_goals WHERE id = ?");
        $check->execute([$_POST['goal_id']]);
        $g = $check->fetch();

        if ($g && ($g['user_id'] == $userId || $isManager)) {
            $pdo->prepare("UPDATE coach_goals SET status = ? WHERE id = ?")->execute([$_POST['status'], $_POST['goal_id']]);
            $message = "Status atualizado.";
        }
    }
}

// --- FETCH DATA ---
if ($view === 'my_goals') {
    $stmt = $pdo->prepare("SELECT * FROM coach_goals WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    $goals = $stmt->fetchAll();
}

if ($view === 'team_goals' && $isManager) {
    if ($role === 'admin') {
        $stmt = $pdo->prepare("SELECT g.*, u.name as user_name FROM coach_goals g JOIN users u ON g.user_id = u.id WHERE g.company_id = ? ORDER BY u.name, g.created_at DESC");
        $stmt->execute([$companyId]);
    } else {
        $stmt = $pdo->prepare("SELECT g.*, u.name as user_name FROM coach_goals g JOIN users u ON g.user_id = u.id WHERE g.company_id = ? AND u.manager_id = ? ORDER BY u.name, g.created_at DESC");
        $stmt->execute([$companyId, $userId]);
    }
    $goals = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Metas SMART - Coaching</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .goal-card {
            background: rgba(255, 255, 255, 0.05);
            padding: 1.5rem;
            border-radius: 1rem;
            border: 1px solid var(--glass-border);
            margin-bottom: 1rem;
        }

        .smart-tag {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            background: rgba(255, 255, 255, 0.1);
            font-size: 0.75rem;
            margin-right: 0.5rem;
            color: #cbd5e1;
        }

        .status-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 1rem;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .status-in_progress {
            background: #3b82f6;
            color: white;
        }

        .status-completed {
            background: #10b981;
            color: white;
        }

        .status-delayed {
            background: #ef4444;
            color: white;
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem;">
                <h1>Metas SMART</h1>
                <a href="coach.php" class="btn btn-outline">
                    < Voltar</a>
            </div>

            <?php if ($message): ?>
                <div
                    style="background:rgba(16,185,129,0.2); color:#34d399; padding:1rem; border-radius:0.5rem; margin-bottom:1rem;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div style="display:flex; gap:1rem; margin-bottom:2rem;">
                <a href="?view=my_goals"
                    class="btn <?php echo $view == 'my_goals' ? 'btn-primary' : 'btn-outline'; ?>">Minhas Metas</a>
                <?php if ($isManager): ?>
                    <a href="?view=team_goals"
                        class="btn <?php echo $view == 'team_goals' ? 'btn-primary' : 'btn-outline'; ?>">Metas da Equipe</a>
                <?php endif; ?>
            </div>

            <?php if ($view === 'my_goals'): ?>
                <button onclick="document.getElementById('new-goal-form').style.display='block'" class="btn btn-primary"
                    style="margin-bottom:2rem;">+ Nova Meta</button>

                <div id="new-goal-form"
                    style="display:none; background:rgba(0,0,0,0.2); padding:1.5rem; border-radius:1rem; margin-bottom:2rem;">
                    <h3>Definir Nova Meta SMART</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="create_goal">
                        <div style="display:grid; gap:1rem;">
                            <input type="text" name="title" required placeholder="Título da Meta (ex: Aumentar Vendas)"
                                style="width:100%; padding:0.8rem; background:#1e293b; border:1px solid var(--glass-border); color:white;">

                            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                                <select name="category"
                                    style="padding:0.8rem; background:#1e293b; border:1px solid var(--glass-border); color:white;">
                                    <option value="professional">Profissional</option>
                                    <option value="personal">Pessoal</option>
                                    <option value="health">Saúde</option>
                                    <option value="financial">Financeiro</option>
                                </select>
                                <div style="display:flex; gap:0.5rem;">
                                    <input type="date" name="start_date" required placeholder="Início"
                                        style="background:#1e293b; border:1px solid var(--glass-border); color:white; padding:0.5rem;">
                                    <input type="date" name="end_date" required placeholder="Fim"
                                        style="background:#1e293b; border:1px solid var(--glass-border); color:white; padding:0.5rem;">
                                </div>
                            </div>

                            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                                <textarea name="specific" placeholder="S - Específica: O que exatamente você quer alcançar?"
                                    rows="2"
                                    style="background:#1e293b; border:1px solid var(--glass-border); color:white; padding:0.5rem;"></textarea>
                                <textarea name="measurable" placeholder="M - Mensurável: Como você saberá que alcançou?"
                                    rows="2"
                                    style="background:#1e293b; border:1px solid var(--glass-border); color:white; padding:0.5rem;"></textarea>
                                <textarea name="achievable" placeholder="A - Atingível: É realista com seus recursos?"
                                    rows="2"
                                    style="background:#1e293b; border:1px solid var(--glass-border); color:white; padding:0.5rem;"></textarea>
                                <textarea name="relevant" placeholder="R - Relevante: Por que isso é importante agora?"
                                    rows="2"
                                    style="background:#1e293b; border:1px solid var(--glass-border); color:white; padding:0.5rem;"></textarea>
                            </div>
                            <textarea name="timebound" placeholder="T - Temporal: Qual o prazo final e marcos?" rows="1"
                                style="background:#1e293b; border:1px solid var(--glass-border); color:white; padding:0.5rem;"></textarea>

                            <button class="btn btn-primary">Salvar Meta</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <div class="goals-list">
                <?php if (empty($goals)): ?>
                    <p style="color:var(--text-muted);">Nenhuma meta encontrada.</p>
                <?php else: ?>
                    <?php foreach ($goals as $g): ?>
                        <div class="goal-card">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                                <div>
                                    <?php if (isset($g['user_name'])): ?>
                                        <div style="font-size:0.8rem; color:#a855f7; margin-bottom:0.2rem;">👤
                                            <?php echo htmlspecialchars($g['user_name']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <h3 style="margin:0;">
                                        <?php echo htmlspecialchars($g['title']); ?>
                                    </h3>
                                    <div style="margin-top:0.5rem;">
                                        <span class="smart-tag">
                                            <?php echo ucfirst($g['category']); ?>
                                        </span>
                                        <span style="font-size:0.8rem; color:var(--text-muted);">
                                            📅
                                            <?php echo date('d/m/Y', strtotime($g['start_date'])); ?> até
                                            <?php echo date('d/m/Y', strtotime($g['end_date'])); ?>
                                        </span>
                                    </div>
                                </div>
                                <form method="POST">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="goal_id" value="<?php echo $g['id']; ?>">
                                    <select name="status" onchange="this.form.submit()"
                                        class="status-badge status-<?php echo $g['status']; ?>"
                                        style="border:none; cursor:pointer;">
                                        <option value="in_progress" <?php echo $g['status'] == 'in_progress' ? 'selected' : ''; ?>>Em
                                            Andamento</option>
                                        <option value="completed" <?php echo $g['status'] == 'completed' ? 'selected' : ''; ?>
                                            >Concluída</option>
                                        <option value="delayed" <?php echo $g['status'] == 'delayed' ? 'selected' : ''; ?>>Atrasada
                                        </option>
                                    </select>
                                </form>
                            </div>

                            <div
                                style="margin-top:1rem; background:rgba(0,0,0,0.2); padding:1rem; border-radius:0.5rem; font-size:0.9rem;">
                                <p><strong>S:</strong>
                                    <?php echo htmlspecialchars($g['smart_specific']); ?>
                                </p>
                                <p><strong>M:</strong>
                                    <?php echo htmlspecialchars($g['smart_measurable']); ?>
                                </p>
                                <p><strong>A:</strong>
                                    <?php echo htmlspecialchars($g['smart_achievable']); ?>
                                </p>
                                <p><strong>R:</strong>
                                    <?php echo htmlspecialchars($g['smart_relevant']); ?>
                                </p>
                                <p><strong>T:</strong>
                                    <?php echo htmlspecialchars($g['smart_timebound']); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </main>
    </div>
</body>

</html>
<?php
require_once '../config.php';
require_once '../includes/flash_toast.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$companyId = $_SESSION['company_id'] ?? null;
$userRole = $_SESSION['role'];
$message = '';
$error = '';

// 1. Handle Sending Feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send') {
    $receiverId = $_POST['receiver_id'];
    $feedbackText = trim($_POST['message']); // Renamed to avoid variable conflict

    if ($receiverId && $feedbackText) {
        try {
            $stmt = $pdo->prepare("INSERT INTO feedbacks (company_id, sender_id, receiver_id, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$companyId, $userId, $receiverId, $feedbackText]);
            $message = "Feedback enviado com sucesso!";
        } catch (PDOException $e) {
            $error = "Erro ao enviar feedback.";
        }
    } else {
        $error = "Selecione um destinatário e escreva uma mensagem.";
    }
}

// 2. Fetch Users for Dropdown (Anyone in the company except self)
$users = [];
$stmt = $pdo->prepare("SELECT id, name, area, role FROM users WHERE company_id = ? AND id != ? ORDER BY name");
$stmt->execute([$companyId, $userId]);
$users = $stmt->fetchAll();

// 3. Fetch Received Feedbacks
$received = [];
$stmt = $pdo->prepare("
    SELECT f.*, u.name as sender_name, u.area as sender_area 
    FROM feedbacks f 
    JOIN users u ON f.sender_id = u.id 
    WHERE f.receiver_id = ? 
    ORDER BY f.created_at DESC
");
$stmt->execute([$userId]);
$received = $stmt->fetchAll();

// 4. Fetch Sent Feedbacks
$sent = [];
$stmt = $pdo->prepare("
    SELECT f.*, u.name as receiver_name 
    FROM feedbacks f 
    JOIN users u ON f.receiver_id = u.id 
    WHERE f.sender_id = ? 
    ORDER BY f.created_at DESC
");
$stmt->execute([$userId]);
$sent = $stmt->fetchAll();

// 5. Manager Functionality: Team Status
$teamStatus = [];
$isManager = ($userRole === 'manager' || $userRole === 'responsible' || $userRole === 'admin');

if ($isManager) {
    // Get direct reports (users who have this user as manager)
    // Note: 'Responsible' usually manages 'Managers', 'Managers' manage 'Employees'.
    // The query depends on the 'manager_id' column in users table.

    $stmt = $pdo->prepare("SELECT id, name, area FROM users WHERE manager_id = ?");
    $stmt->execute([$userId]);
    $myTeam = $stmt->fetchAll();

    foreach ($myTeam as $member) {
        // Find last feedback sent by ME (manager) to this member
        $stmtLast = $pdo->prepare("
            SELECT created_at FROM feedbacks 
            WHERE sender_id = ? AND receiver_id = ? 
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmtLast->execute([$userId, $member['id']]);
        $lastFeedbackDate = $stmtLast->fetchColumn();

        $daysWithout = 'N/A';
        $statusColor = 'text-muted';

        if ($lastFeedbackDate) {
            $date1 = new DateTime($lastFeedbackDate);
            $date2 = new DateTime();
            $interval = $date1->diff($date2);
            $daysWithout = $interval->days;

            if ($daysWithout > 30)
                $statusColor = '#f87171'; // Red > 30 days
            elseif ($daysWithout > 15)
                $statusColor = '#fbbf24'; // Yellow > 15 days
            else
                $statusColor = '#34d399'; // Green
        } else {
            $daysWithout = 'Nunca';
            $statusColor = '#f87171'; // Red (Never)
        }

        $teamStatus[] = [
            'name' => $member['name'],
            'area' => $member['area'],
            'days' => $daysWithout,
            'color' => $statusColor,
            'last_date' => $lastFeedbackDate
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Feedback - TestProf Engaja</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .app-layout {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }


        .main-content {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
            background: var(--bg-color);
        }

        .tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--glass-border);
        }

        .tab-btn {
            padding: 0.75rem 1.5rem;
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-weight: 600;
            position: relative;
        }

        .tab-btn.active {
            color: var(--primary-color);
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--primary-color);
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .feedback-item {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
        }

        .feedback-item:hover {
            border-color: var(--glass-border);
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }

        .team-card {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 0.5rem;
            padding: 1rem;
            border: 1px solid var(--glass-border);
            text-align: center;
        }

        .days-badge {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0.5rem 0;
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include '../includes/sidebar.php'; ?>

        <main class="main-content">
            <h1 style="margin-bottom: 2rem;">Feedback 360°</h1>

            <?php if ($message): flashToast($message, 'success'); endif; ?>
            <?php if ($error):   flashToast($error,   'error');   endif; ?>

            <div class="tabs">
                <button class="tab-btn active" onclick="switchTab('received')">Recebidos</button>
                <button class="tab-btn" onclick="switchTab('sent')">Enviados</button>
                <button class="tab-btn" onclick="switchTab('new')">Novo Feedback</button>
                <?php if ($isManager): ?>
                    <button class="tab-btn" onclick="switchTab('team')">Minha Equipe (Gestão)</button>
                <?php endif; ?>
            </div>

            <!-- Tab: Received -->
            <div id="tab-received">
                <?php if (empty($received)): ?>
                    <p style="color: var(--text-muted);">Você ainda não recebeu feedbacks.</p>
                <?php else: ?>
                    <?php foreach ($received as $f): ?>
                        <div class="feedback-item">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span
                                    style="font-weight: 600; color: #fff;"><?php echo htmlspecialchars($f['sender_name']); ?></span>
                                <span
                                    style="font-size: 0.8rem; color: var(--text-muted);"><?php echo date('d/m/Y', strtotime($f['created_at'])); ?></span>
                            </div>
                            <p style="color: var(--text-light); white-space: pre-wrap;">
                                <?php echo htmlspecialchars($f['message']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Tab: Sent -->
            <div id="tab-sent" style="display: none;">
                <?php if (empty($sent)): ?>
                    <p style="color: var(--text-muted);">Você ainda não enviou feedbacks.</p>
                <?php else: ?>
                    <?php foreach ($sent as $f): ?>
                        <div class="feedback-item">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span style="font-weight: 600; color: var(--text-muted);">Enviado para:
                                    <?php echo htmlspecialchars($f['receiver_name']); ?></span>
                                <span
                                    style="font-size: 0.8rem; color: var(--text-muted);"><?php echo date('d/m/Y', strtotime($f['created_at'])); ?></span>
                            </div>
                            <p style="color: var(--text-light); white-space: pre-wrap;">
                                <?php echo htmlspecialchars($f['message']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Tab: New Feedback -->
            <div id="tab-new" style="display: none;">
                <div class="card" style="max-width: 600px;">
                    <form method="POST">
                        <input type="hidden" name="action" value="send">

                        <div class="form-group">
                            <label class="form-label">Para quem?</label>
                            <select name="receiver_id" class="form-control" required>
                                <option value="">Selecione um colega...</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?php echo $u['id']; ?>">
                                        <?php echo htmlspecialchars($u['name']) . ' (' . htmlspecialchars($u['area']) . ')'; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Feedback</label>
                            <textarea name="message" class="form-control" rows="6"
                                placeholder="Escreva seu feedback construtivo aqui..." required></textarea>
                            <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.5rem;">Seja específico
                                e construtivo. O feedback é um presente.</p>
                        </div>

                        <button type="submit" class="btn btn-primary">Enviar Feedback</button>
                    </form>
                </div>
            </div>

            <!-- Tab: Team Management -->
            <?php if ($isManager): ?>
                <div id="tab-team" style="display: none;">
                    <p style="margin-bottom: 1.5rem; color: var(--text-muted);">
                        Acompanhe há quanto tempo você (Gestor) não envia feedback para seus liderados diretos.
                    </p>

                    <?php if (empty($teamStatus)): ?>
                        <p style="color: var(--text-muted);">Você não possui liderados diretos alocados.</p>
                    <?php else: ?>
                        <div class="team-grid">
                            <?php foreach ($teamStatus as $member): ?>
                                <div class="team-card">
                                    <div style="font-weight: 600; margin-bottom: 0.2rem;">
                                        <?php echo htmlspecialchars($member['name']); ?></div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);">
                                        <?php echo htmlspecialchars($member['area']); ?></div>

                                    <div
                                        style="margin: 1rem 0; border-top: 1px solid var(--glass-border); border-bottom: 1px solid var(--glass-border); padding: 0.5rem 0;">
                                        <div style="font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted);">Sem
                                            feedback há</div>
                                        <div class="days-badge" style="color: <?php echo $member['color']; ?>;">
                                            <?php echo $member['days'] === 'Nunca' ? 'NUNCA' : $member['days'] . ' dias'; ?>
                                        </div>
                                    </div>

                                    <?php if ($member['last_date']): ?>
                                        <div style="font-size: 0.7rem; color: var(--text-muted);">Último:
                                            <?php echo date('d/m/y', strtotime($member['last_date'])); ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </main>
    </div>

    <script>
        function switchTab(tabName) {
            // Hide all tabs
            ['received', 'sent', 'new', 'team'].forEach(t => {
                const el = document.getElementById('tab-' + t);
                if (el) el.style.display = 'none';
            });

            // Show selected
            const target = document.getElementById('tab-' + tabName);
            if (target) target.style.display = 'block';

            // Active button state
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
        }
    </script>
</body>

</html>
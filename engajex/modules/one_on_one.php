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

// Determine view mode
$isManager = ($userRole === 'manager' || $userRole === 'responsible' || $userRole === 'admin');

// 1. Handle Scheduling (Manager Only)
if ($isManager && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'schedule') {
    $employeeId = $_POST['employee_id'];
    $date = $_POST['date']; // Y-m-d
    $time = $_POST['time']; // H:i
    $topic = $_POST['topic'];
    $notes = $_POST['notes'];

    $dateTime = $date . ' ' . $time . ':00';

    if ($employeeId && $date && $time) {
        try {
            $stmt = $pdo->prepare("INSERT INTO one_on_ones (company_id, manager_id, employee_id, scheduled_at, topic, notes, status) VALUES (?, ?, ?, ?, ?, ?, 'agendada')");
            $stmt->execute([$companyId, $userId, $employeeId, $dateTime, $topic, $notes]);
            $message = "Reunião agendada com sucesso!";
        } catch (PDOException $e) {
            $error = "Erro ao agendar.";
        }
    }
}

// 2. Handle Status Update (e.g., Mark as Done)
if ($isManager && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $meetingId = $_POST['meeting_id'];
    $newStatus = $_POST['status'];
    // Optional: Update notes upon completion
    $finalNotes = $_POST['final_notes'] ?? null;

    if ($meetingId) {
        $sql = "UPDATE one_on_ones SET status = ?";
        $params = [$newStatus];

        if ($finalNotes) {
            $sql .= ", notes = ?";
            $params[] = $finalNotes;
        }

        $sql .= " WHERE id = ? AND manager_id = ?";
        $params[] = $meetingId;
        $params[] = $userId;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $message = "Status atualizado.";
    }
}

// 3. Fetch Data

// 3a. My Meetings (As Employee)
$myMeetings = [];
if (!$isManager || $userRole === 'responsible') { // Responsible can see both usually, but let's show receipt view too? Or strictly separate.
    // For simplicity, let's query where I am the employee
    $stmt = $pdo->prepare("
        SELECT o.*, u.name as other_party 
        FROM one_on_ones o 
        JOIN users u ON o.manager_id = u.id 
        WHERE o.employee_id = ? 
        ORDER BY o.scheduled_at DESC
    ");
    $stmt->execute([$userId]);
    $myMeetings = $stmt->fetchAll();
}

// 3b. Managed Meetings (As Manager)
$managedMeetings = [];
$myTeam = [];
if ($isManager) {
    // 1. Get managed meetings
    $stmt = $pdo->prepare("
        SELECT o.*, u.name as other_party 
        FROM one_on_ones o 
        JOIN users u ON o.employee_id = u.id 
        WHERE o.manager_id = ? 
        ORDER BY o.scheduled_at DESC
    ");
    $stmt->execute([$userId]);
    $managedMeetings = $stmt->fetchAll();

    // 2. Get Team for Dropdown
    $stmt = $pdo->prepare("SELECT id, name FROM users WHERE manager_id = ?");
    $stmt->execute([$userId]);
    $myTeam = $stmt->fetchAll();
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>1:1 Meetings - TestProf Engaja</title>
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

        .meeting-card {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .meeting-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
        }

        .meeting-date {
            font-size: 1.1rem;
            font-weight: 700;
            color: #fff;
        }

        .meeting-with {
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .status-badge {
            padding: 0.2rem 0.6rem;
            border-radius: 4px;
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 600;
        }

        .status-agendada {
            background: rgba(59, 130, 246, 0.2);
            color: #93c5fd;
        }

        .status-realizada {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
        }

        .status-cancelada {
            background: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
        }

        .topic-pill {
            background: rgba(255, 255, 255, 0.1);
            padding: 0.1rem 0.5rem;
            border-radius: 10px;
            font-size: 0.8rem;
            margin-right: 0.5rem;
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include '../includes/sidebar.php'; ?>

        <main class="main-content">
            <h1 style="margin-bottom: 2rem;">Reuniões 1:1</h1>

            <?php if ($message): flashToast($message, 'success'); endif; ?>

            <div
                style="display: grid; grid-template-columns: <?php echo $isManager ? '1fr 1fr' : '1fr'; ?>; gap: 2rem;">

                <!-- Left Column: Scheduled List -->
                <div>
                    <h3 style="margin-bottom: 1rem;">Minha Agenda</h3>

                    <?php
                    $meetingsToShow = $isManager ? $managedMeetings : $myMeetings;
                    if (empty($meetingsToShow)): ?>
                        <p style="color: var(--text-muted);">Nenhuma reunião agendada.</p>
                    <?php else: ?>
                        <?php foreach ($meetingsToShow as $m): ?>
                            <div class="meeting-card">
                                <div class="meeting-header">
                                    <div>
                                        <div class="meeting-date">
                                            <?php echo date('d/m \à\s H:i', strtotime($m['scheduled_at'])); ?>
                                        </div>
                                        <div class="meeting-with">
                                            com <?php echo htmlspecialchars($m['other_party']); ?>
                                        </div>
                                    </div>
                                    <span
                                        class="status-badge status-<?php echo $m['status']; ?>"><?php echo ucfirst($m['status']); ?></span>
                                </div>

                                <div style="margin-top: 0.5rem;">
                                    <span class="topic-pill"><?php echo ucfirst($m['topic']); ?></span>
                                </div>

                                <?php if ($m['notes']): ?>
                                    <p
                                        style="margin-top: 0.5rem; font-size: 0.9rem; color: var(--text-light); background: rgba(0,0,0,0.2); padding: 0.5rem; border-radius: 0.5rem;">
                                        <?php echo nl2br(htmlspecialchars($m['notes'])); ?>
                                    </p>
                                <?php endif; ?>

                                <!-- Manager Actions -->
                                <?php if ($isManager && $m['status'] === 'agendada'): ?>
                                    <div
                                        style="margin-top: 1rem; display: flex; gap: 0.5rem; border-top: 1px solid var(--glass-border); padding-top: 0.5rem;">
                                        <form method="POST" style="flex: 1;">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="meeting_id" value="<?php echo $m['id']; ?>">
                                            <input type="hidden" name="status" value="realizada">
                                            <button type="submit" class="btn btn-primary"
                                                style="width: 100%; font-size: 0.8rem; padding: 0.4rem;">Concluir</button>
                                        </form>
                                        <form method="POST" style="flex: 1;">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="meeting_id" value="<?php echo $m['id']; ?>">
                                            <input type="hidden" name="status" value="cancelada">
                                            <button type="submit" class="btn btn-outline"
                                                style="width: 100%; font-size: 0.8rem; padding: 0.4rem; color: #fca5a5; border-color: #fca5a5;">Cancelar</button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Right Column: Scheduler (Manager Only) -->
                <?php if ($isManager): ?>
                    <div>
                        <div class="card"
                            style="position: sticky; top: 1rem; background: var(--card-bg); border: 1px solid var(--glass-border); border-radius: 1rem; padding: 1.5rem;">
                            <h3 style="margin-bottom: 1rem;">Agendar Nova 1:1</h3>

                            <?php if (empty($myTeam)): ?>
                                <p style="color: var(--text-muted);">Você não possui liderados alocados para agendar.</p>
                                <a href="../allocation.php" style="color: var(--primary-color); font-size: 0.9rem;">Ir para
                                    Alocação →</a>
                            <?php else: ?>
                                <form method="POST">
                                    <input type="hidden" name="action" value="schedule">

                                    <div class="form-group">
                                        <label class="form-label">Colaborador</label>
                                        <select name="employee_id" class="form-control" required>
                                            <option value="">Selecione...</option>
                                            <?php foreach ($myTeam as $emp): ?>
                                                <option value="<?php echo $emp['id']; ?>">
                                                    <?php echo htmlspecialchars($emp['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                        <div class="form-group">
                                            <label class="form-label">Data</label>
                                            <input type="date" name="date" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Hora</label>
                                            <input type="time" name="time" class="form-control" required>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Tópico Principal</label>
                                        <select name="topic" class="form-control" required>
                                            <option value="feedback">Feedback</option>
                                            <option value="avaliacao">Avaliação</option>
                                            <option value="mentoria">Mentoria</option>
                                            <option value="orientacao">Orientação</option>
                                            <option value="outro">Outro</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Pauta / Observações Iniciais</label>
                                        <textarea name="notes" class="form-control" rows="3"
                                            placeholder="O que será discutido?"></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-primary" style="width: 100%;">Agendar Reunião</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </main>
    </div>
</body>

</html>
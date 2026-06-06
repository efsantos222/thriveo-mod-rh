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
$view = $_GET['view'] ?? 'upcoming';
$message = '';

// --- ACTIONS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'schedule_session' && $isManager) {
        $stmt = $pdo->prepare("INSERT INTO coach_sessions (company_id, coach_id, coachee_id, session_date, location, notes, status) VALUES (?, ?, ?, ?, ?, ?, 'scheduled')");
        $stmt->execute([
            $companyId,
            $userId, // Coach is current user (manager)
            $_POST['coachee_id'],
            $_POST['session_date'] . ' ' . $_POST['session_time'],
            $_POST['location'],
            $_POST['notes']
        ]);
        $message = "Sessão agendada!";
    }

    if ($_POST['action'] === 'update_status') {
        // Allow coach to update status
        // Ensure the session belongs to this coach
        $stmt = $pdo->prepare("UPDATE coach_sessions SET status = ?, notes = IF(? != '', ?, notes) WHERE id = ? AND coach_id = ?");
        $stmt->execute([$_POST['status'], $_POST['notes'], $_POST['notes'], $_POST['session_id'], $userId]);
        $message = "Sessão atualizada.";
    }
}

// --- FETCH DATA ---
$sql = "SELECT s.*, c.name as coach_name, co.name as coachee_name 
        FROM coach_sessions s 
        LEFT JOIN users c ON s.coach_id = c.id 
        LEFT JOIN users co ON s.coachee_id = co.id 
        WHERE s.company_id = ?";
$params = [$companyId];

if ($role === 'manager' || $role === 'responsible') {
    // Manager sees sessions where they are coach OR sessions of their subordinates (if another coach? Rare in this model)
    // Simplified: Manager only sees sessions they created (as Coach) OR where they are Coachee (if they have a boss)
    // Let's stick to: Manager = Coach role.
    $sql .= " AND (s.coach_id = ? OR s.coachee_id = ?)";
    $params[] = $userId;
    $params[] = $userId;
} elseif ($role !== 'admin') {
    // Employee only sees where they are Coachee
    $sql .= " AND s.coachee_id = ?";
    $params[] = $userId;
}

$sql .= " ORDER BY s.session_date " . ($view === 'upcoming' ? 'ASC' : 'DESC');
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$allSessions = $stmt->fetchAll();

// Filter logic in PHP for simplicity between "Upcoming" and "History"
$sessions = array_filter($allSessions, function ($s) use ($view) {
    $isPast = strtotime($s['session_date']) < time();
    $isCompleted = $s['status'] !== 'scheduled';
    if ($view === 'upcoming')
        return !$isPast && !$isCompleted;
    return $isPast || $isCompleted;
});

// Users for Dropdown (Team members)
$teamMembers = [];
if ($isManager) {
    if ($role === 'admin') {
        $stmt = $pdo->prepare("SELECT id, name FROM users WHERE company_id = ? AND id != ? ORDER BY name");
        $stmt->execute([$companyId, $userId]);
    } else {
        $stmt = $pdo->prepare("SELECT id, name FROM users WHERE company_id = ? AND manager_id = ? ORDER BY name");
        $stmt->execute([$companyId, $userId]);
    }
    $teamMembers = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Sessões de Coaching</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .session-card {
            background: rgba(255, 255, 255, 0.05);
            padding: 1.5rem;
            border-radius: 1rem;
            border: 1px solid var(--glass-border);
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .date-box {
            background: rgba(0, 0, 0, 0.3);
            padding: 1rem;
            border-radius: 0.5rem;
            text-align: center;
            min-width: 80px;
            margin-right: 1.5rem;
        }

        .date-day {
            font-size: 1.5rem;
            font-weight: bold;
            display: block;
        }

        .date-month {
            font-size: 0.9rem;
            color: var(--primary-color);
            text-transform: uppercase;
        }

        .status-scheduled {
            color: #3b82f6;
        }

        .status-completed {
            color: #10b981;
        }

        .status-canceled {
            color: #ef4444;
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h1>Sessões de Coaching</h1>
                <a href="coach.php" class="btn btn-outline">
                    < Voltar</a>
            </div>

            <?php if ($message): ?>
                <div style="margin:1rem 0; color:#34d399;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div style="margin-bottom:2rem; display:flex; gap:1rem;">
                <a href="?view=upcoming"
                    class="btn <?php echo $view == 'upcoming' ? 'btn-primary' : 'btn-outline'; ?>">Próximas</a>
                <a href="?view=history"
                    class="btn <?php echo $view == 'history' ? 'btn-primary' : 'btn-outline'; ?>">Histórico</a>
                <?php if ($isManager): ?>
                    <button onclick="document.getElementById('schedule-form').style.display='block'" class="btn btn-primary"
                        style="margin-left:auto;">+ Agendar Sessão</button>
                <?php endif; ?>
            </div>

            <?php if ($isManager): ?>
                <div id="schedule-form"
                    style="display:none; background:rgba(0,0,0,0.2); padding:1.5rem; border-radius:1rem; margin-bottom:2rem; border:1px solid var(--glass-border);">
                    <h3>Agendar Nova Sessão</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="schedule_session">
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                            <div>
                                <label>Coachee (Mentorado)</label>
                                <select name="coachee_id" required
                                    style="width:100%; padding:0.8rem; background:#1e293b; border:1px solid var(--glass-border); color:white;">
                                    <option value="">Selecione...</option>
                                    <?php foreach ($teamMembers as $m): ?>
                                        <option value="<?php echo $m['id']; ?>">
                                            <?php echo htmlspecialchars($m['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label>Local / Link</label>
                                <input type="text" name="location" placeholder="Sala 1 ou Google Meet..."
                                    style="width:100%; padding:0.8rem; background:#1e293b; border:1px solid var(--glass-border); color:white;">
                            </div>
                            <div>
                                <label>Data</label>
                                <input type="date" name="session_date" required
                                    style="width:100%; padding:0.8rem; background:#1e293b; border:1px solid var(--glass-border); color:white;">
                            </div>
                            <div>
                                <label>Hora</label>
                                <input type="time" name="session_time" required
                                    style="width:100%; padding:0.8rem; background:#1e293b; border:1px solid var(--glass-border); color:white;">
                            </div>
                        </div>
                        <div style="margin-top:1rem;">
                            <label>Pauta / Notas Iniciais</label>
                            <textarea name="notes" rows="2"
                                style="width:100%; padding:0.8rem; background:#1e293b; border:1px solid var(--glass-border); color:white;"></textarea>
                        </div>
                        <button class="btn btn-primary" style="margin-top:1rem;">Confirmar Agendamento</button>
                    </form>
                </div>
            <?php endif; ?>

            <div class="sessions-list">
                <?php if (empty($sessions)): ?>
                    <p style="color:var(--text-muted);">Nenhuma sessão encontrada.</p>
                <?php else: ?>
                    <?php foreach ($sessions as $s): ?>
                        <div class="session-card">
                            <div style="display:flex; align-items:center;">
                                <div class="date-box">
                                    <span class="date-day">
                                        <?php echo date('d', strtotime($s['session_date'])); ?>
                                    </span>
                                    <span class="date-month">
                                        <?php echo date('M', strtotime($s['session_date'])); ?>
                                    </span>
                                </div>
                                <div>
                                    <h3 style="margin:0;">
                                        <?php echo $role === 'manager' || $role === 'admin' ? $s['coachee_name'] : 'Sessão com Coach'; ?>
                                    </h3>
                                    <div style="color:var(--text-muted); font-size:0.9rem; margin-top:0.3rem;">
                                        clock
                                        <?php echo date('H:i', strtotime($s['session_date'])); ?> |
                                        pin
                                        <?php echo htmlspecialchars($s['location']); ?>
                                    </div>
                                    <?php if ($s['status'] !== 'scheduled'): ?>
                                        <div style="font-size:0.8rem; margin-top:0.3rem;"
                                            class="status-<?php echo $s['status']; ?>">Status:
                                            <?php echo ucfirst($s['status']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if ($isManager && $s['coach_id'] == $userId): ?>
                                <div style="text-align:right;">
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="session_id" value="<?php echo $s['id']; ?>">

                                        <?php if ($s['status'] === 'scheduled'): ?>
                                            <button name="status" value="completed" class="btn"
                                                style="background:#10b981; color:white; padding:0.5rem 1rem;">Concluir</button>
                                            <button name="status" value="canceled" class="btn"
                                                style="background:#ef4444; color:white; padding:0.5rem 1rem;">Cancelar</button>
                                        <?php endif; ?>

                                        <details style="margin-top:0.5rem; text-align:right;">
                                            <summary style="cursor:pointer; color:var(--text-muted); font-size:0.8rem;">Adicionar
                                                Notas</summary>
                                            <textarea name="notes" placeholder="Descreva o que foi discutido..."
                                                style="margin-top:0.5rem; background:#1e293b; color:white; width:250px;"><?php echo htmlspecialchars($s['notes']); ?></textarea>
                                            <button type="submit" name="status" value="<?php echo $s['status']; ?>"
                                                style="display:block; margin-left:auto; margin-top:0.2rem; cursor:pointer;">Salvar
                                                Notas</button>
                                        </details>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </main>
    </div>
</body>

</html>
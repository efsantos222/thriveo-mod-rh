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

// Only Managers/Responsible/Admin can view this
// In this system, "Responsible" creates users.
if (!in_array($role, ['admin', 'manager', 'responsible'])) {
    die("Acesso negado.");
}

$search = $_GET['search'] ?? '';

// Fetch Mentees (Direct Reports)
// We assume 'manager_id' in users table defines the relationship.
$sql = "SELECT * FROM users WHERE company_id = ? AND manager_id = ?";
$params = [$companyId, $userId];

if ($role === 'admin') {
    // Admin sees all? Or just their direct? Using 'manager_id' logic implies hierarchy. 
    // If Admin wants to see all, they manage 'Companies'.
    // Let's stick to manager_id for 'Meus Mentorados'.
    // If no manager_id set for anyone, this might be empty.
    // Allow Admin to see ALL company users for demo if needed.
    // The user request says "Meus Mentorados" (My Mentees), implies direct relationship.
    // BUT the image shows "Meus Mentorados" + "Novo Usuário", implying management.
}

if ($search) {
    $sql .= " AND (name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY name";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$mentees = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Meus Mentorados - Coaching</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .mentee-card {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 0.5rem;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .mentee-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .avatar-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .search-bar {
            width: 100%;
            padding: 0.8rem;
            border-radius: 0.5rem;
            border: 1px solid var(--glass-border);
            background: rgba(255, 255, 255, 0.05);
            color: white;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem;">
                <h1>Meus Mentorados</h1>
                <!-- Link to create user? Assuming manage_employees.php or admin/users.php logic -->
                <a href="../manage_employees.php?action=new" class="btn btn-primary">+ Novo Usuário</a>
            </div>

            <form method="GET">
                <input type="text" name="search" class="search-bar" placeholder="Buscar usuário..."
                    value="<?php echo htmlspecialchars($search); ?>">
            </form>

            <div
                style="background: rgba(255,255,255,0.02); padding: 1.5rem; border-radius: 1rem; border: 1px solid var(--glass-border);">
                <?php if (empty($mentees)): ?>
                    <p style="color:var(--text-muted);">Nenhum usuário encontrado.</p>
                    <?php if ($role === 'responsible' || $role === 'admin'): ?>
                        <p style="font-size:0.9rem; color:var(--text-muted);">Certifique-se de que os usuários estajam
                            cadastrados e você definido como Gestor deles.</p>
                    <?php endif; ?>
                <?php else: ?>
                    <?php foreach ($mentees as $m): ?>
                        <div class="mentee-card">
                            <div class="mentee-info">
                                <div class="avatar-circle">
                                    <?php echo strtoupper(substr($m['name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div style="font-weight:bold;">
                                        <?php echo htmlspecialchars($m['name']); ?>
                                    </div>
                                    <div style="font-size:0.8rem; color:var(--text-muted);">
                                        <?php echo htmlspecialchars($m['email']); ?>
                                    </div>
                                    <div style="font-size:0.8rem; color:var(--text-muted);">
                                        <?php echo htmlspecialchars($m['area'] ?? 'Sem Área'); ?>
                                    </div>
                                </div>
                            </div>
                            <div style="display:flex; gap:0.5rem;">
                                <a href="coach_sessions.php?coachee_id=<?php echo $m['id']; ?>" class="btn btn-outline"
                                    style="font-size:0.8rem;">Agendar Sessão</a>
                                <a href="coach_goals.php?view=team_goals&user_id=<?php echo $m['id']; ?>"
                                    class="btn btn-outline" style="font-size:0.8rem;">Ver Metas</a>
                                <!-- Link to general feedback or specific coach feedback -->
                                <a href="coach_feedback.php?receiver_id=<?php echo $m['id']; ?>" class="btn btn-outline"
                                    style="font-size:0.8rem;">Dar Feedback</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </main>
    </div>
</body>

</html>
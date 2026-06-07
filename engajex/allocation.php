<?php
require_once 'config.php';

// Check permissions
if (!isLoggedIn() || !in_array($_SESSION['role'], ['responsible', 'company_admin', 'admin'])) {
    header("Location: login.php");
    exit;
}

$companyId = null;
if ($_SESSION['role'] === 'responsible' || $_SESSION['role'] === 'company_admin') {
    $currentUserId = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT company_id FROM users WHERE id = ?");
    $stmt->execute([$currentUserId]);
    $companyId = $stmt->fetchColumn();
} else {
    $companyId = $_SESSION['company_id'] ?? null;
}

$message = '';
$error = '';

// Handle Allocation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $managerId = $_POST['manager_id'];
    $employeeIds = $_POST['employee_ids'] ?? [];

    if ($managerId && !empty($employeeIds)) {
        try {
            $pdo->beginTransaction();

            // Prepare statement
            $stmt = $pdo->prepare("UPDATE users SET manager_id = ? WHERE id = ? AND company_id = ?");

            foreach ($employeeIds as $empId) {
                $stmt->execute([$managerId, $empId, $companyId]);
            }

            $pdo->commit();
            $message = "Alocação realizada com sucesso! " . count($employeeIds) . " colaboradores vinculados.";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Erro ao alocar: " . $e->getMessage();
        }
    } else {
        $error = "Selecione um gestor e pelo menos um colaborador.";
    }
}

// Fetch Managers
$managers = [];
if ($companyId) {
    $stmt = $pdo->prepare("SELECT id, name, area FROM users WHERE company_id = ? AND role = 'manager' ORDER BY name");
    $stmt->execute([$companyId]);
    $managers = $stmt->fetchAll();
}

// Fetch Employees (Show current manager info if exists)
$employees = [];
if ($companyId) {
    // Left Join to get manager name if already allocated
    $sql = "SELECT e.id, e.name, e.area, e.manager_id, m.name as manager_name 
            FROM users e 
            LEFT JOIN users m ON e.manager_id = m.id 
            WHERE e.company_id = ? AND e.role = 'employee' 
            ORDER BY e.name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$companyId]);
    $employees = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Alocação de Times - TestProf Engaja</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
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

        .allocation-grid {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
            height: calc(100vh - 200px);
        }

        .panel {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 1rem;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .panel-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--glass-border);
            background: rgba(0, 0, 0, 0.2);
        }

        .panel-body {
            padding: 1rem;
            overflow-y: auto;
            flex: 1;
        }

        .user-item {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            border-bottom: 1px solid var(--glass-border);
            gap: 0.75rem;
        }

        .user-item:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .user-item:last-child {
            border-bottom: none;
        }

        .checkbox-custom {
            width: 1.25rem;
            height: 1.25rem;
            border-radius: 0.25rem;
            border: 1px solid var(--text-muted);
            display: grid;
            place-items: center;
            cursor: pointer;
        }

        input[type="checkbox"]:checked+.checkbox-custom {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .badge {
            font-size: 0.7rem;
            padding: 0.1rem 0.4rem;
            border-radius: 4px;
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-muted);
            margin-left: auto;
        }

        .badge-manager {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <h1 style="margin-bottom: 2rem;">Alocação de Times</h1>

            <?php if ($message): ?>
                <div
                    style="background: rgba(16, 185, 129, 0.2); color: #34d399; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div
                    style="background: rgba(239, 68, 68, 0.2); color: #fca5a5; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="allocation-grid">
                <!-- Managers Panel (Selection) -->
                <div class="panel">
                    <div class="panel-header">
                        <h3>1. Selecione o Gestor</h3>
                        <p style="font-size: 0.8rem; color: var(--text-muted);">Quem receberá a equipe?</p>
                    </div>
                    <div class="panel-body">
                        <?php if (empty($managers)): ?>
                            <p style="color: var(--text-muted); text-align: center;">Nenhum gestor cadastrado.</p>
                        <?php else: ?>
                            <?php foreach ($managers as $mgr): ?>
                                <label class="user-item" style="cursor: pointer;">
                                    <input type="radio" name="manager_id" value="<?php echo $mgr['id']; ?>" required
                                        style="margin-right: 0.5rem;">
                                    <div>
                                        <div style="font-weight: 500;"><?php echo htmlspecialchars($mgr['name']); ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted);">
                                            <?php echo htmlspecialchars($mgr['area']); ?>
                                        </div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Employees Panel (List) -->
                <div class="panel">
                    <div class="panel-header"
                        style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h3>2. Selecione os Colaboradores</h3>
                            <p style="font-size: 0.8rem; color: var(--text-muted);">Marque os membros da equipe deste
                                gestor.</p>
                        </div>
                        <button type="submit" class="btn btn-primary">Salvar Alocação</button>
                    </div>
                    <div class="panel-body">
                        <?php if (empty($employees)): ?>
                            <p style="color: var(--text-muted); text-align: center;">Nenhum colaborador cadastrado.</p>
                        <?php else: ?>
                            <div
                                style="margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--glass-border); font-size: 0.9rem; color: var(--text-muted); display: grid; grid-template-columns: 40px 1fr 1fr 1fr; padding-left: 0.75rem;">
                                <span>All</span>
                                <span>Nome</span>
                                <span>Área</span>
                                <span>Gestor Atual</span>
                            </div>

                            <?php foreach ($employees as $emp): ?>
                                <div class="user-item" style="display: grid; grid-template-columns: 40px 1fr 1fr 1fr; gap: 0;">
                                    <div>
                                        <input type="checkbox" name="employee_ids[]" value="<?php echo $emp['id']; ?>">
                                    </div>
                                    <div style="font-weight: 500;"><?php echo htmlspecialchars($emp['name']); ?></div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);">
                                        <?php echo htmlspecialchars($emp['area']); ?>
                                    </div>
                                    <div>
                                        <?php if ($emp['manager_id']): ?>
                                            <span
                                                class="badge badge-manager"><?php echo htmlspecialchars($emp['manager_name']); ?></span>
                                        <?php else: ?>
                                            <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #fca5a5;">Sem
                                                gestor</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </form>

        </main>
    </div>
</body>

</html>
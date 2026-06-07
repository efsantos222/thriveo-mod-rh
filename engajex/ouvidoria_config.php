<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];
$companyId = $_SESSION['company_id'] ?? null;

// Only 'responsible' (or admin) can configure this
if (!in_array($userRole, ['responsible', 'company_admin', 'admin'])) {
    header("Location: dashboard.php");
    exit;
}

// Fetch current setting
$currentOmbudsmanId = null;
if ($companyId) {
    $stmt = $pdo->prepare("SELECT ombudsman_user_id FROM companies WHERE id = ?");
    $stmt->execute([$companyId]);
    $currentOmbudsmanId = $stmt->fetchColumn();
}

$message = '';

// Handle Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedId = $_POST['ombudsman_id'];
    if ($companyId && $selectedId) {
        try {
            $stmt = $pdo->prepare("UPDATE companies SET ombudsman_user_id = ? WHERE id = ?");
            $stmt->execute([$selectedId, $companyId]);
            $currentOmbudsmanId = $selectedId;
            $message = "Configuração salva com sucesso!";
        } catch (PDOException $e) {
            $message = "Erro ao salvar.";
        }
    }
}

// Fetch all users to populate dropdown
$users = [];
if ($companyId) {
    $stmt = $pdo->prepare("SELECT id, name, cargo, role FROM users WHERE company_id = ? ORDER BY name");
    $stmt->execute([$companyId]);
    $users = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Configuração Ouvidoria - TestProf Engaja</title>
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

        .config-card {
            background: var(--card-bg);
            max-width: 600px;
            padding: 2rem;
            border-radius: 1rem;
            border: 1px solid var(--glass-border);
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <h1 style="margin-bottom: 2rem;">Configuração da Ouvidoria</h1>

            <?php if ($message): ?>
                <div
                    style="background: rgba(16, 185, 129, 0.2); color: #34d399; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="config-card">
                <h3 style="margin-bottom: 1rem;">Quem recebe as denúncias?</h3>
                <p style="color: var(--text-muted); margin-bottom: 2rem;">Selecione o profissional que será responsável
                    por receber e tratar as mensagens enviadas para a ouvidoria. Esta pessoa terá acesso ao módulo de
                    leitura de relatórios.</p>

                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Responsável pela Ouvidoria</label>
                        <select name="ombudsman_id" class="form-control" required>
                            <option value="">Selecione um profissional...</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?php echo $u['id']; ?>" <?php echo ($u['id'] == $currentOmbudsmanId) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($u['name']) . " (" . htmlspecialchars($u['cargo']) . ")"; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Salvar Configuração</button>
                </form>
            </div>
        </main>
    </div>
</body>

</html>
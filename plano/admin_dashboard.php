<?php
require_once 'includes/auth.php';
checkAdmin();
include 'includes/header.php';

// Handle Actions (Add/Delete)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'create_company_user') {
        $companyName = $_POST['company_name'];
        $userName = $_POST['user_name'];
        $userEmail = $_POST['user_email'];
        $userPass = password_hash($_POST['user_password'], PASSWORD_DEFAULT);

        $pdo->beginTransaction();
        try {
            // 1. Create Company
            $stmt = $pdo->prepare("INSERT INTO companies (name) VALUES (?)");
            $stmt->execute([$companyName]);
            $companyId = $pdo->lastInsertId();

            // 2. Create User
            $stmt = $pdo->prepare("INSERT INTO users (company_id, name, email, password, role, trial_start_date) VALUES (?, ?, ?, ?, 'user', CURDATE())");
            $stmt->execute([$companyId, $userName, $userEmail, $userPass]);

            $pdo->commit();
            $msg = "Empresa e Usuário criados com sucesso!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Erro ao criar: " . $e->getMessage();
        }
    }

    // Update API Key
    if ($_POST['action'] == 'update_api_key') {
        $key = $_POST['api_key'];
        $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES ('openai_api_key', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$key, $key]);
        $msg = "Chave de API atualizada!";
    }
}

// Fetch Users
$users = $pdo->query("SELECT u.*, c.name as company_name FROM users u LEFT JOIN companies c ON u.company_id = c.id ORDER BY u.created_at DESC")->fetchAll();
$currentKey = getSystemApiKey($pdo);
?>

<div class="dashboard-grid">
    <!-- Admin Controls -->
    <div class="card" style="grid-column: 1 / -1;">
        <div class="card-header">
            <h2 class="card-title">Configurações de Administrador</h2>
        </div>

        <div class="form-section">
            <h3 class="form-section-title">Chave API OpenAI</h3>
            <form method="POST" style="display: flex; gap: 10px;">
                <input type="hidden" name="action" value="update_api_key">
                <input type="password" name="api_key" class="form-control" value="<?= htmlspecialchars($currentKey) ?>"
                    placeholder="sk-..." style="flex: 1;">
                <button type="submit" class="btn btn-primary">Salvar Chave</button>
            </form>
        </div>

        <div class="form-section" style="margin-top: 2rem;">
            <h3 class="form-section-title">Cadastrar Nova Empresa e Usuário</h3>
            <?php if (isset($msg))
                echo "<div class='alert' style='color: green; background: #ecfdf5; border-color: #a7f3d0;'>$msg</div>"; ?>
            <?php if (isset($error))
                echo "<div class='alert' style='color: red; background: #fef2f2; border-color: #fecaca;'>$error</div>"; ?>

            <form method="POST" class="form-grid">
                <input type="hidden" name="action" value="create_company_user">
                <div class="form-group">
                    <label>Nome da Empresa</label>
                    <input type="text" name="company_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Nome do Usuário</label>
                    <input type="text" name="user_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>E-mail Corporativo</label>
                    <input type="email" name="user_email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Senha Inicial</label>
                    <input type="text" name="user_password" class="form-control" required
                        value="<?= substr(md5(time()), 0, 8) ?>">
                </div>
                <div style="grid-column: 1/-1;">
                    <button type="submit" class="btn btn-primary">Criar Cadastro</button>
                </div>
            </form>
        </div>
    </div>

    <!-- User List -->
    <div class="card" style="grid-column: 1 / -1;">
        <div class="card-header">
            <h2 class="card-title">Usuários Cadastrados</h2>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Empresa</th>
                        <th>Usuário</th>
                        <th>E-mail</th>
                        <th>Role</th>
                        <th>Trial Início</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u):
                        $trialEnd = date('Y-m-d', strtotime($u['trial_start_date'] . ' + 30 days'));
                        $isExpired = date('Y-m-d') > $trialEnd && $u['role'] != 'admin';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($u['company_name'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($u['name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= $u['role'] ?></td>
                            <td><?= date('d/m/Y', strtotime($u['trial_start_date'])) ?></td>
                            <td>
                                <?php if ($u['status'] == 'active' && !$isExpired): ?>
                                    <span class="badge badge-star" style="background: #ecfdf5; color: #065f46;">Ativo</span>
                                <?php elseif ($isExpired): ?>
                                    <span class="badge" style="background: #fef2f2; color: #b91c1c;">Expirado</span>
                                <?php else: ?>
                                    <span class="badge" style="background: #f1f5f9; color: #64748b;">Inativo</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
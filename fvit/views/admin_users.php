<?php
// Add User
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $company_id = $_POST['company_id'] ?: null;

    if ($name && $email && $password) {
        // Check if email exists
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->rowCount() > 0) {
            $msg = "<div style='color: #ff8888;'>E-mail já cadastrado.</div>";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, company_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hash, $role, $company_id]);
            $msg = "<div style='color: #88ff88;'>Usuário criado com sucesso.</div>";
        }
    }
}

// Delete User
if (isset($_GET['delete_user'])) {
    $id = $_GET['delete_user'];
    // Prevent deleting self
    if ($id != $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
    }
    echo "<script>window.location.href='dashboard.php?page=home';</script>";
}

// Fetch Data
$users = $pdo->query("
    SELECT u.*, c.name as company_name 
    FROM users u 
    LEFT JOIN companies c ON u.company_id = c.id 
    ORDER BY u.created_at DESC
")->fetchAll();
$companies = $pdo->query("SELECT * FROM companies ORDER BY name")->fetchAll();
?>

<h2>Gerenciar Usuários</h2>

<?= $msg ?>

<div class="glass-card" style="margin-bottom: 2rem;">
    <h3>Novo Usuário</h3>
    <form method="POST" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
        <div class="form-group">
            <label>Nome</label>
            <input type="text" name="name" required>
        </div>
        <div class="form-group">
            <label>E-mail</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Senha</label>
            <input type="text" name="password" required>
        </div>
        <div class="form-group">
            <label>Função</label>
            <select name="role" required>
                <option value="responsible">Responsável</option>
                <option value="admin">Administrador</option>
            </select>
        </div>
        <div class="form-group">
            <label>Empresa</label>
            <select name="company_id">
                <option value="">Selecione...</option>
                <?php foreach ($companies as $c): ?>
                    <option value="<?= $c['id'] ?>">
                        <?= htmlspecialchars($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="display: flex; align-items: flex-end;">
            <button type="submit" name="add_user" class="btn btn-primary" style="width: 100%;">Criar Usuário</button>
        </div>
    </form>
</div>

<div class="glass-card">
    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Função</th>
                <th>Empresa</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($u['name']) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($u['email']) ?>
                    </td>
                    <td>
                        <span
                            style="padding: 2px 8px; border-radius: 4px; background: <?= $u['role'] == 'admin' ? 'var(--primary)' : 'rgba(255,255,255,0.1)' ?>">
                            <?= ucfirst($u['role']) ?>
                        </span>
                    </td>
                    <td>
                        <?= htmlspecialchars($u['company_name'] ?? '-') ?>
                    </td>
                    <td>
                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                            <a href="?page=home&delete_user=<?= $u['id'] ?>" class="btn-danger"
                                style="padding: 0.2rem 0.5rem; font-size: 0.8rem;"
                                onclick="return confirm('Excluir este usuário?')">X</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
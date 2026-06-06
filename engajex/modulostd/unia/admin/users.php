<?php
require_once '../config/db.php';
require_once 'header.php';

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$message = '';

// Fetch Companies for specific dropdown
$companies = $pdo->query("SELECT id, name FROM companies ORDER BY name ASC")->fetchAll();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $company_id = !empty($_POST['company_id']) ? $_POST['company_id'] : null;

    // Check Email unique (simple check)
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->rowCount() > 0) {
            $message = "Erro: Este e-mail já está em uso.";
            $action = 'create'; // Stay on form
        }
    }

    if (!$message) {
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            // Update
            $sql = "UPDATE users SET name = ?, email = ?, role = ?, company_id = ? WHERE id = ?";
            $params = [$name, $email, $role, $company_id, $_POST['id']];

            if (!empty($_POST['password'])) {
                $sql = "UPDATE users SET name = ?, email = ?, role = ?, company_id = ?, password = ? WHERE id = ?";
                $params = [$name, $email, $role, $company_id, password_hash($_POST['password'], PASSWORD_DEFAULT), $_POST['id']];
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $message = "Usuário atualizado com sucesso!";
        } else {
            // Create
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, company_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $password, $role, $company_id]);
            $message = "Usuário criado com sucesso!";
        }
        if ($message == "Usuário criado com sucesso!" || $message == "Usuário atualizado com sucesso!") {
            $action = 'list';
        }
    }
}

// Handle Delete
if ($action === 'delete' && $id) {
    if ($id == $_SESSION['user_id']) {
        $message = "Você não pode excluir a si mesmo!";
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: users.php");
        exit;
    }
}
?>

<div style="margin-bottom: 2rem;">
    <?php if ($message): ?>
        <div
            style="background: rgba(16, 185, 129, 0.2); color: #10b981; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1>Gerenciar Usuários</h1>
        <?php if ($action === 'list'): ?>
            <a href="users.php?action=create" class="btn btn-primary">+ Novo Usuário</a>
        <?php else: ?>
            <a href="users.php" class="btn btn-outline">Voltar</a>
        <?php endif; ?>
    </div>
</div>

<?php if ($action === 'create' || $action === 'edit'): ?>
    <?php
    $user = ['name' => '', 'email' => '', 'id' => '', 'role' => 'student', 'company_id' => ''];
    if ($action === 'edit' && $id) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
    }
    ?>
    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <h3>
            <?= $action === 'create' ? 'Cadastrar Usuário' : 'Editar Usuário' ?>
        </h3>
        <form method="POST" action="users.php">
            <input type="hidden" name="id" value="<?= $user['id'] ?>">
            <div class="form-group">
                <label>Nome Completo</label>
                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($user['name']) ?>">
            </div>
            <div class="form-group">
                <label>E-mail Corporativo</label>
                <input type="email" name="email" class="form-control" required
                    value="<?= htmlspecialchars($user['email']) ?>">
            </div>
            <div class="form-group">
                <label>Senha
                    <?= $action === 'edit' ? '(Deixe em branco para manter a atual)' : '' ?>
                </label>
                <input type="password" name="password" class="form-control" <?= $action === 'create' ? 'required' : '' ?>>
            </div>
            <div class="form-group">
                <label>Perfil de Acesso</label>
                <select name="role" class="form-control">
                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Administrador</option>
                    <option value="coordinator" <?= $user['role'] == 'coordinator' ? 'selected' : '' ?>>Coordenador</option>
                    <option value="instructor" <?= $user['role'] == 'instructor' ? 'selected' : '' ?>>Instrutor</option>
                    <option value="student" <?= $user['role'] == 'student' ? 'selected' : '' ?>>Aluno</option>
                </select>
            </div>
            <div class="form-group">
                <label>Empresa</label>
                <select name="company_id" class="form-control">
                    <option value="">Selecione...</option>
                    <?php foreach ($companies as $comp): ?>
                        <option value="<?= $comp['id'] ?>" <?= $user['company_id'] == $comp['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($comp['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Salvar</button>
        </form>
    </div>

<?php else: ?>
    <!-- LIST VIEW -->
    <?php
    $sql = "SELECT u.*, c.name as company_name 
            FROM users u 
            LEFT JOIN companies c ON u.company_id = c.id 
            ORDER BY u.created_at DESC";
    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll();
    ?>
    <div class="card table-container">
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Perfil</th>
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
                                style="text-transform: capitalize; padding: 0.2rem 0.5rem; border-radius: 4px; background: rgba(255,255,255,0.1);">
                                <?= $u['role'] ?>
                            </span>
                        </td>
                        <td>
                            <?= $u['company_name'] ? htmlspecialchars($u['company_name']) : '-' ?>
                        </td>
                        <td>
                            <a href="users.php?action=edit&id=<?= $u['id'] ?>" class="btn btn-outline"
                                style="font-size: 0.8rem; padding: 0.2rem 0.6rem;">Editar</a>
                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                <a href="users.php?action=delete&id=<?= $u['id'] ?>" class="btn btn-outline"
                                    style="font-size: 0.8rem; padding: 0.2rem 0.6rem; border-color: #ef4444; color: #ef4444;"
                                    onclick="return confirm('Tem certeza que deseja excluir este usuário?')">Excluir</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

</body>

</html>
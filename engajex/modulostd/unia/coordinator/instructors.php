<?php
require_once '../config/db.php';
require_once 'header.php';

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$message = '';

// Fetch Companies for dropdown (since instructors belong to companies too)
$companies = $pdo->query("SELECT id, name FROM companies ORDER BY name ASC")->fetchAll();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $company_id = !empty($_POST['company_id']) ? $_POST['company_id'] : null;
    $role = 'instructor'; // Force role

    // Check Email unique
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->rowCount() > 0) {
            $message = "Erro: Este e-mail já está em uso.";
            $action = 'create';
        }
    }

    if (!$message) {
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            // Update
            $sql = "UPDATE users SET name = ?, email = ?, company_id = ? WHERE id = ? AND role = 'instructor'";
            $params = [$name, $email, $company_id, $_POST['id']];

            if (!empty($_POST['password'])) {
                $sql = "UPDATE users SET name = ?, email = ?, company_id = ?, password = ? WHERE id = ? AND role = 'instructor'";
                $params = [$name, $email, $company_id, password_hash($_POST['password'], PASSWORD_DEFAULT), $_POST['id']];
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $message = "Instrutor atualizado com sucesso!";
        } else {
            // Create
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, company_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $password, $role, $company_id]);
            $message = "Instrutor cadastrado com sucesso!";
        }
        if (strpos($message, 'sucesso') !== false) {
            $action = 'list';
        }
    }
}

// Handle Delete
if ($action === 'delete' && $id) {
    // Only delete if role is instructor
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'instructor'");
    $stmt->execute([$id]);
    header("Location: instructors.php");
    exit;
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
        <h1>Gerenciar Instrutores</h1>
        <?php if ($action === 'list'): ?>
            <a href="instructors.php?action=create" class="btn btn-primary">+ Novo Instrutor</a>
        <?php else: ?>
            <a href="instructors.php" class="btn btn-outline">Voltar</a>
        <?php endif; ?>
    </div>
</div>

<?php if ($action === 'create' || $action === 'edit'): ?>
    <?php
    $user = ['name' => '', 'email' => '', 'id' => '', 'company_id' => ''];
    if ($action === 'edit' && $id) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'instructor'");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        if (!$user) {
            echo "<script>window.location.href='instructors.php';</script>";
            exit;
        }
    }
    ?>
    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <h3>
            <?= $action === 'create' ? 'Cadastrar Instrutor' : 'Editar Instrutor' ?>
        </h3>
        <form method="POST" action="instructors.php">
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
            WHERE u.role = 'instructor'
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
                    <th>Empresa</th>
                    <th>Data Cadastro</th>
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
                            <?= $u['company_name'] ? htmlspecialchars($u['company_name']) : '-' ?>
                        </td>
                        <td>
                            <?= date('d/m/Y', strtotime($u['created_at'])) ?>
                        </td>
                        <td>
                            <a href="instructors.php?action=edit&id=<?= $u['id'] ?>" class="btn btn-outline"
                                style="font-size: 0.8rem; padding: 0.2rem 0.6rem;">Editar</a>
                            <a href="instructors.php?action=delete&id=<?= $u['id'] ?>" class="btn btn-outline"
                                style="font-size: 0.8rem; padding: 0.2rem 0.6rem; border-color: #ef4444; color: #ef4444;"
                                onclick="return confirm('Tem certeza?')">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (count($users) == 0): ?>
            <p style="text-align: center; padding: 2rem; color: var(--text-muted);">Nenhum instrutor cadastrado.</p>
        <?php endif; ?>
    </div>
<?php endif; ?>

</body>

</html>
require_once 'config/db.php';
requireAuth();
require_once 'includes/header.php';

if ($_SESSION['role'] !== 'manager') {
header("Location: index.php");
exit;
}

$msg = '';
$companyId = $_SESSION['company_id'];

// Create User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
$name = $_POST['name'];
$email = $_POST['email'];
$role = $_POST['role']; // user or manager (sub-manager?) - Let's stick to 'user' for now as per prompt

// Check if email exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
$msg = "E-mail já cadastrado.";
} else {
$password = password_hash('123456', PASSWORD_DEFAULT); // Default
$stmt = $pdo->prepare("INSERT INTO users (company_id, name, email, password, role) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$companyId, $name, $email, $password, 'user']);
$msg = "Usuário criado. Senha padrão: 123456";
}
}

// Delete
if (isset($_GET['delete'])) {
$id = $_GET['delete'];
// Ensure belongs to company
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND company_id = ?");
$stmt->execute([$id, $companyId]);
header("Location: manager_users.php");
exit;
}

$users = $pdo->prepare("SELECT * FROM users WHERE company_id = ? AND role = 'user' ORDER BY name");
$users->execute([$companyId]);
$users = $users->fetchAll();
?>

<div class="flex-between mb-4">
    <h2>Membros da Equipe</h2>
    <button onclick="document.getElementById('newUserModal').style.display='block'" class="btn btn-primary">
        <i class="fas fa-user-plus"></i> Novo Usuário
    </button>
</div>

<?php if ($msg): ?>
    <div
        style="background: rgba(16, 185, 129, 0.2); color: #6ee7b7; padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem;">
        <?= $msg ?>
    </div>
<?php endif; ?>

<div class="card table-container">
    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Data Cadastro</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['name']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <a href="?delete=<?= $u['id'] ?>" class="btn btn-danger"
                            onclick="return confirm('Confirmar exclusão?')"
                            style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Remover</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div id="newUserModal"
    style="display:none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:1000;">
    <div class="card" style="width: 90%; max-width: 500px; margin: 5% auto;">
        <h3>Novo Usuário</h3>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="input-group">
                <label>Nome Completo</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="input-group">
                <label>E-mail Corporativo</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="flex-between">
                <button type="button" class="btn btn-outline"
                    onclick="document.getElementById('newUserModal').style.display='none'">Cancelar</button>
                <button type="submit" class="btn btn-primary">Adicionar</button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
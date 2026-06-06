require_once 'config/db.php';
requireAuth();
require_once 'includes/header.php';

if ($_SESSION['role'] !== 'superadmin') {
header("Location: index.php");
exit;
}

$msg = '';

// Handle Create Company
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
$companyName = $_POST['company_name'];
$managerName = $_POST['manager_name'];
$managerEmail = $_POST['manager_email'];

try {
$pdo->beginTransaction();

// Create Company
$stmt = $pdo->prepare("INSERT INTO companies (name) VALUES (?)");
$stmt->execute([$companyName]);
$companyId = $pdo->lastInsertId();

// Create Manager
$password = password_hash('mudeme', PASSWORD_DEFAULT); // Default password
$stmt = $pdo->prepare("INSERT INTO users (company_id, name, email, password, role) VALUES (?, ?, ?, ?, 'manager')");
$stmt->execute([$companyId, $managerName, $managerEmail, $password]);

$pdo->commit();
$msg = "Empresa e Gestor criados com sucesso. Senha padrão: mudeme";
} catch (Exception $e) {
$pdo->rollBack();
$msg = "Erro: " . $e->getMessage();
}
}

// Handle Delete
if (isset($_GET['delete'])) {
$id = $_GET['delete'];
$pdo->prepare("DELETE FROM companies WHERE id = ?")->execute([$id]);
header("Location: admin_companies.php");
exit;
}

// List Companies
$companies = $pdo->query("
SELECT c.*, u.name as manager_name, u.email as manager_email
FROM companies c
LEFT JOIN users u ON u.company_id = c.id AND u.role = 'manager'
ORDER BY c.created_at DESC
")->fetchAll();
?>

<div class="flex-between mb-4">
    <h2>Gerenciar Empresas</h2>
    <button onclick="document.getElementById('newCompanyModal').style.display='block'" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nova Empresa
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
                <th>Empresa</th>
                <th>Responsável</th>
                <th>E-mail</th>
                <th>Data Cadastro</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($companies as $company): ?>
                <tr>
                    <td><?= htmlspecialchars($company['name']) ?></td>
                    <td><?= htmlspecialchars($company['manager_name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($company['manager_email'] ?? 'N/A') ?></td>
                    <td><?= date('d/m/Y', strtotime($company['created_at'])) ?></td>
                    <td>
                        <a href="?delete=<?= $company['id'] ?>" class="btn btn-danger"
                            style="padding: 0.25rem 0.5rem; font-size: 0.8rem;"
                            onclick="return confirm('Tem certeza? Isso apagará todos os usuários e dados da empresa.')">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div id="newCompanyModal"
    style="display:none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:1000;">
    <div class="card" style="width: 90%; max-width: 500px; margin: 5% auto;">
        <h3>Nova Empresa</h3>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="input-group">
                <label>Nome da Empresa</label>
                <input type="text" name="company_name" class="form-control" required>
            </div>
            <h4>Dados do Responsável</h4>
            <div class="input-group">
                <label>Nome do Gestor</label>
                <input type="text" name="manager_name" class="form-control" required>
            </div>
            <div class="input-group">
                <label>E-mail do Gestor</label>
                <input type="email" name="manager_email" class="form-control" required>
            </div>
            <div class="flex-between">
                <button type="button" class="btn btn-outline"
                    onclick="document.getElementById('newCompanyModal').style.display='none'">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
<?php
use PDI\models\User;
use PDI\models\Company;

// Guard
if (($_SESSION['user_role'] ?? '') !== 'superadmin') {
    header('Location: ?route=dashboard');
    exit;
}

$userModel = new User($pdo);
$companyModel = new Company($pdo);

$success = '';
$error = '';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $data = [
        'name' => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'role' => $_POST['role'] ?? 'employee',
        'company_id' => !empty($_POST['company_id']) ? $_POST['company_id'] : null,
    ];

    try {
        if ($action === 'create') {
            $data['password'] = $_POST['password'] ?? '123456';
            if ($userModel->create($data)) {
                $success = 'Usuário criado com sucesso!';
            } else {
                $error = 'Erro ao criar usuário.';
            }
        } elseif ($action === 'update') {
            $id = $_POST['id'] ?? 0;
            if (!empty($_POST['password'])) {
                $data['password'] = $_POST['password'];
            }
            if ($userModel->update($id, $data)) {
                $success = 'Usuário atualizado com sucesso!';
            } else {
                $error = 'Erro ao atualizar usuário.';
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Fetch Data
$users = $userModel->getAll(); // This method should join with companies
$companies = $companyModel->getAll();

ob_start();
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gerenciar Usuários (Global)</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal"
            onclick="openModal()">
            <i class="fas fa-plus"></i> Novo Usuário
        </button>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Empresa</th>
                            <th>Papel</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($user['company_name'] ?? '-'); ?>
                                </td>
                                <td>
                                    <?php
                                    $badges = [
                                        'superadmin' => 'bg-danger',
                                        'company_admin' => 'bg-primary',
                                        'employee' => 'bg-secondary'
                                    ];
                                    $roleName = [
                                        'superadmin' => 'Super Admin',
                                        'company_admin' => 'Gestor Empresa',
                                        'employee' => 'Colaborador'
                                    ];
                                    $badge = $badges[$user['role']] ?? 'bg-secondary';
                                    $label = $roleName[$user['role']] ?? $user['role'];
                                    ?>
                                    <span class="badge <?php echo $badge; ?>"><?php echo $label; ?></span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary"
                                        onclick='editUser(<?php echo json_encode($user); ?>)'>
                                        <i class="fas fa-edit"></i> Editar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Novo Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="create">
                    <input type="hidden" name="id" id="userId">

                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="name" id="userName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="userEmail" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Senha</label>
                        <input type="password" name="password" class="form-control"
                            placeholder="Deixe em branco para manter a atual">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Papel (Role)</label>
                        <select name="role" id="userRole" class="form-select">
                            <option value="employee">Colaborador</option>
                            <option value="company_admin">Gestor de Empresa (Responsável)</option>
                            <option value="superadmin">Super Admin</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Empresa</label>
                        <select name="company_id" id="userCompany" class="form-select">
                            <option value="">-- Nenhuma (Global) --</option>
                            <?php foreach ($companies as $comp): ?>
                                <option value="<?php echo $comp['id']; ?>">
                                    <?php echo htmlspecialchars($comp['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Gestores devem ser vinculados a uma empresa.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openModal() {
        document.getElementById('modalTitle').innerText = 'Novo Usuário';
        document.getElementById('formAction').value = 'create';
        document.getElementById('userId').value = '';
        document.getElementById('userName').value = '';
        document.getElementById('userEmail').value = '';
        document.getElementById('userRole').value = 'employee';
        document.getElementById('userCompany').value = '';
    }

    function editUser(user) {
        document.getElementById('modalTitle').innerText = 'Editar Usuário';
        document.getElementById('formAction').value = 'update';
        document.getElementById('userId').value = user.id;
        document.getElementById('userName').value = user.name;
        document.getElementById('userEmail').value = user.email;
        document.getElementById('userRole').value = user.role || 'employee';
        document.getElementById('userCompany').value = user.company_id || '';

        var modal = new bootstrap.Modal(document.getElementById('userModal'));
        modal.show();
    }
</script>

<?php
$content = ob_get_clean();
$pageTitle = "Gerenciar Usuários";
require_once TEMPLATES_PATH . '/base.php';
?>
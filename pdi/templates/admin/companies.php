<?php
use PDI\models\Company;
use PDI\models\User;

// Guard
if (($_SESSION['user_role'] ?? '') !== 'superadmin') {
    header('Location: ?route=dashboard');
    exit;
}

$companyModel = new Company($pdo);
$userModel = new User($pdo);
$message = '';
$error = '';

// Handle POST actions... (Mantendo a mesma lógica de antes, focando no visual)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'create') {
            if ($companyModel->create($_POST)) {
                $message = 'Empresa criada com sucesso!';
            } else {
                $error = 'Erro ao criar empresa.';
            }
        } elseif ($action === 'delete') {
            $id = $_POST['id'] ?? 0;
            if ($companyModel->delete($id)) {
                $message = 'Empresa removida.';
            } else {
                $error = 'Erro ao remover empresa. Verifique se há usuários vinculados.';
            }
        } elseif ($action === 'create_manager') {
            $userData = [
                'name' => $_POST['manager_name'],
                'email' => $_POST['manager_email'],
                'password' => $_POST['manager_password'],
                'role' => 'company_admin',
                'company_id' => $_POST['company_id']
            ];

            if ($userModel->findByEmail($userData['email'])) {
                $error = 'Já existe um usuário com este e-mail.';
            } else {
                if ($userModel->create($userData)) {
                    $message = 'Responsável criado com sucesso!';
                } else {
                    $error = 'Erro ao criar responsável.';
                }
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$companies = $companyModel->getAll();

ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Gestão de Empresas</h2>
        <p class="text-muted">Gerencie as organizações parceiras e seus acessos.</p>
    </div>
    <!-- Botão de Nova Empresa agora abre modal para manter o foco na lista -->
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCompanyModal">
        <i class="fas fa-plus-circle me-2"></i> Nova Empresa
    </button>
</div>

<?php if ($message): ?>
    <div class="alert alert-success border-0 shadow-sm border-start border-4 border-success"><?php echo $message; ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger border-0 shadow-sm border-start border-4 border-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Empresa / CNPJ</th>
                                <th>Responsáveis</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($companies as $c):
                                $managers = $userModel->getByCompany($c['id']);
                                $managerNames = [];
                                foreach ($managers as $m) {
                                    if ($m['role'] === 'company_admin')
                                        $managerNames[] = $m['name'];
                                }
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded p-2 me-3 text-primary">
                                                <i class="fas fa-building fa-lg"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($c['name']); ?>
                                                </div>
                                                <div class="small text-muted"><?php echo htmlspecialchars($c['cnpj']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (empty($managerNames)): ?>
                                            <span class="badge bg-light text-muted fw-normal border">Pendante</span>
                                        <?php else: ?>
                                            <div class="d-flex gap-1 flex-wrap">
                                                <?php foreach ($managerNames as $name): ?>
                                                    <span
                                                        class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">
                                                        <i
                                                            class="fas fa-user-circle me-1"></i><?php echo htmlspecialchars($name); ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-success bg-opacity-10 text-success">Ativa</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn btn-sm btn-light text-primary border me-1"
                                            onclick="openManagerModal(<?php echo $c['id']; ?>, '<?php echo htmlspecialchars($c['name']); ?>')"
                                            data-bs-toggle="tooltip" title="Adicionar Gestor">
                                            <i class="fas fa-user-plus"></i>
                                        </button>

                                        <form method="post" style="display:inline"
                                            onsubmit="return confirm('Tem certeza? Isso pode afetar usuários vinculados.');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                            <button class="btn btn-sm btn-light text-danger border" data-bs-toggle="tooltip"
                                                title="Excluir">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Create Company -->
<div class="modal fade" id="createCompanyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Nova Empresa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-4">Preencha os dados básicos da organização.</p>
                <form method="post">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label class="form-label fw-500">Razão Social / Nome Fantasia</label>
                        <input type="text" name="name" class="form-control" required
                            placeholder="Ex: Tech Solutions Ltda">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-500">CNPJ</label>
                        <input type="text" name="cnpj" class="form-control" placeholder="00.000.000/0001-00">
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary">Cadastrar Empresa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Create Manager -->
<div class="modal fade" id="managerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Novo Responsável</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="create_manager">

                <div class="d-flex align-items-center bg-light p-3 rounded mb-4">
                    <div class="me-3 text-primary"><i class="fas fa-building fa-2x"></i></div>
                    <div>
                        <small class="text-muted d-block">Adicionando gestor para:</small>
                        <strong id="modalCompanyName" class="text-dark"></strong>
                    </div>
                </div>

                <form method="post">
                    <input type="hidden" name="action" value="create_manager">
                    <input type="hidden" name="company_id" id="modalCompanyId">

                    <div class="mb-3">
                        <label class="form-label fw-500">Nome Completo</label>
                        <input type="text" name="manager_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-500">E-mail Corporativo</label>
                        <input type="email" name="manager_email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-500">Senha de Acesso</label>
                        <input type="password" name="manager_password" class="form-control" required>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-success">Criar Responsável</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function openManagerModal(id, name) {
        document.getElementById('modalCompanyId').value = id;
        document.getElementById('modalCompanyName').innerText = name;
        var modal = new bootstrap.Modal(document.getElementById('managerModal'));
        modal.show();
    }
    // Tooltip init
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Gerenciar Empresas';
require_once TEMPLATES_PATH . '/base.php';
?>
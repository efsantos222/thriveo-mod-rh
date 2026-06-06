<?php
use PDI\models\User;

// Guard
if (($_SESSION['user_role'] ?? '') !== 'company_admin') {
    die('Acesso negado');
}

$companyId = $_SESSION['company_id'];
$userModel = new User($pdo);
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $data = $_POST;
        $data['company_id'] = $companyId;
        $data['role'] = 'employee'; // Default role for created users
        $data['password'] = '123456'; // Default password (should specificy or email)

        try {
            if ($userModel->create($data)) {
                $message = "Colaborador cadastrado! Senha padrão: 123456";
            } else {
                $error = "Erro ao cadastrar.";
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$users = $userModel->getByCompany($companyId);

ob_start();
?>
<h2>Gerenciar Colaboradores</h2>
<?php if ($message): ?>
    <div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

<div class="card mb-4">
    <div class="card-header">Novo Colaborador</div>
    <div class="card-body">
        <form method="post">
            <input type="hidden" name="action" value="create">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Nome</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Cargo</label>
                    <input type="text" name="job_title" class="form-control">
                </div>
            </div>
            <div class="mb-3">
                <label>Atividades</label>
                <textarea name="job_activities" class="form-control" rows="2"></textarea>
            </div>
            <div class="mb-3">
                <label>Avaliação por Competência (Inicial)</label>
                <textarea name="competency_eval" class="form-control" rows="2"
                    placeholder="Descreva as competências atuais..."></textarea>
            </div>
            <div class="mb-3">
                <label>Demandas de Melhoria</label>
                <textarea name="improvement_demands" class="form-control" rows="2"
                    placeholder="O que precisa melhorar..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Cadastrar e Gerar PDI (Futuro)</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Equipe</div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Cargo</th>
                    <th>Email</th>
                    <th>Status PDI</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($u['name']); ?></td>
                        <td><?php echo htmlspecialchars($u['job_title'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td>
                        <a href="?route=pdi/generate&user_id=<?php echo $u['id']; ?>" class="btn btn-sm btn-info" title="Gerar PDI com IA">
                            <i class="fas fa-magic"></i> Gerar PDI
                        </a>
                    </td> 
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Colaboradores';
require_once TEMPLATES_PATH . '/base.php';
?>
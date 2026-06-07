<?php
$pageTitle = "Usuários";
require_once TEMPLATES_PATH . '/base.php';

// Verificar se o usuário é admin
if (!isset($_SESSION['user']['is_admin']) || !$_SESSION['user']['is_admin']) {
    $_SESSION['error'] = "Acesso negado. Você não tem permissão para acessar esta página.";
    header('Location: ?route=pdi');
    exit;
}

// Buscar todos os usuários
$stmt = $pdo->query('
    SELECT u.*, 
           m.name as manager_name 
    FROM users u 
    LEFT JOIN users m ON u.manager_id = m.id 
    ORDER BY u.name
');
$users = $stmt->fetchAll();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gerenciar Usuários</h2>
        <a href="?route=users/new" class="btn btn-primary">
            <i class="fas fa-plus"></i> Novo Usuário
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Gestor</th>
                            <th>Tipo</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['manager_name'] ?? '-'); ?></td>
                                <td>
                                    <?php if ($user['is_admin']): ?>
                                        <span class="badge bg-danger">Admin</span>
                                    <?php endif; ?>
                                    <?php if ($user['is_manager']): ?>
                                        <span class="badge bg-primary">Gestor</span>
                                    <?php endif; ?>
                                    <?php if (!$user['is_admin'] && !$user['is_manager']): ?>
                                        <span class="badge bg-secondary">Colaborador</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $user['active'] ? 'success' : 'danger'; ?>">
                                        <?php echo $user['active'] ? 'Ativo' : 'Inativo'; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?route=users/edit&id=<?php echo $user['id']; ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($user['id'] != $_SESSION['user']['id']): ?>
                                        <button type="button" 
                                                class="btn btn-sm btn-<?php echo $user['active'] ? 'danger' : 'success'; ?>"
                                                onclick="toggleUserStatus(<?php echo $user['id']; ?>, <?php echo $user['active'] ? 'false' : 'true'; ?>)">
                                            <i class="fas fa-<?php echo $user['active'] ? 'ban' : 'check'; ?>"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function toggleUserStatus(userId, newStatus) {
    if (confirm('Tem certeza que deseja ' + (newStatus ? 'ativar' : 'desativar') + ' este usuário?')) {
        window.location.href = `?route=users/toggle&id=${userId}&status=${newStatus}`;
    }
}
</script>

<?php require_once TEMPLATES_PATH . '/footer.php'; ?>

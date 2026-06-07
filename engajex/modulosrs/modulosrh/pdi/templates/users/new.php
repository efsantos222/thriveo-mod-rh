<?php
$pageTitle = "Novo Usuário";
require_once TEMPLATES_PATH . '/base.php';

// Verificar se o usuário é admin
if (!isset($_SESSION['user']['is_admin']) || !$_SESSION['user']['is_admin']) {
    $_SESSION['error'] = "Acesso negado. Você não tem permissão para acessar esta página.";
    header('Location: ?route=pdi');
    exit;
}

// Buscar gestores para o select
$stmt = $pdo->query('SELECT id, name FROM users WHERE is_manager = 1 AND active = 1 ORDER BY name');
$managers = $stmt->fetchAll();
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Novo Usuário</h2>
                <a href="?route=users" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>

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
                    <form method="post" action="?route=users/create">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="name" name="name" required
                                value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required
                                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="password" name="password" required
                                minlength="6">
                            <div class="form-text">A senha deve ter pelo menos 6 caracteres.</div>
                        </div>

                        <div class="mb-3">
                            <label for="manager_id" class="form-label">Gestor</label>
                            <select class="form-select" id="manager_id" name="manager_id">
                                <option value="">Selecione um gestor</option>
                                <?php foreach ($managers as $manager): ?>
                                    <option value="<?php echo $manager['id']; ?>"
                                        <?php echo (isset($_POST['manager_id']) && $_POST['manager_id'] == $manager['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($manager['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tipo de Usuário</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_admin" name="is_admin" value="1"
                                    <?php echo isset($_POST['is_admin']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_admin">
                                    Administrador
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_manager" name="is_manager" value="1"
                                    <?php echo isset($_POST['is_manager']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_manager">
                                    Gestor
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Criar Usuário
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once TEMPLATES_PATH . '/footer.php'; ?>

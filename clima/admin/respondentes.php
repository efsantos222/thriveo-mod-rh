<?php
require_once '../config/config.php';
require_once '../config/database.php';
checkAuth();

$db = Database::getInstance()->getConnection();

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $codigo = strtoupper(sanitize($_POST['codigo']));
        $area = sanitize($_POST['area']);
        
        try {
            $stmt = $db->prepare("INSERT INTO respondentes (codigo, area) VALUES (?, ?)");
            $stmt->execute([$codigo, $area]);
            $_SESSION['success'] = "Respondente adicionado com sucesso.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erro ao adicionar respondente. Verifique se o código já existe.";
        }
        
    } elseif ($action === 'update') {
        $id = (int)$_POST['id'];
        $codigo = strtoupper(sanitize($_POST['codigo']));
        $area = sanitize($_POST['area']);
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        
        try {
            $stmt = $db->prepare("UPDATE respondentes SET codigo = ?, area = ?, ativo = ? WHERE id = ?");
            $stmt->execute([$codigo, $area, $ativo, $id]);
            $_SESSION['success'] = "Respondente atualizado com sucesso.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erro ao atualizar respondente. Verifique se o código já existe.";
        }
        
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        try {
            // Inicia uma transação
            $db->beginTransaction();

            // Primeiro, exclui todas as respostas do respondente
            $stmt = $db->prepare("DELETE FROM respostas WHERE id_respondente = ?");
            $stmt->execute([$id]);

            // Depois, exclui o respondente
            $stmt = $db->prepare("DELETE FROM respondentes WHERE id = ?");
            $stmt->execute([$id]);

            // Confirma as alterações
            $db->commit();
            $_SESSION['success'] = "Respondente e suas respostas foram removidos com sucesso.";

        } catch (PDOException $e) {
            // Em caso de erro, desfaz as alterações
            $db->rollBack();
            error_log("Erro ao remover respondente: " . $e->getMessage());
            $_SESSION['error'] = "Erro ao remover respondente. Por favor, tente novamente.";
        }
    }
    
    redirect('/admin/respondentes.php');
}

// Paginação
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Buscar total de registros
$total = $db->query("SELECT COUNT(*) FROM respondentes")->fetchColumn();
$total_pages = ceil($total / $per_page);

// Buscar respondentes
$stmt = $db->prepare("SELECT * FROM respondentes ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->execute([$per_page, $offset]);
$respondentes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Respondentes - Área Administrativa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Painel Administrativo</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="perguntas.php">Perguntas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="respondentes.php">Respondentes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="relatorios.php">Relatórios</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Sair</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestão de Respondentes</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#novoRespondenteModal">
                Novo Respondente
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Área</th>
                        <th>Status</th>
                        <th>Criado em</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($respondentes as $respondente): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($respondente['codigo']); ?></td>
                        <td><?php echo htmlspecialchars($respondente['area']); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $respondente['ativo'] ? 'success' : 'danger'; ?>">
                                <?php echo $respondente['ativo'] ? 'Ativo' : 'Inativo'; ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($respondente['created_at'])); ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary" 
                                    onclick="editarRespondente(<?php echo htmlspecialchars(json_encode($respondente)); ?>)">
                                Editar
                            </button>
                            <button class="btn btn-sm btn-danger" 
                                    onclick="confirmarExclusao(<?php echo $respondente['id']; ?>)">
                                Excluir
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Navegação de páginas">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>

    <!-- Modal Novo Respondente -->
    <div class="modal fade" id="novoRespondenteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Novo Respondente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <div class="mb-3">
                            <label for="codigo" class="form-label">Código (6 caracteres)</label>
                            <input type="text" class="form-control" id="codigo" name="codigo" 
                                   required maxlength="6" minlength="6" pattern="[A-Za-z0-9]{6}"
                                   placeholder="Ex: ABC123">
                            <div class="form-text">Use letras e números, exatamente 6 caracteres.</div>
                        </div>
                        <div class="mb-3">
                            <label for="area" class="form-label">Área</label>
                            <input type="text" class="form-control" id="area" name="area" 
                                   required maxlength="20"
                                   placeholder="Ex: TI, RH, Vendas">
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

    <!-- Modal Editar Respondente -->
    <div class="modal fade" id="editarRespondenteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Respondente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label for="edit_codigo" class="form-label">Código (6 caracteres)</label>
                            <input type="text" class="form-control" id="edit_codigo" name="codigo" 
                                   required maxlength="6" minlength="6" pattern="[A-Za-z0-9]{6}"
                                   placeholder="Ex: ABC123">
                            <div class="form-text">Use letras e números, exatamente 6 caracteres.</div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_area" class="form-label">Área</label>
                            <input type="text" class="form-control" id="edit_area" name="area" 
                                   required maxlength="20"
                                   placeholder="Ex: TI, RH, Vendas">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="edit_ativo" name="ativo">
                            <label class="form-check-label" for="edit_ativo">Ativo</label>
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

    <!-- Modal Confirmar Exclusão -->
    <div class="modal fade" id="confirmarExclusaoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Tem certeza que deseja excluir este respondente?
                </div>
                <div class="modal-footer">
                    <form method="POST">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editarRespondente(respondente) {
            document.getElementById('edit_id').value = respondente.id;
            document.getElementById('edit_codigo').value = respondente.codigo;
            document.getElementById('edit_area').value = respondente.area;
            document.getElementById('edit_ativo').checked = respondente.ativo == 1;
            new bootstrap.Modal(document.getElementById('editarRespondenteModal')).show();
        }

        function confirmarExclusao(id) {
            document.getElementById('delete_id').value = id;
            new bootstrap.Modal(document.getElementById('confirmarExclusaoModal')).show();
        }
    </script>
</body>
</html>

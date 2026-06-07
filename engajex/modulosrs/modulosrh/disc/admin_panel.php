<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
$auth->requireRole('admin');

$db = getDbConnection();

// Buscar todos os candidatos e seus testes
$stmt = $db->prepare("
    SELECT 
        c.id,
        c.name,
        c.email,
        c.created_at,
        c.selector_id,
        s.name as selector_name,
        GROUP_CONCAT(DISTINCT ta.test_type) as assigned_tests,
        GROUP_CONCAT(DISTINCT tr.test_type) as completed_tests
    FROM candidates c
    LEFT JOIN selectors s ON c.selector_id = s.id
    LEFT JOIN test_assignments ta ON c.id = ta.candidate_id
    LEFT JOIN test_results tr ON c.id = tr.candidate_id
    GROUP BY c.id
    ORDER BY c.created_at DESC
");
$stmt->execute();
$candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar todos os seletores
$stmt = $db->prepare("SELECT * FROM selectors ORDER BY name");
$stmt->execute();
$selectors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'update_selector':
                    $stmt = $db->prepare("UPDATE candidates SET selector_id = ? WHERE id = ?");
                    $stmt->execute([$_POST['selector_id'], $_POST['candidate_id']]);
                    header('Location: admin_panel.php?success=Avaliador(a) atualizado(a) com sucesso');
                    exit;
                    break;

                case 'delete_candidate':
                    // Primeiro excluir registros relacionados
                    $db->beginTransaction();
                    try {
                        $stmt = $db->prepare("DELETE FROM test_results WHERE candidate_id = ?");
                        $stmt->execute([$_POST['candidate_id']]);
                        
                        $stmt = $db->prepare("DELETE FROM test_assignments WHERE candidate_id = ?");
                        $stmt->execute([$_POST['candidate_id']]);
                        
                        $stmt = $db->prepare("DELETE FROM candidates WHERE id = ?");
                        $stmt->execute([$_POST['candidate_id']]);
                        
                        $db->commit();
                        header('Location: admin_panel.php?success=Candidato excluído com sucesso');
                        exit;
                    } catch (Exception $e) {
                        $db->rollBack();
                        throw $e;
                    }
                    break;
            }
        } catch (Exception $e) {
            header('Location: admin_panel.php?error=' . urlencode($e->getMessage()));
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .status-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }
        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .selector-select {
            max-width: 200px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-shield-lock"></i>
                Painel do Administrador
            </a>
            <div class="navbar-text text-white">
                <i class="bi bi-person-circle"></i>
                <?php echo htmlspecialchars($auth->getCurrentUserName()); ?>
                <a href="logout.php" class="btn btn-outline-light btn-sm ms-3">
                    <i class="bi bi-box-arrow-right"></i>
                    Sair
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header bg-white">
                <h4 class="card-title mb-0">
                    <i class="bi bi-people"></i>
                    Gerenciamento de Candidatos
                </h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="candidatesTable">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Avaliador(a)</th>
                                <th>Data de Cadastro</th>
                                <th>Testes Atribuídos</th>
                                <th>Testes Concluídos</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($candidates as $candidate): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($candidate['name']); ?></td>
                                    <td><?php echo htmlspecialchars($candidate['email']); ?></td>
                                    <td>
                                        <form method="POST" class="d-flex align-items-center selector-form">
                                            <input type="hidden" name="action" value="update_selector">
                                            <input type="hidden" name="candidate_id" value="<?php echo $candidate['id']; ?>">
                                            <select name="selector_id" class="form-select form-select-sm selector-select me-2" onchange="this.form.submit()">
                                                <option value="">Sem avaliador(a)</option>
                                                <?php foreach ($selectors as $selector): ?>
                                                    <option value="<?php echo $selector['id']; ?>" <?php echo $selector['id'] == $candidate['selector_id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($selector['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </form>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($candidate['created_at'])); ?></td>
                                    <td>
                                        <?php
                                        $assignedTests = explode(',', $candidate['assigned_tests'] ?? '');
                                        foreach ($assignedTests as $test):
                                            if ($test):
                                        ?>
                                            <span class="badge bg-info status-badge">
                                                <?php echo strtoupper($test); ?>
                                            </span>
                                        <?php
                                            endif;
                                        endforeach;
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $completedTests = explode(',', $candidate['completed_tests'] ?? '');
                                        foreach ($completedTests as $test):
                                            if ($test):
                                        ?>
                                            <span class="badge bg-success status-badge">
                                                <?php echo strtoupper($test); ?>
                                            </span>
                                        <?php
                                            endif;
                                        endforeach;
                                        ?>
                                    </td>
                                    <td class="action-buttons">
                                        <a href="view_results.php?candidate_id=<?php echo $candidate['id']; ?>" 
                                           class="btn btn-outline-primary btn-sm" 
                                           title="Ver Resultados">
                                            <i class="bi bi-graph-up"></i>
                                        </a>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir este candidato? Esta ação não pode ser desfeita.');">
                                            <input type="hidden" name="action" value="delete_candidate">
                                            <input type="hidden" name="candidate_id" value="<?php echo $candidate['id']; ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Excluir Candidato">
                                                <i class="bi bi-trash"></i>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#candidatesTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json'
            },
            order: [[3, 'desc']], // Ordenar por data de cadastro
            pageLength: 25,
            columnDefs: [
                { orderable: false, targets: [6] } // Desabilitar ordenação na coluna de ações
            ]
        });

        // Remover o submit automático do form ao pressionar enter
        $('.selector-form').on('keypress', function(e) {
            return e.which !== 13;
        });
    });
    </script>
</body>
</html>

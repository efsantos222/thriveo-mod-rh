<?php
require_once 'includes/auth.php';

$auth = new Auth();
$auth->requireSuperAdmin();

$db = getDbConnection();

// Verificar se um ID de selecionador foi fornecido
$selectorId = $_GET['selector_id'] ?? null;
if (!$selectorId) {
    header('Location: superadmin_panel.php');
    exit;
}

// Buscar informações do selecionador
$stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND role = 'selector'");
$stmt->execute([$selectorId]);
$selector = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$selector) {
    header('Location: superadmin_panel.php');
    exit;
}

// Buscar candidatos do selecionador
$stmt = $db->prepare("
    SELECT c.*, 
           GROUP_CONCAT(DISTINCT ta.test_type) as assigned_tests,
           GROUP_CONCAT(DISTINCT tr.test_type) as completed_tests
    FROM candidates c
    LEFT JOIN test_assignments ta ON c.id = ta.candidate_id
    LEFT JOIN test_results tr ON c.id = tr.candidate_id
    WHERE c.selector_id = ?
    GROUP BY c.id
    ORDER BY c.created_at DESC
");
$stmt->execute([$selectorId]);
$candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidatos de <?php echo htmlspecialchars($selector['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Sistema de Avaliação</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="superadmin_panel.php">
                            <i class="bi bi-arrow-left"></i> Voltar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Sair</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col">
                <h2>Candidatos de <?php echo htmlspecialchars($selector['name']); ?></h2>
                <p class="text-muted">Email: <?php echo htmlspecialchars($selector['email']); ?></p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Testes Atribuídos</th>
                                <th>Status</th>
                                <th>Data de Cadastro</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($candidates as $candidate): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($candidate['name']); ?></td>
                                <td><?php echo htmlspecialchars($candidate['email']); ?></td>
                                <td>
                                    <?php
                                    $assigned = explode(',', $candidate['assigned_tests'] ?? '');
                                    $completed = explode(',', $candidate['completed_tests'] ?? '');
                                    foreach ($assigned as $test) {
                                        if ($test) {
                                            $status = in_array($test, $completed) ? 'success' : 'warning';
                                            echo "<span class='badge bg-{$status} me-1'>" . strtoupper($test) . "</span>";
                                        }
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $total = count(array_filter($assigned));
                                    $completed = count(array_filter($completed));
                                    if ($total === 0) {
                                        echo "<span class='badge bg-secondary'>Sem testes</span>";
                                    } elseif ($completed === $total) {
                                        echo "<span class='badge bg-success'>Concluído</span>";
                                    } else {
                                        echo "<span class='badge bg-warning'>Em andamento</span>";
                                    }
                                    ?>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($candidate['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewResults(<?php echo $candidate['id']; ?>)">
                                        <i class="bi bi-graph-up"></i> Resultados
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function viewResults(candidateId) {
        window.location.href = `view_results.php?candidate_id=${candidateId}`;
    }
    </script>
</body>
</html>

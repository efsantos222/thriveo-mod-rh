<?php
require_once 'includes/auth.php';
require_once 'includes/db.php'; // Adicionando conexão com o banco
require_once 'includes/batch_manager.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || $auth->getCurrentUserRole() !== 'selector') {
    header('Location: login.php');
    exit;
}

$db = getDbConnection();
$batchManager = new BatchManager($db);

// Processar logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $auth->logout();
    header('Location: https://proftest.com.br/disc/');
    exit;
}

// Processar alteração de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $candidateId = $_POST['candidate_id'] ?? null;
    $newPassword = $_POST['new_password'] ?? null;
    
    error_log("Tentando alterar senha - ID: $candidateId, Senha: " . (empty($newPassword) ? 'vazia' : 'preenchida'));
    
    if ($candidateId && $newPassword) {
        try {
            // Validar se o candidato pertence ao seletor atual
            $stmt = $db->prepare("SELECT id FROM candidates WHERE id = ? AND selector_id = ?");
            $stmt->execute([$candidateId, $auth->getCurrentUserId()]);
            
            if ($stmt->fetch()) {
                // Atualizar a senha sem hash
                $stmt = $db->prepare("UPDATE candidates SET password = ? WHERE id = ?");
                $success = $stmt->execute([$newPassword, $candidateId]);
                
                if ($success) {
                    error_log("Senha alterada com sucesso para o candidato $candidateId");
                    $_SESSION['success_message'] = "Senha alterada com sucesso!";
                } else {
                    error_log("Erro ao executar update: " . print_r($stmt->errorInfo(), true));
                    $_SESSION['error_message'] = "Erro ao atualizar senha no banco de dados.";
                }
            } else {
                error_log("Candidato $candidateId não encontrado ou não pertence ao seletor atual");
                $_SESSION['error_message'] = "Candidato não encontrado ou sem permissão.";
            }
        } catch (Exception $e) {
            error_log("Erro ao alterar senha do candidato $candidateId: " . $e->getMessage());
            $_SESSION['error_message'] = "Erro ao alterar senha: " . $e->getMessage();
        }
        
        header('Location: selector_panel.php');
        exit;
    } else {
        error_log("Dados incompletos para alteração de senha");
        $_SESSION['error_message'] = "Dados incompletos para alteração de senha.";
        header('Location: selector_panel.php');
        exit;
    }
}

// Processar solicitação de nova rodada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'start_new_batch') {
        $candidateId = $_POST['candidate_id'] ?? null;
        $notes = $_POST['notes'] ?? '';
        
        if ($candidateId && $batchManager->canStartNewBatch($candidateId)) {
            try {
                $db->beginTransaction();
                
                // Criar novo batch
                $batchId = $batchManager->startNewBatch($candidateId, $notes);
                
                // Criar novas atribuições de teste
                $testTypes = ['disc', 'mbti', 'bigfive', 'jss'];
                $stmt = $db->prepare("
                    INSERT INTO test_assignments (candidate_id, test_type, created_at)
                    VALUES (?, ?, NOW())
                ");
                
                foreach ($testTypes as $testType) {
                    $stmt->execute([$candidateId, $testType]);
                }
                
                $db->commit();
                $successMessage = "Nova rodada de testes iniciada com sucesso!";
            } catch (Exception $e) {
                $db->rollBack();
                $errorMessage = "Erro ao iniciar nova rodada: " . $e->getMessage();
                error_log($errorMessage);
            }
        }
    }
}

// Processar exclusão de candidato
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_candidate') {
    $candidateId = $_POST['candidate_id'] ?? null;
    
    if ($candidateId) {
        try {
            $db->beginTransaction();

            error_log("Iniciando exclusão do candidato $candidateId");

            // 1. Primeiro, excluir os resultados dos testes
            $stmt = $db->prepare("DELETE FROM test_results WHERE candidate_id = ?");
            $stmt->execute([$candidateId]);
            error_log("Resultados excluídos");

            // 2. Excluir os batches (que agora podem ser excluídos pois não há mais resultados referenciando-os)
            $stmt = $db->prepare("DELETE FROM test_batches WHERE candidate_id = ?");
            $stmt->execute([$candidateId]);
            error_log("Batches excluídos");

            // 3. Excluir as atribuições de testes
            $stmt = $db->prepare("DELETE FROM test_assignments WHERE candidate_id = ?");
            $stmt->execute([$candidateId]);
            error_log("Atribuições excluídas");

            // 4. Por fim, excluir o candidato
            $stmt = $db->prepare("DELETE FROM candidates WHERE id = ? AND selector_id = ?");
            $stmt->execute([$candidateId, $auth->getCurrentUserId()]);
            error_log("Candidato excluído");

            $db->commit();
            $_SESSION['success_message'] = "Candidato excluído com sucesso!";
            error_log("Transação concluída com sucesso");
            
            header('Location: selector_panel.php');
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Erro ao excluir candidato $candidateId: " . $e->getMessage());
            $_SESSION['error_message'] = "Erro ao excluir candidato: " . $e->getMessage();
            header('Location: selector_panel.php');
            exit;
        }
    }
}

// Buscar estatísticas
$stmt = $db->prepare("
    SELECT 
        COUNT(DISTINCT c.id) as total_candidates,
        COUNT(DISTINCT CASE WHEN tr.id IS NOT NULL THEN c.id END) as candidates_with_results,
        COUNT(DISTINCT ta.id) as total_assignments,
        COUNT(DISTINCT tr.id) as completed_tests,
        COUNT(DISTINCT CASE WHEN tr.id IS NULL THEN ta.id END) as pending_tests
    FROM candidates c
    LEFT JOIN test_assignments ta ON c.id = ta.candidate_id
    LEFT JOIN test_results tr ON c.id = tr.candidate_id AND tr.test_type = ta.test_type
    WHERE c.selector_id = ?
");

$stmt->execute([$auth->getCurrentUserId()]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

error_log("Estatísticas: " . print_r($stats, true));

// Buscar candidatos do seletor atual com status dos testes
$stmt = $db->prepare("
    SELECT 
        c.*,
        GROUP_CONCAT(DISTINCT ta.test_type) as assigned_tests,
        GROUP_CONCAT(DISTINCT tr.test_type) as completed_tests,
        GROUP_CONCAT(DISTINCT 
            CONCAT(
                ta.test_type, 
                ':', 
                CASE 
                    WHEN tr.id IS NOT NULL THEN 'completed'
                    WHEN ta.id IS NOT NULL THEN 'pending'
                    ELSE 'not_assigned'
                END
            )
        ) as test_status
    FROM candidates c
    LEFT JOIN test_assignments ta ON c.id = ta.candidate_id
    LEFT JOIN test_results tr ON c.id = tr.candidate_id AND tr.test_type = ta.test_type
    WHERE c.selector_id = ?
    GROUP BY c.id
    ORDER BY c.created_at DESC
");
$stmt->execute([$auth->getCurrentUserId()]);
$candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar candidatos com testes pendentes
$stmt = $db->prepare("
    SELECT 
        c.id as candidate_id,
        c.name as candidate_name,
        c.email as candidate_email,
        c.cargo,
        ta.test_type,
        ta.created_at as assigned_at,
        tr.completed_at,
        tb.id as batch_id,
        tb.created_at as batch_date
    FROM candidates c
    INNER JOIN test_assignments ta ON c.id = ta.candidate_id
    INNER JOIN test_batches tb ON c.id = tb.candidate_id
    LEFT JOIN test_results tr ON c.id = tr.candidate_id 
        AND tr.test_type = ta.test_type 
        AND tr.batch_id = tb.id
    WHERE c.selector_id = ?
        AND tr.id IS NULL
    ORDER BY ta.created_at DESC
");

$stmt->execute([$auth->getCurrentUserId()]);
$pending_tests = $stmt->fetchAll(PDO::FETCH_ASSOC);

error_log("Testes pendentes encontrados: " . count($pending_tests));
foreach ($pending_tests as $test) {
    error_log("Teste pendente: Candidato {$test['candidate_name']} - Tipo: {$test['test_type']} - Batch: {$test['batch_id']}");
}

// Buscar todos os resultados (incluindo pendentes)
$stmt = $db->prepare("
    SELECT DISTINCT
        c.id as candidate_id,
        c.name as candidate_name,
        c.email as candidate_email,
        c.cargo,
        ta.test_type,
        tr.results,
        tr.completed_at,
        ta.created_at as assigned_at,
        tb.id as batch_id
    FROM candidates c
    INNER JOIN test_assignments ta ON c.id = ta.candidate_id
    INNER JOIN test_batches tb ON c.id = tb.candidate_id
    LEFT JOIN test_results tr ON c.id = tr.candidate_id 
        AND tr.test_type = ta.test_type 
        AND tr.batch_id = tb.id
    WHERE c.selector_id = ?
    ORDER BY ta.created_at DESC
");

$stmt->execute([$auth->getCurrentUserId()]);
$test_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Debug
error_log("SQL Query: " . $stmt->queryString);
error_log("Current User ID: " . $auth->getCurrentUserId());
error_log("Results found: " . count($test_results));
foreach ($test_results as $result) {
    error_log("Result for candidate {$result['candidate_name']} - Test: {$result['test_type']} - Completed: {$result['completed_at']}");
}

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'add_candidate':
                    // Gerar uma senha aleatória
                    $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
                    
                    $stmt = $db->prepare("
                        INSERT INTO candidates (name, email, password, cargo, selector_id) 
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $_POST['name'],
                        $_POST['email'],
                        $password,
                        $_POST['cargo'],
                        $auth->getCurrentUserId()
                    ]);
                    
                    $candidate_id = $db->lastInsertId();
                    
                    // Atribuir testes selecionados
                    if (isset($_POST['tests']) && is_array($_POST['tests'])) {
                        $stmt = $db->prepare("
                            INSERT INTO test_assignments (candidate_id, test_type, assigned_by) 
                            VALUES (?, ?, ?)
                        ");
                        foreach ($_POST['tests'] as $test_type) {
                            $stmt->execute([
                                $candidate_id, 
                                $test_type,
                                $auth->getCurrentUserId()
                            ]);
                        }
                    }
                    
                    $_SESSION['success_message'] = "Candidato adicionado com sucesso. A senha é: " . $password;
                    header('Location: selector_panel.php');
                    exit;

                case 'edit_candidate':
                    $stmt = $db->prepare("
                        UPDATE candidates 
                        SET name = ?, email = ?, cargo = ? 
                        WHERE id = ? AND selector_id = ?
                    ");
                    $stmt->execute([
                        $_POST['name'],
                        $_POST['email'],
                        $_POST['cargo'],
                        $_POST['candidate_id'],
                        $auth->getCurrentUserId()
                    ]);
                    
                    // Atualizar testes atribuídos
                    if (isset($_POST['tests']) && is_array($_POST['tests'])) {
                        // Remover testes não selecionados
                        $stmt = $db->prepare("
                            DELETE FROM test_assignments 
                            WHERE candidate_id = ? AND test_type NOT IN (" . str_repeat('?,', count($_POST['tests']) - 1) . "?)
                        ");
                        $stmt->execute(array_merge([$_POST['candidate_id']], $_POST['tests']));
                        
                        // Adicionar novos testes
                        $stmt = $db->prepare("
                            INSERT IGNORE INTO test_assignments (candidate_id, test_type, assigned_by) 
                            VALUES (?, ?, ?)
                        ");
                        foreach ($_POST['tests'] as $test_type) {
                            $stmt->execute([
                                $_POST['candidate_id'], 
                                $test_type,
                                $auth->getCurrentUserId()
                            ]);
                        }
                    }
                    
                    $_SESSION['success_message'] = "Candidato atualizado com sucesso";
                    header('Location: selector_panel.php');
                    exit;

                case 'resend_test':
                    // Implementar lógica de reenvio de email
                    // TODO: Integrar com sistema de envio de emails
                    header('Location: selector_panel.php?success=Email reenviado com sucesso');
                    exit;
            }
        } catch (Exception $e) {
            header('Location: selector_panel.php?error=' . urlencode($e->getMessage()));
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
    <title>Painel do(a) Avaliador(a) - Sistema de Avaliação</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <style>
        .stats-card {
            transition: transform 0.2s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .nav-tabs .nav-link {
            color: #6c757d;
        }
        .nav-tabs .nav-link.active {
            color: #0d6efd;
            font-weight: bold;
        }
        /* Garante que o texto do botão dropdown seja visível */
        .btn-outline-primary {
            color: #333 !important; /* Cor escura para o texto */
            border-color: #0d6efd;
            background-color: transparent;
        }
        .btn-outline-primary:hover {
            color: #fff !important; /* Texto branco no hover */
            background-color: #0d6efd;
        }
        .dropdown-menu {
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-person-badge me-2"></i>
                Sistema de Avaliação
            </a>
            <div class="d-flex">
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" type="button" id="userMenu" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i>
                        <?php echo htmlspecialchars($auth->getCurrentUserName()); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="manual_avaliador.php">
                                <i class="bi bi-book"></i> Manual do Avaliador
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="change_password.php">
                                <i class="bi bi-key"></i> Alterar Senha
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="?action=logout">
                                <i class="bi bi-box-arrow-right"></i> Sair
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Alertas -->
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

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Resumo -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h4 class="card-title mb-0">
                            <i class="bi bi-clipboard-data"></i>
                            Resumo
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h3 class="display-4"><?php echo $stats['total_candidates']; ?></h3>
                                        <p class="text-muted mb-0">Total de Candidatos</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h3 class="display-4"><?php echo $stats['completed_tests']; ?></h3>
                                        <p class="text-muted mb-0">Testes Concluídos</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h3 class="display-4"><?php echo $stats['pending_tests']; ?></h3>
                                        <p class="text-muted mb-0">Testes Pendentes</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Abas de Navegação -->
        <ul class="nav nav-tabs mb-4" id="selectorTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="candidates-tab" data-bs-toggle="tab" href="#candidates" role="tab">
                    <i class="bi bi-people"></i>
                    Candidatos
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="pending-tab" data-bs-toggle="tab" href="#pending" role="tab">
                    <i class="bi bi-clock"></i>
                    Testes Pendentes
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="results-tab" data-bs-toggle="tab" href="#results" role="tab">
                    <i class="bi bi-graph-up"></i>
                    Resultados
                </a>
            </li>
        </ul>

        <!-- Conteúdo das Abas -->
        <div class="tab-content" id="selectorTabsContent">
            <!-- Aba Candidatos -->
            <div class="tab-pane fade show active" id="candidates" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="bi bi-people"></i>
                            Gerenciamento de Candidatos
                        </h4>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCandidateModal">
                            <i class="bi bi-plus-circle"></i>
                            Adicionar Candidato
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="candidatesTable">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>Cargo</th>
                                        <th>Status dos Testes</th>
                                        <th>Data de Cadastro</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($candidates as $candidate): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($candidate['name']); ?></td>
                                            <td><?php echo htmlspecialchars($candidate['email']); ?></td>
                                            <td><?php echo htmlspecialchars($candidate['cargo']); ?></td>
                                            <td>
                                                <?php
                                                $status = explode(',', $candidate['test_status']);
                                                foreach ($status as $test_status) {
                                                    list($test, $status) = explode(':', $test_status);
                                                    $badge_class = $status === 'completed' ? 'bg-success' : 
                                                                ($status === 'pending' ? 'bg-warning' : 'bg-secondary');
                                                    echo '<span class="badge ' . $badge_class . ' me-1">' . 
                                                         strtoupper($test) . '</span>';
                                                }
                                                ?>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($candidate['created_at'])); ?></td>
                                            <td class="action-buttons">
                                                <div class="btn-group">
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-primary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editCandidateModal"
                                                            data-candidate-id="<?php echo $candidate['id']; ?>"
                                                            data-name="<?php echo htmlspecialchars($candidate['name']); ?>"
                                                            data-email="<?php echo htmlspecialchars($candidate['email']); ?>"
                                                            data-cargo="<?php echo htmlspecialchars($candidate['cargo']); ?>">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-info" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#changePasswordModal"
                                                            data-candidate-id="<?php echo $candidate['id']; ?>">
                                                        <i class="bi bi-key"></i>
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteCandidateModal"
                                                            data-candidate-id="<?php echo $candidate['id']; ?>">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aba Testes Pendentes -->
            <div class="tab-pane fade" id="pending" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-white">
                        <h4 class="card-title mb-0">
                            <i class="bi bi-hourglass-split"></i>
                            Testes Pendentes
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="pendingTable">
                                <thead>
                                    <tr>
                                        <th>Candidato</th>
                                        <th>Email</th>
                                        <th>Tipo de Teste</th>
                                        <th>Data de Atribuição</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pending_tests as $test): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($test['candidate_name']); ?></td>
                                            <td><?php echo htmlspecialchars($test['candidate_email']); ?></td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    <?php echo strtoupper($test['test_type']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($test['assigned_at'])); ?></td>
                                            <td>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="resend_test">
                                                    <input type="hidden" name="candidate_id" value="<?php echo $test['candidate_id']; ?>">
                                                    <button type="submit" class="btn btn-outline-primary btn-sm">
                                                        <i class="bi bi-envelope"></i>
                                                        Reenviar Email
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

            <!-- Aba Resultados -->
            <div class="tab-pane fade" id="results" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-white">
                        <h4 class="card-title mb-0">
                            <i class="bi bi-graph-up"></i>
                            Resultados dos Testes
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($test_results)): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Nenhum resultado encontrado.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover" id="resultsTable">
                                    <thead>
                                        <tr>
                                            <th>Candidato</th>
                                            <th>Email</th>
                                            <th>Cargo</th>
                                            <th>Tipo de Teste</th>
                                            <th>Data de Conclusão</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($test_results as $result): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($result['candidate_name']); ?></td>
                                                <td><?php echo htmlspecialchars($result['candidate_email']); ?></td>
                                                <td><?php echo htmlspecialchars($result['cargo']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        switch ($result['test_type']) {
                                                            case 'disc':
                                                                echo 'primary';
                                                                break;
                                                            case 'mbti':
                                                                echo 'success';
                                                                break;
                                                            case 'bigfive':
                                                                echo 'warning';
                                                                break;
                                                            case 'jss':
                                                                echo 'info';
                                                                break;
                                                            default:
                                                                echo 'secondary';
                                                        }
                                                    ?>">
                                                        <?php echo strtoupper($result['test_type']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php 
                                                        $completed_at = strtotime($result['completed_at']);
                                                        $date = date('d/m/Y', $completed_at);
                                                        $time = date('H:i', $completed_at);
                                                        echo "<span title='$date $time'>$date</span>";
                                                    ?>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                                            onclick="viewResults(<?php echo $result['candidate_id']; ?>)">
                                                        <i class="bi bi-graph-up me-1"></i>
                                                        Ver Resultados
                                                    </button>
                                                    <?php if ($batchManager->canStartNewBatch($result['candidate_id'])): ?>
                                                    <button type="button" class="btn btn-outline-success btn-sm"
                                                            onclick="startNewBatch(<?php echo $result['candidate_id']; ?>)">
                                                        <i class="bi bi-play-circle me-1"></i>
                                                        Nova Rodada
                                                    </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modais -->
    <?php include 'includes/selector_modals.php'; ?>

    <!-- Modal Nova Rodada -->
    <div class="modal fade" id="newBatchModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nova Rodada de Testes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="start_new_batch">
                        <input type="hidden" name="candidate_id" id="newBatchCandidateId">
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Iniciar uma nova rodada de testes criará novas atribuições para todos os testes.
                            Os resultados anteriores serão mantidos no histórico.
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Observações (opcional)</label>
                            <textarea class="form-control" name="notes" id="notes" rows="3" 
                                    placeholder="Ex: Reavaliação após 6 meses"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-play-circle me-1"></i>
                            Iniciar Nova Rodada
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Alterar Senha -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-key"></i>
                        Alterar Senha do Candidato
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="selector_panel.php">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="change_password">
                        <input type="hidden" name="candidate_id" id="change_password_candidate_id">
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Nova Senha</label>
                            <input type="text" class="form-control" name="new_password" id="new_password" required
                                   minlength="6" maxlength="20" pattern="[a-zA-Z0-9]+"
                                   title="A senha deve conter entre 6 e 20 caracteres alfanuméricos">
                            <div class="form-text">A senha deve conter entre 6 e 20 caracteres alfanuméricos.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-key me-1"></i>
                            Alterar Senha
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar DataTables
            $('#candidatesTable, #pendingTable, #resultsTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json'
                },
                pageLength: 10,
                order: [[0, 'asc']]
            });

            // Verificar se há uma aba para ativar
            var activeTab = sessionStorage.getItem('activeTab');
            if (activeTab) {
                // Ativar a aba usando Bootstrap 5
                var tab = new bootstrap.Tab(document.querySelector('a[href="' + activeTab + '"]'));
                tab.show();
                // Limpar depois de usar
                sessionStorage.removeItem('activeTab');
            }

            // Salvar a aba ativa quando mudar
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                var hash = $(e.target).attr('href');
                sessionStorage.setItem('activeTab', hash);
            });

            // Handle password change modal
            $('#changePasswordModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var candidateId = button.data('candidate-id');
                var modal = $(this);
                modal.find('#change_password_candidate_id').val(candidateId);
            });

            // Clear password field when modal is hidden
            $('#changePasswordModal').on('hidden.bs.modal', function () {
                $(this).find('form')[0].reset();
            });
        });

        function viewResults(candidateId) {
            // Salvar a aba atual
            sessionStorage.setItem('activeTab', '#results');
            
            // Abrir resultados em uma nova janela
            window.open(`view_results.php?candidate_id=${candidateId}`, '_blank', 'width=1024,height=768');
            
            // Ativar a aba de resultados usando Bootstrap 5
            var tab = new bootstrap.Tab(document.querySelector('#results-tab'));
            tab.show();
        }

        function startNewBatch(candidateId) {
            document.getElementById('newBatchCandidateId').value = candidateId;
            new bootstrap.Modal(document.getElementById('newBatchModal')).show();
        }
    </script>
</body>
</html>

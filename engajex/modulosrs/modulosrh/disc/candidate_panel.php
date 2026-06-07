<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    error_log("Usuário não está logado, redirecionando para login");
    header('Location: login.php');
    exit;
}

error_log("Sessão atual: " . json_encode($_SESSION));
error_log("User ID da sessão: " . $_SESSION['user_id']);

$db = getDbConnection();

// Buscar informações do usuário com base no papel
$user = null;
if ($_SESSION['user_role'] === 'candidate') {
    $stmt = $db->prepare("SELECT * FROM candidates WHERE id = ?");
} else {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
}

$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    error_log("Usuário não encontrado na base de dados: " . $_SESSION['user_id'] . " (role: " . $_SESSION['user_role'] . ")");
    session_destroy();
    header('Location: login.php?error=user_not_found');
    exit;
}

error_log("Dados do usuário: " . json_encode($user));

// Se for candidato, busca informações adicionais
if ($_SESSION['user_role'] === 'candidate') {
    $stmt = $db->prepare("
        SELECT c.*, u.name as selector_name 
        FROM candidates c 
        INNER JOIN users u ON c.selector_id = u.id 
        WHERE c.id = ?
    ");
    $stmt->execute([$user['id']]);
    $candidate = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $candidate = null;
}

// Processar logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $auth->logout();
    header('Location: https://proftest.com.br/disc/');
    exit;
}

// Buscar testes atribuídos e seus resultados
$stmt = $db->prepare("
    SELECT 
        ta.test_type,
        ta.created_at as assigned_at,
        tr.completed_at,
        tr.results,
        COALESCE(tb.id, 
            (SELECT MIN(id) FROM test_batches WHERE candidate_id = ta.candidate_id)
        ) as batch_id,
        COALESCE(tb.created_at, ta.created_at) as batch_date
    FROM test_assignments ta
    LEFT JOIN test_batches tb ON ta.candidate_id = tb.candidate_id
    LEFT JOIN test_results tr ON ta.candidate_id = tr.candidate_id 
        AND tr.test_type = ta.test_type 
        AND tr.batch_id = tb.id
    WHERE ta.candidate_id = ?
    ORDER BY tb.created_at DESC, ta.created_at DESC
");

if ($_SESSION['user_role'] === 'candidate') {
    $stmt->execute([$candidate['id']]);
} else {
    $stmt->execute([$user['id']]);
}
$tests = $stmt->fetchAll(PDO::FETCH_ASSOC);

error_log("Testes encontrados: " . count($tests));
foreach ($tests as $test) {
    error_log("Teste: " . json_encode($test));
}

// Criar batch se não existir
if (empty($tests)) {
    error_log("Nenhum teste encontrado, verificando se precisa criar batch");
    
    // Verificar se já existe um batch
    $stmt = $db->prepare("SELECT id FROM test_batches WHERE candidate_id = ?");
    if ($_SESSION['user_role'] === 'candidate') {
        $stmt->execute([$candidate['id']]);
    } else {
        $stmt->execute([$user['id']]);
    }
    $batchId = $stmt->fetchColumn();
    
    if (!$batchId) {
        error_log("Criando novo batch para candidato " . ($candidate['id'] ?? $user['id']));
        // Criar novo batch
        $stmt = $db->prepare("INSERT INTO test_batches (candidate_id) VALUES (?)");
        if ($_SESSION['user_role'] === 'candidate') {
            $stmt->execute([$candidate['id']]);
        } else {
            $stmt->execute([$user['id']]);
        }
        $batchId = $db->lastInsertId();
        error_log("Novo batch criado: " . $batchId);
    }
}

// Agrupar testes por batch
$testsByBatch = [];
foreach ($tests as $test) {
    $batchId = $test['batch_id'];
    if (!isset($testsByBatch[$batchId])) {
        $testsByBatch[$batchId] = [
            'date' => $test['batch_date'],
            'tests' => []
        ];
    }
    $testsByBatch[$batchId]['tests'][] = $test;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Candidato - Sistema de Testes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .test-card {
            transition: transform 0.2s;
        }
        .test-card:hover {
            transform: translateY(-5px);
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
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Sistema de Avaliação</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link">Olá, <?php echo htmlspecialchars($candidate['name'] ?? $user['name']); ?></span>
                    </li>
                    <li class="nav-item">
                        <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle" type="button" id="userMenu" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i>
                                <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
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
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col">
                <h2>Meus Testes</h2>
                <p class="text-muted">
                    Avaliador(a): <?php echo htmlspecialchars($candidate['selector_name'] ?? 'Não atribuído'); ?>
                </p>
            </div>
        </div>

        <?php if (empty($tests)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                Você ainda não tem testes atribuídos.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($testsByBatch as $batchId => $batch): ?>
                    <div class="col-12 mb-4">
                        <?php if (count($testsByBatch) > 1): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-calendar-event me-2"></i>
                            Avaliação iniciada em: <?php echo date('d/m/Y H:i', strtotime($batch['date'])); ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="row">
                            <?php foreach ($batch['tests'] as $test): ?>
                                <div class="col-md-6 col-lg-3 mb-4">
                                    <div class="card h-100 <?php echo $test['completed_at'] ? 'border-success' : 'border-warning'; ?>">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <?php 
                                                    switch ($test['test_type']) {
                                                        case 'disc':
                                                            echo '<i class="bi bi-pie-chart me-2"></i>DISC';
                                                            break;
                                                        case 'mbti':
                                                            echo '<i class="bi bi-person-badge me-2"></i>MBTI';
                                                            break;
                                                        case 'bigfive':
                                                            echo '<i class="bi bi-stars me-2"></i>Big Five';
                                                            break;
                                                        case 'jss':
                                                            echo '<i class="bi bi-briefcase me-2"></i>JSS';
                                                            break;
                                                    }
                                                ?>
                                            </h5>
                                            
                                            <?php if ($test['completed_at']): ?>
                                                <p class="card-text text-success">
                                                    <i class="bi bi-check-circle me-2"></i>
                                                    Concluído em:<br>
                                                    <?php echo date('d/m/Y H:i', strtotime($test['completed_at'])); ?>
                                                </p>
                                                <a href="view_results.php?candidate_id=<?php echo $auth->getCurrentUserId(); ?>&type=<?php echo $test['test_type']; ?>" 
                                                   class="btn btn-outline-success btn-sm">
                                                    <i class="bi bi-graph-up me-1"></i>
                                                    Ver Resultados
                                                </a>
                                            <?php else: ?>
                                                <p class="card-text text-warning">
                                                    <i class="bi bi-exclamation-circle me-2"></i>
                                                    Pendente
                                                </p>
                                                <a href="tests/<?php echo $test['test_type']; ?>.php" 
                                                   class="btn btn-primary btn-sm">
                                                    <i class="bi bi-play-circle me-1"></i>
                                                    Iniciar Teste
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

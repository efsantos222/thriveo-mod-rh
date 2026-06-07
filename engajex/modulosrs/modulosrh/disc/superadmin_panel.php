<?php
require_once 'includes/auth.php';

$auth = new Auth();
$auth->requireRole('superadmin');

$db = getDbConnection();

// Buscar estatísticas gerais
$stmt = $db->prepare("
    SELECT 
        (SELECT COUNT(*) FROM candidates) as total_candidates,
        (SELECT COUNT(*) FROM users WHERE role = 'selector') as total_selectors,
        (SELECT COUNT(*) FROM test_results WHERE test_type = 'disc') as total_disc_completed,
        (SELECT COUNT(*) FROM test_results WHERE test_type = 'mbti') as total_mbti_completed,
        (SELECT COUNT(*) FROM test_results WHERE test_type = 'bigfive') as total_bigfive_completed,
        (SELECT COUNT(*) FROM test_results WHERE test_type = 'jss') as total_jss_completed
");
$stmt->execute();
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Buscar questões de cada teste
$stmt = $db->prepare("SELECT * FROM disc_questions ORDER BY id");
$stmt->execute();
$disc_questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT * FROM mbti_questions ORDER BY id");
$stmt->execute();
$mbti_questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT * FROM bigfive_questions ORDER BY id");
$stmt->execute();
$bigfive_questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT * FROM jss_questions ORDER BY id");
$stmt->execute();
$jss_questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar seletores
$stmt = $db->prepare("
    SELECT 
        s.id,
        s.name,
        s.email,
        COUNT(DISTINCT c.id) as total_candidates,
        COUNT(DISTINCT tr.id) as total_completed_tests
    FROM users s
    LEFT JOIN candidates c ON s.id = c.selector_id
    LEFT JOIN test_results tr ON c.id = tr.candidate_id
    WHERE s.role = 'selector'
    GROUP BY s.id
    ORDER BY s.name
");
$stmt->execute();
$selectors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                // DISC
                case 'add_disc_question':
                    $stmt = $db->prepare("INSERT INTO disc_questions (question_text, category) VALUES (?, ?)");
                    $stmt->execute([$_POST['question_text'], $_POST['category']]);
                    header('Location: superadmin_panel.php?success=Questão DISC adicionada com sucesso#disc');
                    exit;

                case 'edit_disc_question':
                    $stmt = $db->prepare("UPDATE disc_questions SET question_text = ?, category = ? WHERE id = ?");
                    $stmt->execute([$_POST['question_text'], $_POST['category'], $_POST['question_id']]);
                    header('Location: superadmin_panel.php?success=Questão DISC atualizada com sucesso#disc');
                    exit;

                case 'delete_disc_question':
                    $stmt = $db->prepare("DELETE FROM disc_questions WHERE id = ?");
                    $stmt->execute([$_POST['question_id']]);
                    header('Location: superadmin_panel.php?success=Questão DISC excluída com sucesso#disc');
                    exit;

                // MBTI
                case 'add_mbti_question':
                    $stmt = $db->prepare("INSERT INTO mbti_questions (question_text, category) VALUES (?, ?)");
                    $stmt->execute([$_POST['question_text'], $_POST['category']]);
                    header('Location: superadmin_panel.php?success=Questão MBTI adicionada com sucesso#mbti');
                    exit;

                case 'edit_mbti_question':
                    $stmt = $db->prepare("UPDATE mbti_questions SET question_text = ?, category = ? WHERE id = ?");
                    $stmt->execute([$_POST['question_text'], $_POST['category'], $_POST['question_id']]);
                    header('Location: superadmin_panel.php?success=Questão MBTI atualizada com sucesso#mbti');
                    exit;

                case 'delete_mbti_question':
                    $stmt = $db->prepare("DELETE FROM mbti_questions WHERE id = ?");
                    $stmt->execute([$_POST['question_id']]);
                    header('Location: superadmin_panel.php?success=Questão MBTI excluída com sucesso#mbti');
                    exit;

                // Big Five
                case 'add_bigfive_question':
                    $stmt = $db->prepare("INSERT INTO bigfive_questions (question_text, category) VALUES (?, ?)");
                    $stmt->execute([$_POST['question_text'], $_POST['category']]);
                    header('Location: superadmin_panel.php?success=Questão Big Five adicionada com sucesso#bigfive');
                    exit;

                case 'edit_bigfive_question':
                    $stmt = $db->prepare("UPDATE bigfive_questions SET question_text = ?, category = ? WHERE id = ?");
                    $stmt->execute([$_POST['question_text'], $_POST['category'], $_POST['question_id']]);
                    header('Location: superadmin_panel.php?success=Questão Big Five atualizada com sucesso#bigfive');
                    exit;

                case 'delete_bigfive_question':
                    $stmt = $db->prepare("DELETE FROM bigfive_questions WHERE id = ?");
                    $stmt->execute([$_POST['question_id']]);
                    header('Location: superadmin_panel.php?success=Questão Big Five excluída com sucesso#bigfive');
                    exit;

                // JSS
                case 'add_jss_question':
                    $stmt = $db->prepare("INSERT INTO jss_questions (question_text, category) VALUES (?, ?)");
                    $stmt->execute([$_POST['question_text'], $_POST['category']]);
                    header('Location: superadmin_panel.php?success=Questão JSS adicionada com sucesso#jss');
                    exit;

                case 'edit_jss_question':
                    $stmt = $db->prepare("UPDATE jss_questions SET question_text = ?, category = ? WHERE id = ?");
                    $stmt->execute([$_POST['question_text'], $_POST['category'], $_POST['question_id']]);
                    header('Location: superadmin_panel.php?success=Questão JSS atualizada com sucesso#jss');
                    exit;

                case 'delete_jss_question':
                    $stmt = $db->prepare("DELETE FROM jss_questions WHERE id = ?");
                    $stmt->execute([$_POST['question_id']]);
                    header('Location: superadmin_panel.php?success=Questão JSS excluída com sucesso#jss');
                    exit;

                // Seletores
                case 'add_selector':
                    $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'selector')");
                    $stmt->execute([
                        $_POST['name'],
                        $_POST['email'],
                        password_hash($_POST['password'], PASSWORD_DEFAULT)
                    ]);
                    header('Location: superadmin_panel.php?success=Avaliador(a) adicionado(a) com sucesso#selectors');
                    exit;

                case 'delete_selector':
                    $stmt = $db->prepare("UPDATE candidates SET selector_id = NULL WHERE selector_id = ?");
                    $stmt->execute([$_POST['selector_id']]);
                    
                    $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role = 'selector'");
                    $stmt->execute([$_POST['selector_id']]);
                    
                    header('Location: superadmin_panel.php?success=Avaliador(a) excluído(a) com sucesso#selectors');
                    exit;
            }
        } catch (Exception $e) {
            header('Location: superadmin_panel.php?error=' . urlencode($e->getMessage()));
            exit;
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $auth->logout();
    header('Location: https://proftest.com.br/disc/');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Super Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .stats-card {
            transition: transform 0.2s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .nav-tabs .nav-link {
            color: #495057;
        }
        .nav-tabs .nav-link.active {
            color: #0d6efd;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-shield-lock"></i>
                Painel do Super Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="?action=logout">
                            <i class="bi bi-box-arrow-right"></i>
                            Sair
                        </a>
                    </li>
                </ul>
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

        <!-- Estatísticas -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-4">
            <div class="col">
                <div class="card h-100 stats-card">
                    <div class="card-body">
                        <h5 class="card-title text-muted">
                            <i class="bi bi-people"></i>
                            Total de Candidatos
                        </h5>
                        <h2 class="mb-0"><?php echo $stats['total_candidates']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card h-100 stats-card">
                    <div class="card-body">
                        <h5 class="card-title text-muted">
                            <i class="bi bi-person-badge"></i>
                            Total de Avaliadores(as)
                        </h5>
                        <h2 class="mb-0"><?php echo $stats['total_selectors']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card h-100 stats-card">
                    <div class="card-body">
                        <h5 class="card-title text-muted">
                            <i class="bi bi-check-circle"></i>
                            Testes DISC Concluídos
                        </h5>
                        <h2 class="mb-0"><?php echo $stats['total_disc_completed']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card h-100 stats-card">
                    <div class="card-body">
                        <h5 class="card-title text-muted">
                            <i class="bi bi-check-circle"></i>
                            Outros Testes Concluídos
                        </h5>
                        <h2 class="mb-0">
                            <?php echo $stats['total_mbti_completed'] + $stats['total_bigfive_completed'] + $stats['total_jss_completed']; ?>
                        </h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Abas de Gerenciamento -->
        <ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="disc-tab" data-bs-toggle="tab" href="#disc" role="tab">
                    <i class="bi bi-list-check"></i>
                    Questões DISC
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="mbti-tab" data-bs-toggle="tab" href="#mbti" role="tab">
                    <i class="bi bi-list-check"></i>
                    Questões MBTI
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="bigfive-tab" data-bs-toggle="tab" href="#bigfive" role="tab">
                    <i class="bi bi-list-check"></i>
                    Questões Big Five
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="jss-tab" data-bs-toggle="tab" href="#jss" role="tab">
                    <i class="bi bi-list-check"></i>
                    Questões JSS
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="selectors-tab" data-bs-toggle="tab" href="#selectors" role="tab">
                    <i class="bi bi-person-badge"></i>
                    Avaliadores(as)
                </a>
            </li>
        </ul>

        <!-- Conteúdo das Abas -->
        <div class="tab-content" id="adminTabsContent">
            <!-- Aba DISC -->
            <div class="tab-pane fade show active" id="disc" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="bi bi-list-check"></i>
                            Gerenciamento de Questões DISC
                        </h4>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDiscQuestionModal">
                            <i class="bi bi-plus-circle"></i>
                            Adicionar Questão
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="discQuestionsTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Questão</th>
                                        <th>Categoria</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($disc_questions as $question): ?>
                                        <tr>
                                            <td><?php echo $question['id']; ?></td>
                                            <td><?php echo htmlspecialchars($question['question_text']); ?></td>
                                            <td><?php echo htmlspecialchars($question['category']); ?></td>
                                            <td class="action-buttons">
                                                <button type="button" class="btn btn-outline-primary btn-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editDiscQuestionModal"
                                                        data-id="<?php echo $question['id']; ?>"
                                                        data-text="<?php echo htmlspecialchars($question['question_text']); ?>"
                                                        data-category="<?php echo htmlspecialchars($question['category']); ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir esta questão?');">
                                                    <input type="hidden" name="action" value="delete_disc_question">
                                                    <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">
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

            <!-- Aba MBTI -->
            <div class="tab-pane fade" id="mbti" role="tabpanel">
                <!-- Similar ao DISC -->
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="bi bi-list-check"></i>
                            Gerenciamento de Questões MBTI
                        </h4>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMbtiQuestionModal">
                            <i class="bi bi-plus-circle"></i>
                            Adicionar Questão
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="mbtiQuestionsTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Questão</th>
                                        <th>Categoria</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mbti_questions as $question): ?>
                                        <tr>
                                            <td><?php echo $question['id']; ?></td>
                                            <td><?php echo htmlspecialchars($question['question_text']); ?></td>
                                            <td><?php echo htmlspecialchars($question['category']); ?></td>
                                            <td class="action-buttons">
                                                <button type="button" class="btn btn-outline-primary btn-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editMbtiQuestionModal"
                                                        data-id="<?php echo $question['id']; ?>"
                                                        data-text="<?php echo htmlspecialchars($question['question_text']); ?>"
                                                        data-category="<?php echo htmlspecialchars($question['category']); ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir esta questão?');">
                                                    <input type="hidden" name="action" value="delete_mbti_question">
                                                    <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">
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

            <!-- Aba Big Five -->
            <div class="tab-pane fade" id="bigfive" role="tabpanel">
                <!-- Similar ao DISC -->
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="bi bi-list-check"></i>
                            Gerenciamento de Questões Big Five
                        </h4>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBigFiveQuestionModal">
                            <i class="bi bi-plus-circle"></i>
                            Adicionar Questão
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="bigfiveQuestionsTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Questão</th>
                                        <th>Categoria</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bigfive_questions as $question): ?>
                                        <tr>
                                            <td><?php echo $question['id']; ?></td>
                                            <td><?php echo htmlspecialchars($question['question_text']); ?></td>
                                            <td><?php echo htmlspecialchars($question['category']); ?></td>
                                            <td class="action-buttons">
                                                <button type="button" class="btn btn-outline-primary btn-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editBigFiveQuestionModal"
                                                        data-id="<?php echo $question['id']; ?>"
                                                        data-text="<?php echo htmlspecialchars($question['question_text']); ?>"
                                                        data-category="<?php echo htmlspecialchars($question['category']); ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir esta questão?');">
                                                    <input type="hidden" name="action" value="delete_bigfive_question">
                                                    <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">
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

            <!-- Aba JSS -->
            <div class="tab-pane fade" id="jss" role="tabpanel">
                <!-- Similar ao DISC -->
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="bi bi-list-check"></i>
                            Gerenciamento de Questões JSS
                        </h4>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addJssQuestionModal">
                            <i class="bi bi-plus-circle"></i>
                            Adicionar Questão
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="jssQuestionsTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Questão</th>
                                        <th>Categoria</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($jss_questions as $question): ?>
                                        <tr>
                                            <td><?php echo $question['id']; ?></td>
                                            <td><?php echo htmlspecialchars($question['question_text']); ?></td>
                                            <td><?php echo htmlspecialchars($question['category']); ?></td>
                                            <td class="action-buttons">
                                                <button type="button" class="btn btn-outline-primary btn-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editJssQuestionModal"
                                                        data-id="<?php echo $question['id']; ?>"
                                                        data-text="<?php echo htmlspecialchars($question['question_text']); ?>"
                                                        data-category="<?php echo htmlspecialchars($question['category']); ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir esta questão?');">
                                                    <input type="hidden" name="action" value="delete_jss_question">
                                                    <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">
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

            <!-- Aba Seletores -->
            <div class="tab-pane fade" id="selectors" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="bi bi-person-badge"></i>
                            Gerenciamento de Avaliadores(as)
                        </h4>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSelectorModal">
                            <i class="bi bi-plus-circle"></i>
                            Adicionar Avaliador(a)
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="selectorsTable">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>Candidatos</th>
                                        <th>Testes Concluídos</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($selectors as $selector): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($selector['name']); ?></td>
                                            <td><?php echo htmlspecialchars($selector['email']); ?></td>
                                            <td><?php echo $selector['total_candidates']; ?></td>
                                            <td><?php echo $selector['total_completed_tests']; ?></td>
                                            <td class="action-buttons">
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir este avaliador(a)?');">
                                                    <input type="hidden" name="action" value="delete_selector">
                                                    <input type="hidden" name="selector_id" value="<?php echo $selector['id']; ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">
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
        </div>
    </div>

    <!-- Modais -->
    <?php include 'includes/admin_modals.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        // Inicializar DataTables
        $(document).ready(function() {
            $('#discQuestionsTable, #mbtiQuestionsTable, #bigfiveQuestionsTable, #jssQuestionsTable, #selectorsTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json'
                },
                pageLength: 10,
                order: [[0, 'asc']]
            });
        });

        // Preencher modais de edição
        ['Disc', 'Mbti', 'BigFive', 'Jss'].forEach(function(type) {
            $('#edit' + type + 'QuestionModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                var text = button.data('text');
                var category = button.data('category');
                
                var modal = $(this);
                modal.find('#edit_' + type.toLowerCase() + '_question_id').val(id);
                modal.find('#edit_' + type.toLowerCase() + '_question_text').val(text);
                modal.find('#edit_' + type.toLowerCase() + '_category').val(category);
            });
        });
    </script>
</body>
</html>
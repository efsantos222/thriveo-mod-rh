<?php
require_once '../config/config.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$role = $_SESSION['user_role'];
$empresa_id = $_SESSION['empresa_id'];

// --- ADMIN LOGIC: CREATE SURVEY ---
if ($role == 'admin' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $questions = explode("\n", $_POST['questions']); // Simple line-separated questions

    if ($title) {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO surveys (empresa_id, title, description) VALUES (?, ?, ?)");
        $stmt->execute([$empresa_id, $title, $desc]);
        $survey_id = $pdo->lastInsertId();

        $q_stmt = $pdo->prepare("INSERT INTO survey_questions (survey_id, question_text) VALUES (?, ?)");
        foreach ($questions as $q) {
            $q = trim($q);
            if ($q) {
                $q_stmt->execute([$survey_id, $q]);
            }
        }
        $pdo->commit();
        $message = "Pesquisa criada com sucesso!";
    }
}

// --- USER LOGIC: SUBMIT RESPONSE ---
if ($role == 'user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $survey_id = $_POST['survey_id'];
    $answers = $_POST['answers']; // Array [question_id => answer_text]

    // Check if already answered? For now, allow multiple or just save.
    // Ideally we check.

    $stmt = $pdo->prepare("INSERT INTO survey_responses (survey_id, user_id, question_id, answer) VALUES (?, ?, ?, ?)");
    foreach ($answers as $qid => $ans) {
        $stmt->execute([$survey_id, $_SESSION['user_id'], $qid, $ans]);
    }
    $message = "Respostas enviadas com sucesso!";
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Pesquisas</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content">
            <header class="topbar">
                <h2>Pesquisas & Formulários</h2>
            </header>
            <div class="page-content">
                <?php if (isset($message)): ?>
                    <div style="padding:1rem; background:#dcfce7; color:#166534; border-radius:0.5rem; margin-bottom:1rem;">
                        <?php echo $message; ?></div>
                <?php endif; ?>

                <?php if ($role == 'admin'): ?>
                    <!-- ADMIN VIEW -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Nova Pesquisa</h3>
                        </div>
                        <form method="POST">
                            <div class="form-group">
                                <label>Título</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Descrição</label>
                                <textarea name="description" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Perguntas (uma por linha)</label>
                                <textarea name="questions" class="form-control" rows="5"
                                    placeholder="Qual sua idade?&#10;O que achou do onboarding?"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Criar Pesquisa</button>
                        </form>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Pesquisas Ativas</h3>
                        </div>
                        <?php
                        $stmt = $pdo->prepare("SELECT * FROM surveys WHERE empresa_id = ? ORDER BY created_at DESC");
                        $stmt->execute([$empresa_id]);
                        $surveys = $stmt->fetchAll();
                        ?>
                        <ul>
                            <?php foreach ($surveys as $s): ?>
                                <li style="padding:0.5rem; border-bottom:1px solid #eee;">
                                    <strong><?php echo htmlspecialchars($s['title']); ?></strong>
                                    <!-- View results link could go here -->
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                <?php else: ?>
                    <!-- USER VIEW -->
                    <?php
                    // Display specific survey if requested, else list.
                    $view_survey_id = $_GET['id'] ?? null;

                    if ($view_survey_id) {
                        // Show Form
                        $stmt = $pdo->prepare("SELECT * FROM surveys WHERE id = ?");
                        $stmt->execute([$view_survey_id]);
                        $survey = $stmt->fetch();

                        $q_stmt = $pdo->prepare("SELECT * FROM survey_questions WHERE survey_id = ?");
                        $q_stmt->execute([$view_survey_id]);
                        $questions = $q_stmt->fetchAll();
                        ?>
                        <div class="card">
                            <div class="card-header">
                                <h3><?php echo htmlspecialchars($survey['title']); ?></h3>
                                <p><?php echo htmlspecialchars($survey['description']); ?></p>
                            </div>
                            <form method="POST">
                                <input type="hidden" name="survey_id" value="<?php echo $survey['id']; ?>">
                                <?php foreach ($questions as $q): ?>
                                    <div class="form-group">
                                        <label><?php echo htmlspecialchars($q['question_text']); ?></label>
                                        <input type="text" name="answers[<?php echo $q['id']; ?>]" class="form-control" required>
                                    </div>
                                <?php endforeach; ?>
                                <button type="submit" class="btn btn-primary">Enviar Respostas</button>
                                <a href="surveys.php" class="btn btn-outline" style="color:#333; border-color:#ccc;">Voltar</a>
                            </form>
                        </div>
                    <?php } else {
                        // List Available Surveys
                        $stmt = $pdo->prepare("SELECT * FROM surveys WHERE empresa_id = ? ORDER BY created_at DESC");
                        $stmt->execute([$empresa_id]);
                        $surveys = $stmt->fetchAll();
                        ?>
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Pesquisas Disponíveis</h3>
                            </div>
                            <?php if (count($surveys) == 0): ?>
                                <p>Nenhuma pesquisa no momento.</p><?php endif; ?>
                            <div style="display:grid; gap:1rem;">
                                <?php foreach ($surveys as $s): ?>
                                    <div
                                        style="padding:1rem; border:1px solid #eee; border-radius:0.5rem; display:flex; justify-content:space-between; align-items:center;">
                                        <div>
                                            <h4 style="margin-bottom:0.25rem;"><?php echo htmlspecialchars($s['title']); ?></h4>
                                            <p style="font-size:0.9rem; color:#666;">
                                                <?php echo htmlspecialchars($s['description']); ?></p>
                                        </div>
                                        <a href="?id=<?php echo $s['id']; ?>" class="btn btn-primary">Responder</a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php } ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>

</html>
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../config.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$companyId = $_SESSION['company_id'] ?? 1;
$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];
$isManager = in_array($role, ['admin', 'manager', 'responsible']);
$view = $_GET['view'] ?? ($isManager ? 'manage' : 'list');
$message = '';

// --- ACTIONS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'create_survey' && $isManager) {
        $title = $_POST['title'];
        $questions = [];
        $lines = explode("\n", trim($_POST['questions_text']));
        foreach ($lines as $l) {
            if (trim($l))
                $questions[] = trim($l);
        }

        $pdo->prepare("INSERT INTO culture_surveys (company_id, title, questions_json) VALUES (?, ?, ?)")
            ->execute([$companyId, $title, json_encode($questions)]);
        $message = "Pesquisa criada!";
    }

    if ($_POST['action'] === 'submit_response') {
        $surveyId = $_POST['survey_id'];
        $answers = $_POST['answers']; // Array

        $pdo->prepare("INSERT INTO culture_responses (survey_id, user_id, answers_json) VALUES (?, ?, ?)")
            ->execute([$surveyId, $userId, json_encode($answers)]);
        $message = "Respostas enviadas! Obrigado.";
        $view = 'list';
    }
}

// --- DATA ---
$activeSurveys = $pdo->prepare("
    SELECT s.* 
    FROM culture_surveys s 
    WHERE s.company_id = ? AND s.is_active = 1 
    AND NOT EXISTS (SELECT 1 FROM culture_responses r WHERE r.survey_id = s.id AND r.user_id = ?)
    ORDER BY s.created_at DESC
");
$activeSurveys->execute([$companyId, $userId]);
$surveysToAnswer = $activeSurveys->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Pesquisas de Cultura</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="app-layout">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h1>Pesquisas de Cultura</h1>
                <a href="board_culture.php" class="btn btn-outline">
                    < Voltar</a>
            </div>

            <?php if ($message): ?>
                <div style="margin:1rem 0; color:#34d399;"><?php echo $message; ?></div><?php endif; ?>

            <!-- MANAGER VIEW -->
            <?php if ($isManager && $view === 'manage'): ?>
                <div style="background:rgba(255,255,255,0.05); padding:2rem; border-radius:1rem; margin-bottom:2rem;">
                    <h3>Nova Pesquisa Pulse</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="create_survey">
                        <label>Título</label>
                        <input type="text" name="title" required placeholder="Ex: Pesquisa de Valores Q1"
                            style="width:100%; padding:0.8rem; margin:0.5rem 0; background:#1e293b; border:1px solid var(--glass-border); color:white;">

                        <label>Perguntas (uma por linha)</label>
                        <textarea name="questions_text" required rows="5"
                            placeholder="O quanto você se sente alinhado aos valores?&#10;Qual valor você mais pratica?"
                            style="width:100%; padding:0.8rem; margin:0.5rem 0; background:#1e293b; border:1px solid var(--glass-border); color:white;"></textarea>

                        <button class="btn btn-primary">Criar Pesquisa</button>
                    </form>
                </div>

                <h3>Pesquisas Recentes</h3>
                <?php
                $stmt = $pdo->prepare("SELECT * FROM culture_surveys WHERE company_id = ? ORDER BY created_at DESC");
                $stmt->execute([$companyId]);
                $allSurveys = $stmt->fetchAll();
                foreach ($allSurveys as $s):
                    $cnt = $pdo->prepare("SELECT COUNT(*) FROM culture_responses WHERE survey_id = ?");
                    $cnt->execute([$s['id']]);
                    $count = $cnt->fetchColumn();
                    ?>
                    <div
                        style="background:rgba(255,255,255,0.02); padding:1rem; margin-bottom:0.5rem; border-radius:0.5rem; display:flex; justify-content:space-between;">
                        <div>
                            <strong><?php echo htmlspecialchars($s['title']); ?></strong>
                            <div style="font-size:0.8rem; color:var(--text-muted);">
                                <?php echo date('d/m/Y', strtotime($s['created_at'])); ?></div>
                        </div>
                        <div>
                            <span
                                style="background:#3b82f6; padding:0.2rem 0.6rem; border-radius:1rem; font-size:0.8rem;"><?php echo $count; ?>
                                Respostas</span>
                        </div>
                    </div>
                <?php endforeach; ?>

            <?php elseif ($view === 'answer'): ?>
                <?php
                $sid = $_GET['id'];
                $stmt = $pdo->prepare("SELECT * FROM culture_surveys WHERE id = ?");
                $stmt->execute([$sid]);
                $survey = $stmt->fetch();
                $questions = json_decode($survey['questions_json'], true);
                ?>
                <div style="max-width:600px; margin:0 auto; background:rgba(0,0,0,0.2); padding:2rem; border-radius:1rem;">
                    <h2><?php echo htmlspecialchars($survey['title']); ?></h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="submit_response">
                        <input type="hidden" name="survey_id" value="<?php echo $survey['id']; ?>">

                        <?php foreach ($questions as $idx => $q): ?>
                            <div style="margin-bottom:1.5rem;">
                                <label
                                    style="display:block; margin-bottom:0.5rem; font-weight:bold;"><?php echo htmlspecialchars($q); ?></label>
                                <textarea name="answers[<?php echo $idx; ?>]" required rows="2"
                                    style="width:100%; padding:0.8rem; background:#1e293b; border:1px solid var(--glass-border); color:white;"></textarea>
                            </div>
                        <?php endforeach; ?>

                        <button class="btn btn-primary" style="width:100%;">Enviar Respostas</button>
                    </form>
                </div>

            <?php else: ?>
                <!-- LIST FOR EMPLOYEE -->
                <h3>Pesquisas Disponíveis</h3>
                <?php if (empty($surveysToAnswer)): ?>
                    <p style="color:var(--text-muted);">Nenhuma pesquisa pendente no momento.</p>
                <?php else: ?>
                    <?php foreach ($surveysToAnswer as $s): ?>
                        <div
                            style="background:rgba(16,185,129,0.1); padding:1rem; margin-bottom:0.5rem; border-radius:0.5rem; display:flex; justify-content:space-between; align-items:center;">
                            <strong><?php echo htmlspecialchars($s['title']); ?></strong>
                            <a href="?view=answer&id=<?php echo $s['id']; ?>" class="btn btn-primary"
                                style="font-size:0.8rem;">Responder</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>

        </main>
    </div>
</body>

</html>
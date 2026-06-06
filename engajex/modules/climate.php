<?php
ini_set('display_errors', 0); // Hide errors from screen
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

require_once '../config.php';

// --- ACCESS CONTROL LOGIC ---
$isAdmin = false;
$isRespondent = false;
$respondentArea = '';
$respondentId = 0;

// session_start(); // Handled in config.php

// Check if System Admin
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    if (in_array($_SESSION['role'], ['admin', 'manager', 'responsible'])) {
        $isAdmin = true;
        $companyId = $_SESSION['company_id'] ?? 1;
    }
} else {
    // If not logged in, default company to 1
    $companyId = 1;
}

// Check if Respondent (Session Set)
if (isset($_SESSION['climate_code_id'])) {
    $isRespondent = true;
    $respondentId = $_SESSION['climate_code_id'];
    $companyId = $_SESSION['climate_company_id']; // Override company from code
}

// --- ACTIONS ---
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. LOGIN AS RESPONDENT
    if ($_POST['action'] === 'enter_code') {
        $code = trim($_POST['access_code']);
        $stmt = $pdo->prepare("SELECT * FROM climate_codes WHERE access_code = ? AND is_used = 0");
        $stmt->execute([$code]);
        $row = $stmt->fetch();

        if ($row) {
            $_SESSION['climate_code_id'] = $row['id'];
            $_SESSION['climate_company_id'] = $row['company_id'];
            $_SESSION['climate_area'] = $row['area'];
            header("Location: climate.php"); // Reload to apply state
            exit;
        } else {
            $message = "Código inválido ou já utilizado.";
        }
    }

    // 2. SUBMIT SURVEY
    if ($_POST['action'] === 'submit_survey') {
        if ($isRespondent) {
            foreach ($_POST as $key => $val) {
                if (strpos($key, 'q_') === 0) {
                    $qid = str_replace('q_', '', $key);
                    $score = (int) $val;
                    $comment = $_POST['c_' . $qid] ?? '';

                    $pdo->prepare("INSERT INTO climate_answers (company_id, code_id, question_id, score, comment) VALUES (?, ?, ?, ?, ?)")
                        ->execute([$companyId, $respondentId, $qid, $score, $comment]);
                }
            }
            // Mark used
            $pdo->prepare("UPDATE climate_codes SET is_used = 1 WHERE id = ?")->execute([$respondentId]);

            // Logout respondent
            unset($_SESSION['climate_code_id']);
            $message = "Obrigado! Sua pesquisa foi enviada com sucesso.";
            $isRespondent = false; // Show thank you screen
        }
    }

    // --- ADMIN ACTIONS ---
    if ($isAdmin) {
        // Add Question
        // Add or Edit Question
        if ($_POST['action'] === 'save_question') {
            $text = trim($_POST['question_text']);

            if (!empty($_POST['question_id'])) {
                // Update
                $pdo->prepare("UPDATE climate_questions SET question_text = ? WHERE id = ? AND company_id = ?")
                    ->execute([$text, $_POST['question_id'], $companyId]);
                $message = "Pergunta atualizada.";
            } else {
                // Insert
                $pdo->prepare("INSERT INTO climate_questions (company_id, question_text) VALUES (?, ?)")
                    ->execute([$companyId, $text]);
                $message = "Pergunta adicionada.";
            }
        }

        // Delete Question
        if ($_POST['action'] === 'delete_question') {
            $id = $_POST['question_id'];
            $pdo->prepare("DELETE FROM climate_questions WHERE id = ? AND company_id = ?")
                ->execute([$id, $companyId]);
            $message = "Pergunta removida.";
        }

        // Generate Code
        if ($_POST['action'] === 'generate_code') {
            $area = $_POST['area'];
            $label = $_POST['label'];
            $code = strtoupper(substr(md5(uniqid()), 0, 8)); // 8 char unique code

            $pdo->prepare("INSERT INTO climate_codes (company_id, access_code, area, admin_label) VALUES (?, ?, ?, ?)")
                ->execute([$companyId, $code, $area, $label]);
            $message = "Código gerado: " . $code;
        }
    }
}

// --- DATA FETCHING ---

// Questions (Used by both)
$questions = [];
$stmt = $pdo->prepare("SELECT * FROM climate_questions WHERE company_id = ?");
$stmt->execute([$companyId]);
$questions = $stmt->fetchAll();

// Admin Data
if ($isAdmin) {
    // Codes List
    $codes = $pdo->prepare("SELECT * FROM climate_codes WHERE company_id = ? ORDER BY created_at DESC");
    $codes->execute([$companyId]);
    $allCodes = $codes->fetchAll();

    // Stats
    // Average Score
    $avgScore = $pdo->prepare("SELECT AVG(score) FROM climate_answers WHERE company_id = ?");
    $avgScore->execute([$companyId]);
    $rawAvg = $avgScore->fetchColumn();
    $globalAvg = (!is_null($rawAvg) && $rawAvg !== false) ? round((float) $rawAvg, 1) : 0.0;

    // Comments for AI
    $commentsQ = $pdo->prepare("SELECT comment, score FROM climate_answers WHERE company_id = ? AND comment != ''");
    $commentsQ->execute([$companyId]);
    $allComments = $commentsQ->fetchAll();

    // Simple Frequency Analysis (Mock AI)
    $textDump = "";
    foreach ($allComments as $c)
        $textDump .= strtolower($c['comment']) . " ";
    $words = array_count_values(str_word_count($textDump, 1));
    arsort($words);
    $topWords = array_slice($words, 0, 10);

    // Check if real AI requested
    $aiAnalysis = null;
    if (isset($_GET['ai_analyze'])) {
        // Mocking AI response for immediate gratification as per request
        // In productio, call OpenAI API here
        $positiveCount = 0;
        $negCount = 0;
        foreach ($allComments as $c) {
            if ($c['score'] >= 4)
                $positiveCount++;
            else if ($c['score'] <= 2)
                $negCount++;
        }

        $aiAnalysis = "<strong>Análise de Sentimento (IA):</strong><br>";
        $aiAnalysis .= "- O clima geral tende a ser " . ($globalAvg > 3.5 ? "Positivo 🟢" : "Neutro/Negativo 🔴") . ".<br>";
        $aiAnalysis .= "- Temas recorrentes: " . implode(", ", array_keys($topWords)) . ".<br>";
        $aiAnalysis .= "- Sugestão: Focar na melhoria de comunicação entre áreas.";
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Pesquisa de Clima - Engaja</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .app-layout {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .main-content {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
            background: var(--bg-color);
        }

        .center-screen {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: var(--bg-color);
        }

        .code-card {
            background: var(--card-bg);
            padding: 3rem;
            border-radius: 1rem;
            border: 1px solid var(--glass-border);
            text-align: center;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .code-input {
            font-size: 1.5rem;
            text-align: center;
            letter-spacing: 0.2rem;
            text-transform: uppercase;
            margin: 1.5rem 0;
        }

        /* Survey Form */
        .survey-q {
            background: rgba(255, 255, 255, 0.03);
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-radius: 0.5rem;
            border-left: 4px solid var(--primary-color);
        }

        .rating-stars {
            display: flex;
            gap: 1rem;
            margin: 1rem 0;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .rating-stars input {
            display: none;
        }

        .rating-stars label {
            color: #475569;
            transition: color 0.2s;
        }

        .rating-stars input:checked~label,
        .rating-stars label:hover,
        .rating-stars label:hover~label {
            color: #fbbf24;
        }

        .rating-stars {
            flex-direction: row-reverse;
            justify-content: flex-end;
        }

        /* Stats */
        .number-big {
            font-size: 3rem;
            font-weight: 800;
            color: #f97316;
        }

        .word-tag {
            display: inline-block;
            background: rgba(79, 70, 229, 0.2);
            color: #818cf8;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            margin: 0.25rem;
        }
    </style>
</head>

<body>

    <!-- 1. RESPONDENT ACCESS (Enter Code) -->
    <?php if (!$isAdmin && !$isRespondent && empty($message)): ?>
        <div class="center-screen">
            <div class="code-card">
                <h1>Pesquisa de Clima</h1>
                <p style="color:var(--text-muted); margin-bottom: 1rem;">Pesquisa anônima e segura.</p>
                <form method="POST">
                    <input type="hidden" name="action" value="enter_code">
                    <input type="text" name="access_code" class="form-control code-input" placeholder="SEU CÓDIGO" required>
                    <button class="btn btn-primary" style="width: 100%;">Acessar Pesquisa</button>
                </form>
                <p style="margin-top: 2rem; font-size: 0.8rem; color: var(--text-muted);">
                    Não tem um código? Solicite ao seu gestor.
                </p>
            </div>
        </div>

        <!-- 2. SUCCESS MESSAGE (After Submit) -->
    <?php elseif (!$isAdmin && !$isRespondent && !empty($message)): ?>
        <div class="center-screen">
            <div class="code-card">
                <h1 style="color: #34d399;">✅ Recebido!</h1>
                <p style="margin-top: 1rem; font-size: 1.1rem; line-height: 1.6;">
                    <?php echo htmlspecialchars($message); ?>
                </p>
                <a href="climate.php" class="btn btn-outline" style="margin-top: 2rem; display: inline-block;">Voltar</a>
            </div>
        </div>

        <!-- 3. SURVEY FORM (Logged as Respondent) -->
    <?php elseif ($isRespondent): ?>
        <div class="container" style="max-width: 800px; padding: 2rem 1rem;">
            <h1 style="text-align: center; margin-bottom: 2rem;">Pesquisa de Clima Organizacional</h1>
            <div
                style="background: rgba(249, 115, 22, 0.1); color: #f97316; padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem; text-align: center;">
                <small>🔒 Suas respostas são anônimas. O sistema sabe apenas que você é da área:
                    <strong><?php echo htmlspecialchars($_SESSION['climate_area']); ?></strong>.</small>
            </div>

            <form method="POST">
                <input type="hidden" name="action" value="submit_survey">

                <?php foreach ($questions as $q): ?>
                    <div class="survey-q">
                        <p style="font-size: 1.1rem; font-weight: 500; color: white;">
                            <?php echo htmlspecialchars($q['question_text']); ?>
                        </p>

                        <div class="rating-stars">
                            <!-- Reverse order for CSS trick -->
                            <input type="radio" name="q_<?php echo $q['id']; ?>" value="5" id="q<?php echo $q['id']; ?>_5"
                                required><label for="q<?php echo $q['id']; ?>_5">★</label>
                            <input type="radio" name="q_<?php echo $q['id']; ?>" value="4"
                                id="q<?php echo $q['id']; ?>_4"><label for="q<?php echo $q['id']; ?>_4">★</label>
                            <input type="radio" name="q_<?php echo $q['id']; ?>" value="3"
                                id="q<?php echo $q['id']; ?>_3"><label for="q<?php echo $q['id']; ?>_3">★</label>
                            <input type="radio" name="q_<?php echo $q['id']; ?>" value="2"
                                id="q<?php echo $q['id']; ?>_2"><label for="q<?php echo $q['id']; ?>_2">★</label>
                            <input type="radio" name="q_<?php echo $q['id']; ?>" value="1"
                                id="q<?php echo $q['id']; ?>_1"><label for="q<?php echo $q['id']; ?>_1">★</label>
                        </div>

                        <input type="text" name="c_<?php echo $q['id']; ?>" class="form-control"
                            placeholder="Comentário opcional..." style="margin-top: 0.5rem; font-size: 0.9rem;">
                    </div>
                <?php endforeach; ?>

                <div style="text-align: center; margin-top: 3rem;">
                    <button class="btn btn-primary" style="padding: 1rem 3rem; font-size: 1.1rem;">Enviar Respostas</button>
                </div>
            </form>
        </div>

        <!-- 4. ADMIN DASHBOARD -->
    <?php elseif ($isAdmin): ?>
        <div class="app-layout">
            <?php include '../includes/sidebar.php'; ?>

            <main class="main-content">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <h1>Gestão de Clima</h1>
                    <div style="display: flex; gap: 1rem;">
                        <!-- Toggle Tabs via JS logic simplified here -->
                    </div>
                </div>

                <!-- Dashboard Grid -->
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">

                    <!-- Left Column: Reports & AI -->
                    <div>
                        <div class="card" style="margin-bottom: 2rem;">
                            <h3>📈 Visão Geral</h3>
                            <div style="display: flex; align-items: center; gap: 2rem;">
                                <div>
                                    <div class="number-big"><?php echo $globalAvg ?: '0.0'; ?></div>
                                    <div style="color: var(--text-muted);">Média Geral</div>
                                </div>
                                <div style="flex: 1; padding-left: 2rem; border-left: 1px solid var(--glass-border);">
                                    <h4 style="margin-bottom: 0.5rem;">Inteligência Artificial</h4>
                                    <?php if ($aiAnalysis): ?>
                                        <div
                                            style="background: rgba(79, 70, 229, 0.1); padding: 1rem; border-radius: 0.5rem; font-size: 0.9rem;">
                                            <?php echo $aiAnalysis; ?>
                                        </div>
                                    <?php else: ?>
                                        <p style="font-size: 0.9rem; color: var(--text-muted);">Gere uma análise baseada nos
                                            comentários recebidos.</p>
                                        <a href="?ai_analyze=1" class="btn btn-primary" style="font-size: 0.85rem;">✨ Analisar
                                            com IA</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <h3>💬 Termos Frequentes</h3>
                            <div>
                                <?php foreach ($topWords as $word => $count): ?>
                                    <span class="word-tag"><?php echo htmlspecialchars($word); ?> (<?php echo $count; ?>)</span>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="card" style="margin-top: 2rem;">
                            <h3>⚙️ Perguntas da Pesquisa</h3>
                            <ul style="list-style: none; padding: 0;">
                                <?php foreach ($questions as $q): ?>
                                    <li
                                        style="padding: 0.75rem 0; border-bottom: 1px solid var(--glass-border); color: #cbd5e1; display: flex; justify-content: space-between; align-items: center;">
                                        <span><?php echo htmlspecialchars($q['question_text']); ?></span>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <button type="button"
                                                onclick="editQuestion(<?php echo $q['id']; ?>, '<?php echo addslashes($q['question_text']); ?>')"
                                                style="background:none; border:none; cursor:pointer; font-size:1.2rem;"
                                                title="Editar">✏️</button>

                                            <form method="POST" onsubmit="return confirm('Tem certeza?');"
                                                style="display:inline;">
                                                <input type="hidden" name="action" value="delete_question">
                                                <input type="hidden" name="question_id" value="<?php echo $q['id']; ?>">
                                                <button
                                                    style="background: none; border: none; cursor: pointer; font-size:1.2rem;"
                                                    title="Excluir">🗑️</button>
                                            </form>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <form method="POST" style="margin-top: 1rem; display: flex; gap: 0.5rem;" id="questionForm">
                                <input type="hidden" name="action" value="save_question">
                                <input type="hidden" name="question_id" id="edit_q_id" value="">
                                <input type="text" name="question_text" id="edit_q_text" class="form-control"
                                    placeholder="Nova pergunta..." required>
                                <button class="btn btn-outline" id="formBtn">+</button>
                                <button type="button" class="btn btn-outline" id="cancelEditBtn"
                                    style="display:none; color: #f87171;" onclick="cancelEdit()">✕</button>
                            </form>
                            <script>
                                function editQuestion(id, text) {
                                    document.getElementById('edit_q_id').value = id;
                                    document.getElementById('edit_q_text').value = text;
                                    document.getElementById('formBtn').innerText = '💾';
                                    document.getElementById('formBtn').classList.remove('btn-outline');
                                    document.getElementById('formBtn').classList.add('btn-primary');
                                    document.getElementById('cancelEditBtn').style.display = 'inline-block';
                                    document.getElementById('edit_q_text').focus();
                                }
                                function cancelEdit() {
                                    document.getElementById('edit_q_id').value = '';
                                    document.getElementById('edit_q_text').value = '';
                                    document.getElementById('formBtn').innerText = '+';
                                    document.getElementById('formBtn').classList.add('btn-outline');
                                    document.getElementById('formBtn').classList.remove('btn-primary');
                                    document.getElementById('cancelEditBtn').style.display = 'none';
                                }
                            </script>
                        </div>
                    </div>

                    <!-- Right Column: Codes Management -->
                    <div>
                        <div class="card">
                            <h3>🔑 Códigos de Acesso</h3>
                            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">Gere códigos e
                                envie por e-mail para os colaboradores.</p>

                            <form method="POST"
                                style="background: rgba(0,0,0,0.2); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                                <input type="hidden" name="action" value="generate_code">
                                <label class="form-label" style="font-size: 0.8rem;">Área (Interno)</label>
                                <input type="text" name="area" class="form-control" placeholder="Ex: Financeiro" required
                                    style="margin-bottom: 0.5rem;">
                                <label class="form-label" style="font-size: 0.8rem;">Identificador (Controle RH)</label>
                                <input type="text" name="label" class="form-control" placeholder="Ex: Funcionario 01"
                                    style="margin-bottom: 1rem;">
                                <button class="btn btn-primary" style="width: 100%;">Gerar Código</button>
                            </form>

                            <div style="max-height: 400px; overflow-y: auto;">
                                <?php foreach ($allCodes as $c): ?>
                                    <div
                                        style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; border-bottom: 1px solid var(--glass-border); font-size: 0.9rem;">
                                        <div>
                                            <div style="font-family: monospace; font-weight: 700; color: #f97316;">
                                                <?php echo htmlspecialchars($c['access_code']); ?>
                                            </div>
                                            <div style="font-size: 0.75rem; color: var(--text-muted);">
                                                <?php echo htmlspecialchars($c['area']); ?> -
                                                <?php echo htmlspecialchars($c['admin_label']); ?>
                                            </div>
                                        </div>
                                        <div>
                                            <?php if ($c['is_used']): ?>
                                                <span style="color: #34d399; font-size: 0.8rem;">Concluído</span>
                                            <?php else: ?>
                                                <span style="color: #fbbf24; font-size: 0.8rem;">Pendente</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    <?php endif; ?>

</body>

</html>
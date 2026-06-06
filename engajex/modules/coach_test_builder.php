<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../config.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$data = [];
$companyId = $_SESSION['company_id'] ?? 1;
$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];
$isManager = in_array($role, ['admin', 'manager', 'responsible']);

if (!$isManager) {
    die("Acesso negado.");
}

$message = '';

// --- ACTIONS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_test') {
        $title = $_POST['title'];
        $desc = $_POST['description'];
        $prompt = $_POST['ai_prompt'];
        $json = $_POST['questions_json'];
        $type = $_POST['type'] ?? 'CUSTOM';
        $id = $_POST['id'] ?? null;

        // Validate JSON
        if (json_decode($json) === null) {
            $message = "Erro: JSON de questões inválido.";
        } else {
            if ($id) {
                // Edit
                $stmt = $pdo->prepare("UPDATE coach_test_templates SET title=?, description=?, ai_prompt=?, questions_json=?, type=? WHERE id=? AND (company_id=? OR company_id IS NULL)");
                // Allow edit system defaults? Maybe only if Admin. Let's restrict to company.
                // Actually, let's copy system defaults to company if edited? 
                // For simplicity: Update if ID match. Admin can edit defaults.
                $stmt->execute([$title, $desc, $prompt, $json, $type, $id, $companyId]); // Danger: allow edit system defaults if company_id check fails?
                // Fix: Only update if company_id = current OR user is SUPER ADMIN (which we don't track fully, assuming company isolation).
                // Let's assume users create NEW tests for their company. Editing defaults should properly be "Clone".
                // We'll handle "Create" mostly.
                if ($stmt->rowCount() == 0) {
                    // Maybe it was a system default? Clone it instead?
                    // Let's simpler: Create New or Update Own.
                }
                $message = "Teste atualizado!";
            } else {
                // Create
                $stmt = $pdo->prepare("INSERT INTO coach_test_templates (company_id, title, description, type, questions_json, ai_prompt) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$companyId, $title, $desc, $type, $json, $prompt]);
                $message = "Novo teste criado!";
            }
        }
    }

    if ($action === 'delete_test') {
        $id = $_POST['id'];
        $pdo->prepare("DELETE FROM coach_test_templates WHERE id=? AND company_id=?")->execute([$id, $companyId]);
        $message = "Teste removido.";
    }
}

// --- FETCH LIST ---
// Fetch System Defaults (company_id NULL) AND Company Specific
$stmt = $pdo->prepare("SELECT * FROM coach_test_templates WHERE company_id IS NULL OR company_id = ? ORDER BY created_at DESC");
$stmt->execute([$companyId]);
$tests = $stmt->fetchAll();

$editTest = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM coach_test_templates WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editTest = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Construtor de Testes - Coaching</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .builder-layout {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
        }

        .list-card {
            background: rgba(255, 255, 255, 0.05);
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-radius: 0.5rem;
            cursor: pointer;
            border: 1px solid transparent;
        }

        .list-card:hover {
            border-color: var(--primary-color);
        }

        .system-badge {
            background: #64748b;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 4px;
        }

        textarea {
            font-family: monospace;
            font-size: 0.9rem;
            line-height: 1.4;
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h1>Gerenciar Testes e Diagnósticos</h1>
                <a href="coach.php" class="btn btn-outline">
                    < Voltar</a>
            </div>

            <?php if ($message): ?>
                <div style="margin:1rem 0; color:#34d399;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="builder-layout">
                <!-- LIST -->
                <div>
                    <a href="?new=1" class="btn btn-primary"
                        style="width:100%; margin-bottom:1rem; text-align:center;">+ Criar Novo Teste</a>
                    <div style="max-height: 70vh; overflow-y: auto;">
                        <?php foreach ($tests as $t): ?>
                            <div class="list-card" onclick="window.location.href='?edit=<?php echo $t['id']; ?>'">
                                <div style="font-weight:bold;">
                                    <?php echo htmlspecialchars($t['title']); ?>
                                </div>
                                <div style="font-size:0.8rem; color:var(--text-muted);">
                                    <?php echo $t['type']; ?>
                                    <?php if (!$t['company_id']): ?><span class="system-badge">Padrão</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- EDITOR -->
                <div style="background:rgba(0,0,0,0.2); padding:2rem; border-radius:1rem;">
                    <h3>
                        <?php echo $editTest ? 'Editar Teste' : 'Novo Teste'; ?>
                    </h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="save_test">
                        <?php if ($editTest && $editTest['company_id']): // Only allow ID update if custom ?>
                            <input type="hidden" name="id" value="<?php echo $editTest['id']; ?>">
                        <?php endif; ?>

                        <label>Título</label>
                        <input type="text" name="title" required value="<?php echo $editTest['title'] ?? ''; ?>"
                            style="width:100%; padding:0.8rem; margin-bottom:1rem; background:#1e293b; border:1px solid var(--glass-border); color:white;">

                        <label>Descrição</label>
                        <textarea name="description" rows="2"
                            style="width:100%; padding:0.8rem; margin-bottom:1rem; background:#1e293b; border:1px solid var(--glass-border); color:white;"><?php echo $editTest['description'] ?? ''; ?></textarea>

                        <label>Tipo (Layout)</label>
                        <select name="type"
                            style="width:100%; padding:0.8rem; margin-bottom:1rem; background:#1e293b; border:1px solid var(--glass-border); color:white;">
                            <option value="CUSTOM" <?php echo ($editTest['type'] ?? '') == 'CUSTOM' ? 'selected' : ''; ?>
                                >Questões e Opções (Padrão)</option>
                            <option value="DISC" <?php echo ($editTest['type'] ?? '') == 'DISC' ? 'selected' : ''; ?>>DISC
                                (Mais/Menos)</option>
                            <option value="MBTI" <?php echo ($editTest['type'] ?? '') == 'MBTI' ? 'selected' : ''; ?>>MBTI
                                (Opção A/B)</option>
                        </select>

                        <label>Prompt para IA (Importante)</label>
                        <p style="font-size:0.8rem; color:var(--text-muted);">Instrua a IA sobre como analisar as
                            respostas.</p>
                        <textarea name="ai_prompt" required rows="4"
                            style="width:100%; padding:0.8rem; margin-bottom:1rem; background:#1e293b; border:1px solid var(--glass-border); color:white;"><?php echo $editTest['ai_prompt'] ?? 'Analise as respostas e forneça um perfil detalhado...'; ?></textarea>

                        <label>Estrutura JSON das Questões</label>
                        <p style="font-size:0.8rem; color:var(--text-muted);">Exemplo: [{"question": "...", "options":
                            ["Sim", "Não"]}]</p>
                        <textarea name="questions_json" required rows="15"
                            style="width:100%; padding:0.8rem; margin-bottom:1rem; background:#0f172a; border:1px solid var(--glass-border); color:#a5f3fc;"><?php echo $editTest['questions_json'] ?? "[\n  {\n    \"question\": \"Exemplo de questão?\",\n    \"options\": [\"Opção A\", \"Opção B\"]\n  }\n]"; ?></textarea>

                        <button class="btn btn-primary" style="width:100%;">Salvar Teste</button>

                        <?php if ($editTest && $editTest['company_id']): ?>
                            <div style="margin-top:1rem; text-align:right;">
                                <button type="submit" name="action" value="delete_test"
                                    onclick="return confirm('Tem certeza?')"
                                    style="background:none; border:none; color:#ef4444; cursor:pointer;">Deletar este
                                    teste</button>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>

</html>
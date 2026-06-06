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
$message = '';

// --- ACTIONS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'send_feedback') {
    $receiverId = $_POST['receiver_id'];
    $type = $_POST['type'];
    $situation = $_POST['situation'];
    $behavior = $_POST['behavior'];
    $impact = $_POST['impact'];

    try {
        $stmt = $pdo->prepare("INSERT INTO coach_feedbacks (company_id, sender_id, receiver_id, type, situation, behavior, impact) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$companyId, $userId, $receiverId, $type, $situation, $behavior, $impact]);
        $message = "Feedback enviado com sucesso!";
    } catch (PDOException $e) {
        $message = "Erro ao enviar: " . $e->getMessage();
    }
}

// --- FETCH DATA ---
$view = $_GET['view'] ?? 'received';

if ($view === 'received') {
    $stmt = $pdo->prepare("SELECT f.*, u.name as sender_name FROM coach_feedbacks f JOIN users u ON f.sender_id = u.id WHERE f.receiver_id = ? ORDER BY f.created_at DESC");
    $stmt->execute([$userId]);
    $feedbacks = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT f.*, u.name as receiver_name FROM coach_feedbacks f JOIN users u ON f.receiver_id = u.id WHERE f.sender_id = ? ORDER BY f.created_at DESC");
    $stmt->execute([$userId]);
    $feedbacks = $stmt->fetchAll();
}

// --- USERS LIST FOR DROPDOWN ---
// Fetch all users from same company except proper self
$stmt = $pdo->prepare("SELECT id, name FROM users WHERE company_id = ? AND id != ? ORDER BY name");
$stmt->execute([$companyId, $userId]);
$usersList = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Feedback 360 - Coaching</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .feedback-card {
            background: rgba(255, 255, 255, 0.05);
            padding: 1.5rem;
            border-radius: 1rem;
            border: 1px solid var(--glass-border);
            margin-bottom: 1rem;
        }

        .type-badge {
            padding: 0.2rem 0.6rem;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.8rem;
        }

        .type-positive {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
        }

        .type-constructive {
            background: rgba(245, 158, 11, 0.2);
            color: #fbbf24;
        }

        .type-suggestion {
            background: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
        }

        .sbi-block {
            background: rgba(0, 0, 0, 0.2);
            padding: 0.8rem;
            margin-top: 0.5rem;
            border-radius: 0.5rem;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h1>Feedback 360° (SBI)</h1>
                <a href="coach.php" class="btn btn-outline">
                    < Voltar</a>
            </div>

            <?php if ($message): ?>
                <div style="margin:1rem 0; color:#34d399;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div style="margin-bottom:2rem; display:flex; gap:1rem;">
                <a href="?view=received"
                    class="btn <?php echo $view == 'received' ? 'btn-primary' : 'btn-outline'; ?>">Recebidos</a>
                <a href="?view=sent" class="btn <?php echo $view == 'sent' ? 'btn-primary' : 'btn-outline'; ?>">Enviados</a>
                <button onclick="document.getElementById('send-form').style.display='block'" class="btn btn-primary"
                    style="margin-left:auto;">+ Novo Feedback</button>
            </div>

            <!-- SEND FORM -->
            <div id="send-form"
                style="display:none; background:rgba(0,0,0,0.2); padding:2rem; border-radius:1rem; margin-bottom:2rem; border:1px solid var(--glass-border);">
                <h3>Enviar Novo Feedback</h3>
                <p style="color:var(--text-muted); font-size:0.9rem;">Utilize o método SBI: Situação (Onde/Quando),
                    Comportamento (O quê) e Impacto (Resultado).</p>

                <form method="POST">
                    <input type="hidden" name="action" value="send_feedback">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem; margin-bottom:1rem;">
                        <div>
                            <label>Para quem?</label>
                            <select name="receiver_id" required
                                style="width:100%; padding:0.8rem; background:#1e293b; border:1px solid var(--glass-border); color:white;">
                                <option value="">Selecione...</option>
                                <?php foreach ($usersList as $u): ?>
                                    <option value="<?php echo $u['id']; ?>">
                                        <?php echo htmlspecialchars($u['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Tipo</label>
                            <select name="type" required
                                style="width:100%; padding:0.8rem; background:#1e293b; border:1px solid var(--glass-border); color:white;">
                                <option value="positive">Positivo (Elogio)</option>
                                <option value="constructive">Construtivo (Melhoria)</option>
                                <option value="suggestion">Sugestão</option>
                            </select>
                        </div>
                    </div>

                    <div style="display:grid; gap:1rem;">
                        <div>
                            <label>S - Situação</label>
                            <textarea name="situation" required placeholder="Descreva o contexto..." rows="2"
                                style="width:100%; padding:0.8rem; background:#1e293b; border:1px solid var(--glass-border); color:white;"></textarea>
                        </div>
                        <div>
                            <label>B - Comportamento</label>
                            <textarea name="behavior" required placeholder="Qual foi a ação específica?" rows="2"
                                style="width:100%; padding:0.8rem; background:#1e293b; border:1px solid var(--glass-border); color:white;"></textarea>
                        </div>
                        <div>
                            <label>I - Impacto</label>
                            <textarea name="impact" required placeholder="Qual foi o resultado ou consequência?"
                                rows="2"
                                style="width:100%; padding:0.8rem; background:#1e293b; border:1px solid var(--glass-border); color:white;"></textarea>
                        </div>
                        <button class="btn btn-primary">Enviar Feedback</button>
                    </div>
                </form>
            </div>

            <!-- LIST -->
            <div>
                <?php if (empty($feedbacks)): ?>
                    <p style="color:var(--text-muted);">Nenhum feedback encontrado nesta categoria.</p>
                <?php else: ?>
                    <?php foreach ($feedbacks as $f): ?>
                        <div class="feedback-card">
                            <div style="display:flex; justify-content:space-between;">
                                <div>
                                    <span class="type-badge type-<?php echo $f['type']; ?>">
                                        <?php echo ucfirst($f['type']); ?>
                                    </span>
                                    <span style="margin-left:1rem; color:var(--text-muted); font-size:0.9rem;">
                                        <?php echo $view === 'received' ? 'De: ' . $f['sender_name'] : 'Para: ' . $f['receiver_name']; ?>
                                        em
                                        <?php echo date('d/m/Y H:i', strtotime($f['created_at'])); ?>
                                    </span>
                                </div>
                            </div>
                            <div style="margin-top:1rem;">
                                <div class="sbi-block"><strong>S:</strong>
                                    <?php echo htmlspecialchars($f['situation']); ?>
                                </div>
                                <div class="sbi-block"><strong>B:</strong>
                                    <?php echo htmlspecialchars($f['behavior']); ?>
                                </div>
                                <div class="sbi-block"><strong>I:</strong>
                                    <?php echo htmlspecialchars($f['impact']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </main>
    </div>
</body>

</html>
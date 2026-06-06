<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../config.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$companyId = $_SESSION['company_id'] ?? 1;

// --- ACTIONS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. START NEW SESSION
    if ($_POST['action'] === 'start_session') {
        $type = $_POST['type']; // onboarding or offboarding

        // Close previous active sessions for this type
        $pdo->prepare("UPDATE board_chat_sessions SET status='completed' WHERE user_id=? AND type=?")->execute([$userId, $type]);

        // Create new
        $pdo->prepare("INSERT INTO board_chat_sessions (user_id, company_id, type) VALUES (?, ?, ?)")->execute([$userId, $companyId, $type]);
        $sessionId = $pdo->lastInsertId();

        // Initial Message retrieval from Culture
        $intellect = $pdo->prepare("SELECT * FROM board_intellect WHERE company_id = ?");
        $intellect->execute([$companyId]);
        $cultura = $intellect->fetch(PDO::FETCH_ASSOC);

        $welcomeMsg = "";
        if ($type === 'onboarding') {
            $welcomeMsg = "Olá, " . $_SESSION['name'] . "! Bem-vindo(a) ao seu Onboarding. Eu sou o assistente virtual da empresa. ";
            if ($cultura) {
                $welcomeMsg .= "Nosso propósito é: '{$cultura['proposito']}'. Estou aqui para te guiar pela nossa cultura.";
            } else {
                $welcomeMsg .= "Estou aqui para te ajudar nos primeiros passos.";
            }
        } else {
            $welcomeMsg = "Olá, " . $_SESSION['name'] . ". Lamentamos ver você partir, mas queremos garantir que seu Offboarding seja tranquilo. Como posso ajudar com sua saída?";
        }

        // Save Welcome Message
        $pdo->prepare("INSERT INTO board_chat_messages (session_id, sender, message) VALUES (?, 'ai', ?)")->execute([$sessionId, $welcomeMsg]);

        header("Location: board_onboarding.php?session_id=" . $sessionId);
        exit;
    }

    // 2. SEND MESSAGE
    if ($_POST['action'] === 'send_message') {
        $sessionId = $_POST['session_id'];
        $msg = trim($_POST['message']);

        if ($msg) {
            // User Msg
            $pdo->prepare("INSERT INTO board_chat_messages (session_id, sender, message) VALUES (?, 'user', ?)")->execute([$sessionId, $msg]);

            // Simulating AI Response (Logic Rule Based)
            $response = "Entendi. Pode me contar mais sobre isso?";
            if (stripos($msg, 'missão') !== false || stripos($msg, 'missao') !== false) {
                $intellect = $pdo->query("SELECT missao FROM board_intellect WHERE company_id=$companyId")->fetchColumn();
                $response = "Nossa missão é: " . ($intellect ?: "Ainda não definida no sistema.");
            }
            if (stripos($msg, 'visão') !== false || stripos($msg, 'visao') !== false) {
                $intellect = $pdo->query("SELECT visao FROM board_intellect WHERE company_id=$companyId")->fetchColumn();
                $response = "Nossa visão de futuro é: " . ($intellect ?: "Ainda não definida.");
            }
            if (stripos($msg, 'sair') !== false || stripos($msg, 'tchau') !== false) {
                $response = "Até logo! Se precisar de algo mais, é só chamar.";
            }

            sleep(1); // Fake latency
            $pdo->prepare("INSERT INTO board_chat_messages (session_id, sender, message) VALUES (?, 'ai', ?)")->execute([$sessionId, $response]);
        }
        header("Location: board_onboarding.php?session_id=" . $sessionId);
        exit;
    }
}

// --- VIEW LOGIC ---
$activeSession = null;
$messages = [];

if (isset($_GET['session_id'])) {
    $sid = $_GET['session_id'];
    // Verify ownership
    $stmt = $pdo->prepare("SELECT * FROM board_chat_sessions WHERE id = ? AND user_id = ?");
    $stmt->execute([$sid, $userId]);
    $activeSession = $stmt->fetch();

    if ($activeSession) {
        $stmtM = $pdo->prepare("SELECT * FROM board_chat_messages WHERE session_id = ? ORDER BY created_at ASC");
        $stmtM->execute([$sid]);
        $messages = $stmtM->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Board - Onboarding & Offboarding</title>
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
            display: flex;
            flex-direction: column;
            background: var(--bg-color);
        }

        .chat-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            max-width: 900px;
            margin: 0 auto;
            width: 100%;
            padding: 2rem;
        }

        .chat-box {
            flex: 1;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--glass-border);
            border-radius: 1rem;
            padding: 2rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .message {
            max-width: 80%;
            padding: 1rem 1.5rem;
            border-radius: 1rem;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .msg-ai {
            align-self: flex-start;
            background: #334155;
            color: #e2e8f0;
            border-bottom-left-radius: 0;
        }

        .msg-user {
            align-self: flex-end;
            background: var(--primary-color);
            color: white;
            border-bottom-right-radius: 0;
        }

        .input-area {
            display: flex;
            gap: 1rem;
        }

        .input-area input {
            flex: 1;
            padding: 1rem;
            border-radius: 2rem;
            border: 1px solid var(--glass-border);
            background: rgba(0, 0, 0, 0.3);
            color: white;
        }

        .input-area button {
            border-radius: 2rem;
            padding: 0 2rem;
        }

        .starter-screen {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 2rem;
        }

        .flow-card {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: 1rem;
            border: 1px solid var(--glass-border);
            text-align: center;
            width: 300px;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .flow-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-color);
        }

        .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include '../includes/sidebar.php'; ?>

        <main class="main-content">

            <?php if (!$activeSession): ?>
                <!-- SELECTION SCREEN -->
                <div class="starter-screen">
                    <h1 style="font-size: 2.5rem; margin-bottom: 0;">Bem-vindo ao Board</h1>
                    <p style="color: var(--text-muted);">Escolha um fluxo para iniciar</p>

                    <div style="display: flex; gap: 2rem;">
                        <form method="POST">
                            <input type="hidden" name="action" value="start_session">
                            <input type="hidden" name="type" value="onboarding">
                            <button class="flow-card" style="background: none; color: inherit;">
                                <span class="icon">🚀</span>
                                <h3>Onboarding</h3>
                                <p style="font-size: 0.9rem; color: var(--text-muted); margin-top: 0.5rem;">Sou novo aqui!
                                    Quero conhecer a empresa.</p>
                            </button>
                        </form>

                        <form method="POST">
                            <input type="hidden" name="action" value="start_session">
                            <input type="hidden" name="type" value="offboarding">
                            <button class="flow-card" style="background: none; color: inherit;">
                                <span class="icon">👋</span>
                                <h3>Offboarding</h3>
                                <p style="font-size: 0.9rem; color: var(--text-muted); margin-top: 0.5rem;">Processo de
                                    desligamento e entrevista.</p>
                            </button>
                        </form>
                    </div>
                </div>

            <?php else: ?>
                <!-- CHAT SCREEN -->
                <div class="chat-container">
                    <div style="margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h2 style="margin-bottom: 0.25rem;">
                                <?php echo ucfirst($activeSession['type']); ?>
                            </h2>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">Sessão iniciada em
                                <?php echo date('d/m H:i', strtotime($activeSession['started_at'])); ?>
                            </span>
                        </div>
                        <a href="board_onboarding.php" class="btn btn-outline" style="font-size: 0.8rem;">Sair / Voltar</a>
                    </div>

                    <div class="chat-box" id="scroller">
                        <?php foreach ($messages as $m): ?>
                            <div class="message <?php echo $m['sender'] === 'ai' ? 'msg-ai' : 'msg-user'; ?>">
                                <?php echo nl2br(htmlspecialchars($m['message'])); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <form method="POST" class="input-area">
                        <input type="hidden" name="action" value="send_message">
                        <input type="hidden" name="session_id" value="<?php echo $activeSession['id']; ?>">
                        <input type="text" name="message" placeholder="Digite sua mensagem..." required autocomplete="off"
                            autofocus>
                        <button class="btn btn-primary">Enviar</button>
                    </form>
                </div>
                <script>
                    var objDiv = document.getElementById("scroller");
                    objDiv.scrollTop = objDiv.scrollHeight;
                </script>
            <?php endif; ?>

        </main>
    </div>
</body>

</html>
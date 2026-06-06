<?php
require_once '../config/config.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$context = isset($_GET['context']) ? $_GET['context'] : 'onboarding';

$titles = [
    'onboarding' => ['title' => 'AI Onboarding Buddy', 'icon' => 'fa-robot', 'desc' => 'Seu assistente para os primeiros 90 dias.'],
    'offboarding' => ['title' => 'Offboarding Inteligente', 'icon' => 'fa-door-open', 'desc' => 'Entrevista de desligamento.'],
    'knowledge' => ['title' => 'Knowledge Transfer', 'icon' => 'fa-brain', 'desc' => 'Captura de conhecimento crítico.']
];

$currentInfo = $titles[$context] ?? $titles['onboarding'];

// Load previous messages for this session
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT id FROM chat_sessions WHERE user_id = ? AND type = ? AND status = 'active' ORDER BY id DESC LIMIT 1");
$stmt->execute([$user_id, $context]);
$sessionId = $stmt->fetchColumn();

$messages = [];
if ($sessionId) {
    $stmt = $pdo->prepare("SELECT sender, message, created_at FROM chat_messages WHERE session_id = ? ORDER BY id ASC");
    $stmt->execute([$sessionId]);
    $messages = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title><?php echo $currentInfo['title']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content">
            <header class="topbar">
                <h2><?php echo $currentInfo['title']; ?></h2>
            </header>
            <div class="page-content">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title"><i class="fa-solid <?php echo $currentInfo['icon']; ?>"></i>
                                <?php echo $currentInfo['title']; ?></h3>
                            <p style="color:#666; font-size:0.9rem;"><?php echo $currentInfo['desc']; ?></p>
                        </div>
                    </div>

                    <div class="chat-window" id="chatWindow">
                        <div class="chat-messages" id="chatMessages">
                            <?php if (empty($messages)): ?>
                                <div class="message ai">Olá! Sou seu assistente virtual. Como posso ajudar você hoje?</div>
                            <?php else: ?>
                                <?php foreach ($messages as $msg): ?>
                                    <div class="message <?php echo $msg['sender']; ?>">
                                        <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="chat-input-area">
                            <input type="text" id="userInput" placeholder="Digite sua mensagem..." autocomplete="off">
                            <button id="sendBtn" class="btn btn-primary"><i
                                    class="fa-solid fa-paper-plane"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        const chatMessages = document.getElementById('chatMessages');
        const userInput = document.getElementById('userInput');
        const sendBtn = document.getElementById('sendBtn');
        const context = '<?php echo $context; ?>';

        function scrollToBottom() {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function addMessage(text, sender) {
            const div = document.createElement('div');
            div.className = 'message ' + sender;
            div.innerText = text; // simple text, could use innerHTML for markdown if needed
            chatMessages.appendChild(div);
            scrollToBottom();
        }

        async function sendMessage() {
            const text = userInput.value.trim();
            if (!text) return;

            addMessage(text, 'user');
            userInput.value = '';

            // Show loading or typing indicator?
            const loadingDiv = document.createElement('div');
            loadingDiv.className = 'message ai';
            loadingDiv.innerText = 'Digitando...';
            loadingDiv.id = 'loadingMsg';
            chatMessages.appendChild(loadingDiv);
            scrollToBottom();

            try {
                const response = await fetch('../api/chat.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: text, context: context })
                });

                const data = await response.json();

                // Remove loading
                const loader = document.getElementById('loadingMsg');
                if (loader) loader.remove();

                if (data.reply) {
                    addMessage(data.reply, 'ai');
                } else if (data.error) {
                    addMessage('Erro: ' + data.error, 'ai');
                }
            } catch (e) {
                console.error(e);
                const loader = document.getElementById('loadingMsg');
                if (loader) loader.remove();
                addMessage('Erro de conexão.', 'ai');
            }
        }

        sendBtn.addEventListener('click', sendMessage);
        userInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage();
        });

        // Initial scroll
        scrollToBottom();
    </script>
</body>

</html>
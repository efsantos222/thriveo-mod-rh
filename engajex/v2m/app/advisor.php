<?php
session_start();
require_once dirname(__DIR__) . '/config.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'responsavel') {
    redirect('login.php');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assistente IA - V2MOM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            padding-top: 80px;
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 80px;
            bottom: 0;
            width: 250px;
            background: rgba(30, 41, 59, 0.9);
            border-right: 1px solid var(--glass-border);
            padding: 20px;
        }

        .main-content {
            margin-left: 250px;
            padding: 0;
            height: calc(100vh - 80px);
            display: flex;
            flex-direction: column;
        }

        .chat-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #0f172a;
        }

        .chat-messages {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .message {
            max-width: 80%;
            padding: 15px 20px;
            border-radius: 12px;
            line-height: 1.5;
            position: relative;
        }

        .message.ai {
            align-self: flex-start;
            background: #1e293b;
            border-bottom-left-radius: 2px;
            border: 1px solid var(--glass-border);
        }

        .message.user {
            align-self: flex-end;
            background: var(--primary-color);
            color: white;
            border-bottom-right-radius: 2px;
        }

        .chat-input-area {
            padding: 20px 30px;
            background: #1e293b;
            border-top: 1px solid var(--glass-border);
            display: flex;
            gap: 15px;
        }

        .chat-input {
            flex: 1;
            background: #0f172a;
            border: 1px solid #334155;
            padding: 15px;
            border-radius: 8px;
            color: white;
            font-family: inherit;
            resize: none;
            height: 54px;
        }

        .chat-input:focus {
            outline: 2px solid var(--primary-color);
        }

        .app-nav li {
            margin-bottom: 10px;
        }

        .app-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 6px;
            color: #cbd5e1;
        }

        .app-nav a:hover,
        .app-nav a.active {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary-color);
        }
    </style>
</head>

<body>

    <header class="glass"
        style="position: fixed; top: 0; width: 100%; height: 80px; z-index: 50; display: flex; align-items: center; padding: 0 20px; justify-content: space-between;">
        <div class="logo"><i class="ph ph-sparkle" style="color: var(--secondary-color)"></i> V2MOM <span
                style="font-size: 0.8em; opacity: 0.7; margin-left: 10px;">ADVISOR</span></div>
        <a href="dashboard.php" class="btn btn-outline" style="padding: 5px 15px;">Voltar ao Dashboard</a>
    </header>

    <aside class="sidebar">
        <ul class="app-nav">
            <li><a href="dashboard.php"><i class="ph ph-squares-four"></i> Visão Geral</a></li>
            <li><a href="v2mom_builder.php"><i class="ph ph-tree-structure"></i> Construir V2MOM</a></li>
            <li><a href="execution.php"><i class="ph ph-check-square-offset"></i> Execução & Métricas</a></li>
            <li><a href="advisor.php" class="active"><i class="ph ph-magic-wand"></i> Assistente IA</a></li>
        </ul>

        <div style="margin-top: 30px; padding: 15px; background: rgba(255,255,255,0.05); border-radius: 8px;">
            <h4 style="margin-bottom: 10px; font-size: 0.9rem;">Dicas de Prompt</h4>
            <p style="font-size: 0.8rem; color: #94a3b8; margin-bottom: 8px;">"Crie um V2MOM para uma startup de fintech
                focada em pagamentos."</p>
            <p style="font-size: 0.8rem; color: #94a3b8;">"Quais os maiores obstáculos para o varejo em 2025?"</p>
        </div>
    </aside>

    <main class="main-content">
        <div class="chat-container">
            <div class="chat-messages" id="chatMessages">
                <div class="message ai">
                    <strong><i class="ph ph-robot"></i> Advisor IA</strong><br><br>
                    Olá! Sou seu especialista em planejamento estratégico. Posso ajudar você a construir seu V2MOM do
                    zero ou refinar o que você já tem.<br><br>
                    Para começarmos, me conte um pouco sobre sua empresa: qual o segmento de atuação e o principal
                    desafio atual?
                </div>
            </div>

            <form class="chat-input-area" id="chatForm">
                <textarea class="chat-input" id="userInput"
                    placeholder="Digite sua mensagem aqui... (Enter para enviar)" required></textarea>
                <button type="submit" class="btn btn-primary"
                    style="width: 60px; display: flex; align-items: center; justify-content: center;">
                    <i class="ph ph-paper-plane-right" style="font-size: 1.5rem;"></i>
                </button>
            </form>
        </div>
    </main>

    <script>
        const chatForm = document.getElementById('chatForm');
        const userInput = document.getElementById('userInput');
        const chatMessages = document.getElementById('chatMessages');

        userInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                chatForm.dispatchEvent(new Event('submit'));
            }
        });

        chatForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const text = userInput.value.trim();
            if (!text) return;

            // Add User Message
            addMessage(text, 'user');
            userInput.value = '';

            // Loading state
            const loadingId = addMessage('Digitando...', 'ai', true);

            // Simulate AI Response (In production, replace with fetch to PHP/OpenAI)
            setTimeout(() => {
                const responses = [
                    "Entendi. Para o seu segmento, uma Visão forte seria focar na experiência do cliente através da personalização.",
                    "Interessante. Tendo isso em vista, sugiro incluirmos 'Inovação Contínua' e 'Transparência' como Valores fundamentais.",
                    "Um Obstáculo comum nesse cenário é a 'Resistência à mudança cultural'. Vamos adicionar um Método para endereçar isso?",
                    "Para medir esse sucesso, que tal usarmos o NPS e o Churn Rate como Métricas principais?"
                ];
                const randomResponse = responses[Math.floor(Math.random() * responses.length)];

                removeMessage(loadingId);
                addMessage(randomResponse, 'ai');
            }, 1500);
        });

        function addMessage(text, type, isLoading = false) {
            const div = document.createElement('div');
            div.className = `message ${type}`;
            div.id = isLoading ? 'loading-' + Date.now() : '';

            if (type === 'ai' && !isLoading) {
                div.innerHTML = `<strong><i class="ph ph-robot"></i> Advisor IA</strong><br><br>${text}`;
            } else {
                div.innerText = text;
            }

            chatMessages.appendChild(div);
            chatMessages.scrollTop = chatMessages.scrollHeight;
            return div.id;
        }

        function removeMessage(id) {
            const el = document.getElementById(id);
            if (el) el.remove();
        }
    </script>
</body>

</html>
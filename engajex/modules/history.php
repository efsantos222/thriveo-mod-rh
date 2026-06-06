<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
// Company Logic Fallback
$companyId = $_SESSION['company_id'] ?? 1;

$userRole = $_SESSION['role'];
$mode = $_GET['mode'] ?? 'play'; // 'play' or 'admin'
$canManage = ($userRole === 'admin' || $userRole === 'responsible' || $userRole === 'manager');

// Default to admin if managing and no specific mode requested (optional UX choice, keeping play as default for all)
if ($canManage && !isset($_GET['mode'])) {
    // $mode = 'admin'; // Uncomment to force admin view for managers first
}

$message = '';

// --- ADMIN ACTIONS ---
if ($canManage && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'add_event') {
        $title = $_POST['title'];
        $desc = $_POST['description'];
        $year = $_POST['year_label'];
        // Auto-increment order logic
        $stmt = $pdo->prepare("SELECT MAX(correct_order) FROM history_events WHERE company_id = ?");
        $stmt->execute([$companyId]);
        $max = $stmt->fetchColumn();
        $order = $max ? $max + 1 : 1;

        $pdo->prepare("INSERT INTO history_events (company_id, title, description, year_label, correct_order) VALUES (?, ?, ?, ?, ?)")
            ->execute([$companyId, $title, $desc, $year, $order]);
        $message = "Evento adicionado!";
    }

    if ($_POST['action'] === 'delete_event') {
        $pdo->prepare("DELETE FROM history_events WHERE id = ? AND company_id = ?")->execute([$_POST['id'], $companyId]);
    }

    if ($_POST['action'] === 'save_score') {
        // AJAX endpoint mostly, but handling here if simple POST
    }
}

// --- FETCH DATA ---
$events = [];
$stmt = $pdo->prepare("SELECT * FROM history_events WHERE company_id = ? ORDER BY correct_order ASC");
$stmt->execute([$companyId]);
$events = $stmt->fetchAll();

// If playing, we need a shuffled version for the UI
$shuffledEvents = $events;
if ($mode === 'play') {
    shuffle($shuffledEvents);
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>História da Empresa - Thriveo Engajex</title>
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

        /* HEADER & TABS */
        .glass-header {
            background: var(--card-bg);
            padding: 1.5rem;
            border-radius: 1rem;
            border: 1px solid var(--glass-border);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-tabs {
            display: flex;
            gap: 1rem;
        }

        .nav-btn {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-color);
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            border: 1px solid transparent;
            transition: all 0.2s;
        }

        .nav-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: rgba(255, 255, 255, 0.2);
        }

        /* GAME LAYOUT */
        .game-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            height: calc(100vh - 200px);
        }

        .column {
            background: var(--card-bg);
            border-radius: 1rem;
            padding: 1.5rem;
            border: 1px solid var(--glass-border);
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .col-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #f97316;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* CARDS */
        .event-card {
            background: rgba(255, 255, 255, 0.95);
            /* Light card for readability similar to image */
            color: #1e293b;
            padding: 1rem;
            margin-bottom: 0.75rem;
            border-radius: 0.5rem;
            cursor: grab;
            border-left: 5px solid #3b82f6;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .event-card:active {
            cursor: grabbing;
            transform: scale(0.98);
        }

        .event-card h4 {
            margin: 0 0 0.25rem 0;
            font-size: 1rem;
            color: #0f172a;
        }

        .event-card p {
            margin: 0;
            font-size: 0.85rem;
            color: #475569;
        }

        .event-card .year-badge {
            background: #3b82f6;
            color: white;
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.7rem;
            margin-bottom: 0.25rem;
            font-weight: 700;
        }

        /* TIMELINE SLOTS */
        .timeline-slot {
            min-height: 100px;
            background: rgba(255, 255, 255, 0.03);
            border: 2px dashed rgba(255, 255, 255, 0.2);
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            transition: background 0.2s;
        }

        .timeline-slot.drag-over {
            background: rgba(16, 185, 129, 0.1);
            border-color: #10b981;
        }

        .slot-label {
            position: absolute;
            font-size: 2rem;
            font-weight: 800;
            color: rgba(255, 255, 255, 0.05);
            pointer-events: none;
            z-index: 0;
        }

        .slot-number {
            position: absolute;
            top: 5px;
            right: 10px;
            font-size: 0.8rem;
            color: var(--text-muted);
            font-family: monospace;
        }

        /* ADMIN LIST */
        .admin-list .item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255, 255, 255, 0.05);
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-radius: 0.5rem;
        }

        /* FEEDBACK AREA */
        .feedback-area {
            margin-top: auto;
            padding-top: 1rem;
            border-top: 1px solid var(--glass-border);
        }

        .score-box {
            display: none;
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid #10b981;
            padding: 1rem;
            border-radius: 0.5rem;
            color: #d1fae5;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include '../includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="glass-header">
                <div>
                    <h1 style="font-size: 1.5rem; margin-bottom: 0.25rem;">História da Empresa</h1>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">Organize a timeline corporativa.</p>
                </div>
                <?php if ($canManage): ?>
                    <div class="nav-tabs">
                        <a href="?mode=play" class="nav-btn <?php echo $mode === 'play' ? 'active' : ''; ?>">🎮 Jogar</a>
                        <a href="?mode=admin" class="nav-btn <?php echo $mode === 'admin' ? 'active' : ''; ?>">⚙️
                            Gerenciar</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- GAME MODE -->
            <?php if ($mode === 'play'): ?>
                <!-- Instructions -->
                <div
                    style="background: #1e3a8a; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; display: flex; gap: 2rem; align-items: center; flex-wrap: wrap;">
                    <div style="font-weight: 700; color: #fbbf24;">💡 Como Jogar:</div>
                    <div style="font-size: 0.9rem; color: white;">🤏 Arraste os cards</div>
                    <div style="font-size: 0.9rem; color: white;">📋 Ordene na Timeline (Antigo -> Novo)</div>
                    <div style="font-size: 0.9rem; color: white;">✅ Clique 'Verificar' no final</div>
                </div>

                <div class="game-grid">
                    <!-- Source Column -->
                    <div class="column" id="source-container" ondrop="drop(event)" ondragover="allowDrop(event)">
                        <div class="col-title">📚 Eventos (Embaralhados)</div>
                        <?php if (empty($shuffledEvents)): ?>
                            <p style="color:var(--text-muted); font-style:italic">Nenhum evento cadastrado ainda.</p>
                        <?php endif; ?>

                        <?php foreach ($shuffledEvents as $ev): ?>
                            <div class="event-card" id="ev-<?php echo $ev['id']; ?>" draggable="true" ondragstart="drag(event)"
                                data-order="<?php echo $ev['correct_order']; ?>">
                                <!-- <?php if ($ev['year_label']): ?>
                                    <span class="year-badge"><?php echo htmlspecialchars($ev['year_label']); ?></span>
                                <?php endif; ?> -->
                                <h4><?php echo htmlspecialchars($ev['title']); ?></h4>
                                <p><?php echo htmlspecialchars($ev['description']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Target Timeline Column -->
                    <div class="column">
                        <div class="col-title">⏱ Timeline Cronológica</div>
                        <div id="timeline-container">
                            <?php for ($i = 1; $i <= count($events); $i++): ?>
                                <div class="timeline-slot" id="slot-<?php echo $i; ?>" ondrop="drop(event)"
                                    ondragover="allowDrop(event)">
                                    <span class="slot-label"><?php echo $i; ?>º</span>
                                    <span class="slot-number">Evento <?php echo $i; ?></span>
                                </div>
                            <?php endfor; ?>
                        </div>

                        <div class="feedback-area">
                            <div id="result-box" class="score-box" style="margin-bottom: 1rem;">
                                <h3 id="score-title">0 Acertos</h3>
                                <p id="score-msg">Verificação concluída.</p>
                            </div>
                            <div style="display: flex; gap: 1rem;">
                                <button onclick="resetGame()" class="btn btn-outline" style="flex:1;">🔄 Recomeçar</button>
                                <button onclick="checkAnswers()" class="btn btn-primary"
                                    style="flex:1; background: #10b981;">✅ Verificar Resposta</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ADMIN MODE -->
            <?php if ($mode === 'admin' && $canManage): ?>
                <div class="card" style="padding: 1.5rem; margin-bottom: 2rem;">
                    <h3 style="margin-bottom: 1rem;">Novo Evento Histórico</h3>
                    <form method="POST"
                        style="display: grid; grid-template-columns: 1fr 2fr 1fr; gap: 1rem; align-items: end;">
                        <input type="hidden" name="action" value="add_event">
                        <div>
                            <label class="form-label">Ano / Período</label>
                            <input type="text" name="year_label" class="form-control" placeholder="Ex: 1996 ou 2000-2005"
                                required>
                        </div>
                        <div>
                            <label class="form-label">Título</label>
                            <input type="text" name="title" class="form-control" placeholder="Titulo do evento..." required>
                        </div>
                        <div style="grid-column: span 3;">
                            <label class="form-label">Descrição</label>
                            <textarea name="description" class="form-control" rows="2"
                                placeholder="Detalhes do que aconteceu..."></textarea>
                        </div>
                        <div style="grid-column: span 3; text-align: right;">
                            <button class="btn btn-primary">➕ Adicionar Evento</button>
                        </div>
                    </form>
                </div>

                <div class="admin-list">
                    <h3 style="margin-bottom: 1rem; color: var(--text-muted);">Eventos Cadastrados (Ordem Cronológica)</h3>
                    <?php if (!empty($message)): ?>
                        <div style="color: #34d399; margin-bottom:1rem">✅ <?php echo $message; ?></div><?php endif; ?>

                    <?php foreach ($events as $ev): ?>
                        <div class="item">
                            <div>
                                <span
                                    style="background: #3b82f6; font-size: 0.8rem; padding: 2px 6px; border-radius: 4px; margin-right: 0.5rem; color: white;">#<?php echo $ev['correct_order']; ?></span>
                                <strong
                                    style="color: #f97316; margin-right: 0.5rem;"><?php echo htmlspecialchars($ev['year_label']); ?></strong>
                                <span style="font-weight: 600;"><?php echo htmlspecialchars($ev['title']); ?></span>
                                <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">
                                    <?php echo htmlspecialchars($ev['description']); ?></div>
                            </div>
                            <form method="POST" onsubmit="return confirm('Excluir?');" style="margin:0;">
                                <input type="hidden" name="action" value="delete_event">
                                <input type="hidden" name="id" value="<?php echo $ev['id']; ?>">
                                <button class="btn btn-outline"
                                    style="color: #ef4444; border-color: rgba(239,68,68,0.3);">🗑️</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </main>
    </div>

    <!-- DRAG AND DROP LOGIC -->
    <script>
        function allowDrop(ev) {
            ev.preventDefault();
            // Highlight slot
            if (ev.target.classList.contains('timeline-slot')) {
                ev.target.classList.add('drag-over');
            }
        }

        function drag(ev) {
            ev.dataTransfer.setData("text", ev.target.id);
        }

        function drop(ev) {
            ev.preventDefault();
            var data = ev.dataTransfer.getData("text");
            var draggedElement = document.getElementById(data);

            // Logic to find closest valid drop target
            let target = ev.target;
            // Traverse up if dropped on a child element
            while (target && !target.classList.contains('timeline-slot') && !target.classList.contains('column')) {
                target = target.parentElement;
            }

            if (target) {
                target.classList.remove('drag-over');

                // If dropped on source column, just append
                if (target.id === 'source-container') {
                    target.appendChild(draggedElement);
                }
                // If dropped on a timeline slot
                else if (target.classList.contains('timeline-slot')) {
                    // Check if slot already has a card
                    const existingCard = target.querySelector('.event-card');
                    if (existingCard) {
                        // Swap or return to source? Let's simply move existing back to source for simplicity or Swap
                        document.getElementById('source-container').appendChild(existingCard);
                    }
                    target.appendChild(draggedElement);
                }
            }
        }

        // Remove highlighting on drag leave (optional polish)
        document.addEventListener('dragleave', function (ev) {
            if (ev.target.classList && ev.target.classList.contains('timeline-slot')) {
                ev.target.classList.remove('drag-over');
            }
        });

        function checkAnswers() {
            let correct = 0;
            let total = document.querySelectorAll('.timeline-slot').length;
            let filled = 0;

            if (total === 0) return;

            // Reset styles
            document.querySelectorAll('.timeline-slot').forEach(slot => {
                slot.style.borderColor = 'rgba(255,255,255,0.2)';
            });

            // Iterate slots 1 to N
            for (let i = 1; i <= total; i++) {
                let slot = document.getElementById('slot-' + i);
                let card = slot.querySelector('.event-card');

                if (card) {
                    filled++;
                    let cardOrder = parseInt(card.getAttribute('data-order'));
                    // Check if card order matches slot index
                    if (cardOrder === i) {
                        correct++;
                        slot.style.borderColor = '#10b981'; // Green
                    } else {
                        slot.style.borderColor = '#ef4444'; // Red
                    }
                }
            }

            if (filled < total) {
                alert("Preencha todos os slots da timeline antes de verificar!");
                return;
            }

            // Show Result
            const box = document.getElementById('result-box');
            box.style.display = 'block';
            document.getElementById('score-title').innerText = correct + " / " + total + " Acertos";

            if (correct === total) {
                box.style.background = 'rgba(16, 185, 129, 0.2)';
                box.style.borderColor = '#10b981';
                document.getElementById('score-msg').innerText = "Parabéns! Você conhece a história da empresa!";
            } else {
                box.style.background = 'rgba(239, 68, 68, 0.2)';
                box.style.borderColor = '#ef4444';
                document.getElementById('score-msg').innerText = "Ops! Alguns eventos estão fora de ordem.";
            }
        }

        function resetGame() {
            location.reload();
        }
    </script>
</body>

</html>
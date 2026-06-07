<?php
require_once '../config.php';
require_once '../includes/storage.php';

if (!isAdminAuthenticated()) {
    header('Location: admin-login.php');
    exit;
}

$storage = new Storage(STORAGE_DIR);
$configs = $storage->getGameConfigs();
$message = '';
$messageType = 'success';

// Handle logout
if (isset($_GET['logout'])) {
    adminLogout();
    header('Location: admin-login.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $gameType = $_POST['game_type'] ?? '';
    
    if ($action === 'update_items' && $gameType === 'speedIntro') {
        $items = isset($_POST['items']) ? $_POST['items'] : [];
        $items = array_filter(array_map('trim', $items));
        $configs['speedIntro']['items'] = array_values($items);
        $storage->updateGameConfig('speedIntro', $configs['speedIntro']);
        $message = 'Itens atualizados com sucesso!';
    }
    
    if ($action === 'update_questions' && $gameType === 'mysteryBox') {
        $questions = isset($_POST['questions']) ? $_POST['questions'] : [];
        $questions = array_filter(array_map('trim', $questions));
        $configs['mysteryBox']['questions'] = array_values($questions);
        $storage->updateGameConfig('mysteryBox', $configs['mysteryBox']);
        $message = 'Perguntas atualizadas com sucesso!';
    }
    
    // Reload configs
    $configs = $storage->getGameConfigs();
}

$sessions = $storage->getSessions();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - Virtual Connections</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .item-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            padding: 12px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .item-number {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
        }
        .item-input {
            flex: 1;
        }
        .item-actions {
            display: flex;
            gap: 4px;
            flex-shrink: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚙️ Painel Administrativo</h1>
            <p>Gerencie configurações e conteúdo dos jogos</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?>" data-testid="alert-message">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="admin-header">
            <h2 style="color: #1f2937; font-size: 24px;">Configurações</h2>
            <div style="display: flex; gap: 12px;">
                <a href="../index.php" class="btn btn-secondary" data-testid="button-home">
                    🏠 Início
                </a>
                <a href="?logout=1" class="btn btn-danger" data-testid="button-logout">
                    🚪 Sair
                </a>
            </div>
        </div>

        <!-- Speed Introduction Items -->
        <div class="section">
            <h3 class="section-title">⚡ Apresentação Relâmpago - Gerenciar Itens</h3>
            <form method="POST" id="speed-intro-form">
                <input type="hidden" name="action" value="update_items">
                <input type="hidden" name="game_type" value="speedIntro">
                
                <div id="items-container">
                    <?php foreach ($configs['speedIntro']['items'] as $index => $item): ?>
                        <div class="item-row" data-index="<?= $index ?>" draggable="true" data-testid="item-row-<?= $index ?>">
                            <div class="drag-handle" style="cursor: grab; color: #6b7280;">☰</div>
                            <div class="item-number"><?= str_pad($index + 1, 2, '0', STR_PAD_LEFT) ?></div>
                            <input 
                                type="text" 
                                name="items[]" 
                                value="<?= htmlspecialchars($item) ?>" 
                                class="input item-input"
                                data-testid="input-item-<?= $index ?>"
                            >
                            <div class="item-actions">
                                <button type="button" class="icon-btn" onclick="moveItemUp(<?= $index ?>)" data-testid="button-move-up-<?= $index ?>" <?= $index === 0 ? 'disabled' : '' ?>>
                                    ▲
                                </button>
                                <button type="button" class="icon-btn" onclick="moveItemDown(<?= $index ?>)" data-testid="button-move-down-<?= $index ?>" <?= $index === count($configs['speedIntro']['items']) - 1 ? 'disabled' : '' ?>>
                                    ▼
                                </button>
                                <button type="button" class="icon-btn" onclick="removeItem(this)" style="color: #ef4444;" data-testid="button-remove-<?= $index ?>">
                                    ✕
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 16px;">
                    <button type="button" class="btn btn-secondary" onclick="addNewItem()" data-testid="button-add-item">
                        + Adicionar Item
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="sortItemsAlphabetically()" data-testid="button-sort-alphabetically">
                        🔤 Ordenar Alfabeticamente
                    </button>
                    <button type="submit" class="btn btn-primary" data-testid="button-save-items">
                        💾 Salvar Alterações
                    </button>
                </div>
            </form>
        </div>

        <!-- Mystery Box Questions -->
        <div class="section">
            <h3 class="section-title">🎁 Caixa Misteriosa - Gerenciar Perguntas</h3>
            <form method="POST" id="mystery-box-form">
                <input type="hidden" name="action" value="update_questions">
                <input type="hidden" name="game_type" value="mysteryBox">
                
                <div id="questions-container">
                    <?php foreach ($configs['mysteryBox']['questions'] as $index => $question): ?>
                        <div class="item-row" data-index="<?= $index ?>" data-testid="question-row-<?= $index ?>">
                            <div class="item-number"><?= str_pad($index + 1, 2, '0', STR_PAD_LEFT) ?></div>
                            <input 
                                type="text" 
                                name="questions[]" 
                                value="<?= htmlspecialchars($question) ?>" 
                                class="input item-input"
                                data-testid="input-question-<?= $index ?>"
                            >
                            <div class="item-actions">
                                <button type="button" class="icon-btn" onclick="removeQuestion(this)" style="color: #ef4444;" data-testid="button-remove-question-<?= $index ?>">
                                    ✕
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 16px;">
                    <button type="button" class="btn btn-secondary" onclick="addNewQuestion()" data-testid="button-add-question">
                        + Adicionar Pergunta
                    </button>
                    <button type="submit" class="btn btn-primary" data-testid="button-save-questions">
                        💾 Salvar Alterações
                    </button>
                </div>
            </form>
        </div>

        <!-- Sessions List -->
        <div class="section">
            <h3 class="section-title">📋 Sessões Criadas (<?= count($sessions) ?>)</h3>
            <?php if (empty($sessions)): ?>
                <p style="color: #6b7280; text-align: center; padding: 20px;">
                    Nenhuma sessão criada ainda
                </p>
            <?php else: ?>
                <div class="grid grid-2">
                    <?php foreach ($sessions as $session): ?>
                        <div class="card" data-testid="session-card-<?= $session['id'] ?>">
                            <div class="card-header">
                                <h4 class="card-title"><?= htmlspecialchars($session['name']) ?></h4>
                            </div>
                            <p style="color: #6b7280; margin-bottom: 8px;">
                                Participantes: <?= count($session['participants']) ?>
                            </p>
                            <p style="color: #6b7280; font-size: 14px;">
                                Criado: <?= date('d/m/Y H:i', strtotime($session['createdAt'])) ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Add new item
        function addNewItem() {
            const container = document.getElementById('items-container');
            const index = container.children.length;
            const row = createItemRow(index, '');
            container.appendChild(row);
            updateItemNumbers();
            setupDragAndDrop();
        }

        function createItemRow(index, value) {
            const div = document.createElement('div');
            div.className = 'item-row';
            div.setAttribute('data-index', index);
            div.setAttribute('draggable', 'true');
            div.setAttribute('data-testid', 'item-row-' + index);
            div.innerHTML = `
                <div class="drag-handle" style="cursor: grab; color: #6b7280;">☰</div>
                <div class="item-number">${String(index + 1).padStart(2, '0')}</div>
                <input type="text" name="items[]" value="${value}" class="input item-input" data-testid="input-item-${index}">
                <div class="item-actions">
                    <button type="button" class="icon-btn" onclick="moveItemUp(${index})" data-testid="button-move-up-${index}">▲</button>
                    <button type="button" class="icon-btn" onclick="moveItemDown(${index})" data-testid="button-move-down-${index}">▼</button>
                    <button type="button" class="icon-btn" onclick="removeItem(this)" style="color: #ef4444;" data-testid="button-remove-${index}">✕</button>
                </div>
            `;
            return div;
        }

        function removeItem(btn) {
            if (confirm('Remover este item?')) {
                btn.closest('.item-row').remove();
                updateItemNumbers();
            }
        }

        function moveItemUp(index) {
            const container = document.getElementById('items-container');
            const rows = Array.from(container.children);
            if (index > 0) {
                container.insertBefore(rows[index], rows[index - 1]);
                updateItemNumbers();
            }
        }

        function moveItemDown(index) {
            const container = document.getElementById('items-container');
            const rows = Array.from(container.children);
            if (index < rows.length - 1) {
                container.insertBefore(rows[index + 1], rows[index]);
                updateItemNumbers();
            }
        }

        function updateItemNumbers() {
            const container = document.getElementById('items-container');
            const rows = Array.from(container.children);
            rows.forEach((row, index) => {
                row.setAttribute('data-index', index);
                const numberEl = row.querySelector('.item-number');
                if (numberEl) {
                    numberEl.textContent = String(index + 1).padStart(2, '0');
                }
            });
        }

        function sortItemsAlphabetically() {
            const container = document.getElementById('items-container');
            const rows = Array.from(container.children);
            
            rows.sort((a, b) => {
                const textA = a.querySelector('input').value.toLowerCase();
                const textB = b.querySelector('input').value.toLowerCase();
                return textA.localeCompare(textB);
            });
            
            rows.forEach(row => container.appendChild(row));
            updateItemNumbers();
        }

        // Drag and Drop
        let draggedElement = null;

        function setupDragAndDrop() {
            const rows = document.querySelectorAll('#items-container .item-row');
            rows.forEach(row => {
                row.addEventListener('dragstart', handleDragStart);
                row.addEventListener('dragover', handleDragOver);
                row.addEventListener('drop', handleDrop);
                row.addEventListener('dragend', handleDragEnd);
            });
        }

        function handleDragStart(e) {
            draggedElement = this;
            this.style.opacity = '0.5';
        }

        function handleDragOver(e) {
            e.preventDefault();
            return false;
        }

        function handleDrop(e) {
            e.stopPropagation();
            if (draggedElement !== this) {
                const container = document.getElementById('items-container');
                const allRows = Array.from(container.children);
                const draggedIndex = allRows.indexOf(draggedElement);
                const targetIndex = allRows.indexOf(this);
                
                if (draggedIndex < targetIndex) {
                    container.insertBefore(draggedElement, this.nextSibling);
                } else {
                    container.insertBefore(draggedElement, this);
                }
                updateItemNumbers();
            }
            return false;
        }

        function handleDragEnd(e) {
            this.style.opacity = '1';
            draggedElement = null;
        }

        // Questions management
        function addNewQuestion() {
            const container = document.getElementById('questions-container');
            const index = container.children.length;
            const div = document.createElement('div');
            div.className = 'item-row';
            div.setAttribute('data-index', index);
            div.setAttribute('data-testid', 'question-row-' + index);
            div.innerHTML = `
                <div class="item-number">${String(index + 1).padStart(2, '0')}</div>
                <input type="text" name="questions[]" value="" class="input item-input" data-testid="input-question-${index}">
                <div class="item-actions">
                    <button type="button" class="icon-btn" onclick="removeQuestion(this)" style="color: #ef4444;" data-testid="button-remove-question-${index}">✕</button>
                </div>
            `;
            container.appendChild(div);
            updateQuestionNumbers();
        }

        function removeQuestion(btn) {
            if (confirm('Remover esta pergunta?')) {
                btn.closest('.item-row').remove();
                updateQuestionNumbers();
            }
        }

        function updateQuestionNumbers() {
            const container = document.getElementById('questions-container');
            const rows = Array.from(container.children);
            rows.forEach((row, index) => {
                row.setAttribute('data-index', index);
                const numberEl = row.querySelector('.item-number');
                if (numberEl) {
                    numberEl.textContent = String(index + 1).padStart(2, '0');
                }
            });
        }

        // Initialize drag and drop
        setupDragAndDrop();
    </script>
</body>
</html>

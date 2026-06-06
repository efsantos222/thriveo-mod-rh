<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$companyId = $_SESSION['company_id'] ?? 1;
$isManager = in_array($_SESSION['role'], ['admin', 'manager', 'responsible']);
$message = '';

// --- ACTIONS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isManager) {
    if ($_POST['action'] === 'add_article') {
        $title = $_POST['title'];
        $category = $_POST['category'];
        $content = $_POST['content'];

        $pdo->prepare("INSERT INTO board_knowledge (company_id, title, category, content) VALUES (?, ?, ?, ?)")
            ->execute([$companyId, $title, $category, $content]);
        $message = "Artigo adicionado com sucesso.";
    }

    if ($_POST['action'] === 'delete_article') {
        $pdo->prepare("DELETE FROM board_knowledge WHERE id = ? AND company_id = ?")
            ->execute([$_POST['id'], $companyId]);
        $message = "Artigo removido.";
    }
}

// --- FETCH DATA ---
$stmt = $pdo->prepare("SELECT * FROM board_knowledge WHERE company_id = ? ORDER BY category, created_at DESC");
$stmt->execute([$companyId]);
$articles = $stmt->fetchAll();

$grouped = [];
foreach ($articles as $a) {
    $cat = ucfirst($a['category']);
    $grouped[$cat][] = $a;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Base de Conhecimento - Engaja</title>
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

        .kb-section {
            margin-bottom: 3rem;
        }

        .kb-cat-title {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
            border-bottom: 1px solid var(--glass-border);
            padding-bottom: 0.5rem;
        }

        .article-card {
            background: var(--card-bg);
            padding: 1.5rem;
            border-radius: 0.5rem;
            border: 1px solid var(--glass-border);
            margin-bottom: 1rem;
            transition: all 0.2s;
        }

        .article-card:hover {
            transform: translateX(5px);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .article-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            margin-bottom: 0.5rem;
            display: flex;
            justify-content: space-between;
        }

        .article-content {
            color: #cbd5e1;
            font-size: 0.95rem;
            line-height: 1.6;
            white-space: pre-line;
        }

        .add-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            font-size: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
            z-index: 10;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 100;
            align-items: center;
            justify-content: center;
        }

        .modal.open {
            display: flex;
        }

        .modal-box {
            background: #1e293b;
            padding: 2rem;
            border-radius: 1rem;
            width: 90%;
            max-width: 600px;
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include '../includes/sidebar.php'; ?>

        <main class="main-content">
            <h1 style="margin-bottom: 2rem;">📚 Base de Conhecimento</h1>

            <?php if (empty($grouped)): ?>
                <div style="text-align: center; color: var(--text-muted); margin-top: 4rem;">
                    <h3>Nenhum artigo encontrado.</h3>
                    <p>Use o botão + para adicionar documentos, guias ou processos.</p>
                </div>
            <?php endif; ?>

            <?php foreach ($grouped as $cat => $items): ?>
                <div class="kb-section">
                    <h3 class="kb-cat-title">
                        <?php echo htmlspecialchars($cat); ?>
                    </h3>
                    <?php foreach ($items as $item): ?>
                        <div class="article-card">
                            <div class="article-title">
                                <?php echo htmlspecialchars($item['title']); ?>
                                <?php if ($isManager): ?>
                                    <form method="POST" onsubmit="return confirm('Apagar artigo?');">
                                        <input type="hidden" name="action" value="delete_article">
                                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                        <button style="background: none; border: none; color: #ef4444; cursor: pointer;">🗑</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <div class="article-content">
                                <?php echo htmlspecialchars($item['content']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

            <?php if ($isManager): ?>
                <button class="btn btn-primary add-btn"
                    onclick="document.getElementById('addModal').classList.add('open')">+</button>

                <div id="addModal" class="modal">
                    <div class="modal-box">
                        <h2 style="margin-bottom: 1.5rem;">Novo Artigo</h2>
                        <form method="POST">
                            <input type="hidden" name="action" value="add_article">
                            <div class="form-group">
                                <label class="form-label">Título</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Categoria</label>
                                <select name="category" class="form-control">
                                    <option value="general">Geral</option>
                                    <option value="onboarding">Onboarding</option>
                                    <option value="offboarding">Offboarding</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Conteúdo</label>
                                <textarea name="content" class="form-control" rows="8" required></textarea>
                            </div>
                            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                                <button class="btn btn-primary" style="flex: 1;">Salvar</button>
                                <button type="button" class="btn btn-outline" style="flex: 1;"
                                    onclick="document.getElementById('addModal').classList.remove('open')">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

        </main>
    </div>
</body>

</html>
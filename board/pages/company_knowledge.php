<?php
require_once '../config/config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('../login.php');
}

$empresa_id = $_SESSION['empresa_id'];
$message = '';

// Handle Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        // Delete
        $stmt = $pdo->prepare("DELETE FROM company_knowledge_base WHERE id = ? AND empresa_id = ?");
        $stmt->execute([$_POST['delete_id'], $empresa_id]);
        $message = "Item removido com sucesso.";
    } else {
        // Add/Edit
        $title = $_POST['title'];
        $category = $_POST['category'];
        $content = $_POST['content'];

        $stmt = $pdo->prepare("INSERT INTO company_knowledge_base (empresa_id, title, category, content) VALUES (?, ?, ?, ?)");
        $stmt->execute([$empresa_id, $title, $category, $content]);
        $message = "Conhecimento adicionado com sucesso!";
    }
}

// Fetch Items
$stmt = $pdo->prepare("SELECT * FROM company_knowledge_base WHERE empresa_id = ? ORDER BY category, created_at DESC");
$stmt->execute([$empresa_id]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Treinamento da IA</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content">
            <header class="topbar">
                <h2>Base de Conhecimento (Treinamento IA)</h2>
            </header>
            <div class="page-content">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Adicionar Novo Conhecimento</h3>
                    </div>
                    <?php if ($message): ?>
                        <div
                            style="padding:1rem; background:#dcfce7; color:#166534; border-radius:0.5rem; margin-bottom:1rem;">
                            <?php echo $message; ?></div><?php endif; ?>

                    <form method="POST">
                        <div class="row" style="display:flex; gap:1rem;">
                            <div class="form-group" style="flex:2;">
                                <label>Título (Assunto)</label>
                                <input type="text" name="title" class="form-control"
                                    placeholder="Ex: Política de Home Office" required>
                            </div>
                            <div class="form-group" style="flex:1;">
                                <label>Categoria</label>
                                <select name="category" class="form-control">
                                    <option value="general">Geral</option>
                                    <option value="onboarding">Onboarding</option>
                                    <option value="offboarding">Offboarding</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Conteúdo / Regras / Informações</label>
                            <textarea name="content" class="form-control" rows="6"
                                placeholder="Cole aqui o texto com as informações que a IA deve aprender..."
                                required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Adicionar à Base de Conhecimento</button>
                    </form>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Itens Cadastrados</h3>
                    </div>
                    <?php if (count($items) == 0): ?>
                        <p>Nenhum item cadastrado.</p><?php endif; ?>

                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="border-bottom:2px solid #f1f5f9; text-align:left;">
                                <th style="padding:0.5rem;">Categoria</th>
                                <th style="padding:0.5rem;">Título</th>
                                <th style="padding:0.5rem;">Resumo</th>
                                <th style="padding:0.5rem;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td style="padding:0.5rem;"><span class="badge"
                                            style="background:#eff6ff; color:#2563eb; padding:2px 8px; border-radius:4px; font-size:0.8em;"><?php echo ucfirst($item['category']); ?></span>
                                    </td>
                                    <td style="padding:0.5rem; font-weight:600;">
                                        <?php echo htmlspecialchars($item['title']); ?></td>
                                    <td style="padding:0.5rem; color:#666;">
                                        <?php echo substr(htmlspecialchars($item['content']), 0, 50) . '...'; ?></td>
                                    <td style="padding:0.5rem;">
                                        <form method="POST" onsubmit="return confirm('Excluir este item?');"
                                            style="display:inline;">
                                            <input type="hidden" name="delete_id" value="<?php echo $item['id']; ?>">
                                            <button type="submit"
                                                style="background:none; border:none; color:#ef4444; cursor:pointer;"><i
                                                    class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>

</html>
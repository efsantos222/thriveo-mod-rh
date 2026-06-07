<?php
session_start();
require_once __DIR__ . '/config/db.php';

// Check if Super Admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'super_admin') {
    header("Location: dashboard.php");
    exit;
}

$page = 'companies';
$message = '';

// Handle Add Company
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = $_POST['name'];
    $cnpj = $_POST['cnpj'];

    if ($name) {
        $stmt = $pdo->prepare("INSERT INTO companies (name, cnpj) VALUES (?, ?)");
        if ($stmt->execute([$name, $cnpj])) {
            $message = "Empresa adicionada com sucesso!";
        } else {
            $message = "Erro ao adicionar empresa.";
        }
    }
}

// Handle Delete Company
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    // Prevent deleting own company (simplification, real logic needed)
    $stmt = $pdo->prepare("DELETE FROM companies WHERE id = ?");
    if ($stmt->execute([$id])) {
        $message = "Empresa removida.";
    }
}

// Fetch Companies
$stmt = $pdo->query("SELECT * FROM companies ORDER BY created_at DESC");
$companies = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">

<head>
    <meta charset="UTF-8">
    <title>Gestão de Empresas - Thriveo ZBB</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body style="background-color: var(--background-color);">

    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <h1 class="mb-4">Gestão de Empresas</h1>

            <?php if ($message): ?>
                <div
                    style="background-color: #d1fae5; color: #065f46; padding: 1rem; border-radius: var(--border-radius); margin-bottom: 2rem;">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div class="card mb-4">
                <h3>Nova Empresa</h3>
                <form method="POST" action=""
                    style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 1rem; align-items: end;">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Nome da Empresa</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">CNPJ</label>
                        <input type="text" name="cnpj" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Adicionar</button>
                </form>
            </div>

            <div class="card">
                <h3>Empresas Cadastradas</h3>
                <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 1px solid #e2e8f0;">
                            <th style="padding: 1rem;">ID</th>
                            <th style="padding: 1rem;">Nome</th>
                            <th style="padding: 1rem;">CNPJ</th>
                            <th style="padding: 1rem;">Criado em</th>
                            <th style="padding: 1rem;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($companies as $comp): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 1rem;">
                                    <?= $comp['id'] ?>
                                </td>
                                <td style="padding: 1rem; font-weight: 500;">
                                    <?= htmlspecialchars($comp['name']) ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?= htmlspecialchars($comp['cnpj']) ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?= date('d/m/Y', strtotime($comp['created_at'])) ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <a href="?delete=<?= $comp['id'] ?>" class="btn btn-secondary"
                                        style="background-color: #fee2e2; color: #b91c1c; border-color: #fecaca; padding: 0.25rem 0.5rem; font-size: 0.8rem;"
                                        onclick="return confirm('Tem certeza? Isso apagará todos os dados vinculados.');">Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>
</body>

</html>
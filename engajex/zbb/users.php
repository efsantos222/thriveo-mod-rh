<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['super_admin', 'admin'])) {
    header("Location: dashboard.php");
    exit;
}

$page = 'users';
$message = '';

// Handle Add User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $company_id = $_POST['company_id'];

    // If admin (not super), force company_id to their own
    if ($_SESSION['user_role'] === 'admin') {
        $company_id = $_SESSION['company_id'];
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO users (company_id, name, email, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$company_id, $name, $email, $password, $role]);
        $message = "Usuário adicionado com sucesso!";
    } catch (PDOException $e) {
        $message = "Erro: " . $e->getMessage();
    }
}

// Fetch Users
$sql = "SELECT users.*, companies.name as company_name FROM users LEFT JOIN companies ON users.company_id = companies.id";
$params = [];

if ($_SESSION['user_role'] !== 'super_admin') {
    $sql .= " WHERE users.company_id = ?";
    $params[] = $_SESSION['company_id'];
}
$sql .= " ORDER BY users.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Fetch Companies for Select (Super Admin only)
$companies = [];
if ($_SESSION['user_role'] === 'super_admin') {
    $companies = $pdo->query("SELECT id, name FROM companies")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Gestão de Usuários - Thriveo ZBB</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body style="background-color: var(--background-color);">

    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <h1 class="mb-4">Gestão de Usuários</h1>

            <?php if ($message): ?>
                <div
                    style="background-color: #d1fae5; color: #065f46; padding: 1rem; border-radius: var(--border-radius); margin-bottom: 2rem;">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div class="card mb-4">
                <h3>Novo Usuário</h3>
                <form method="POST" action=""
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                    <input type="hidden" name="action" value="add">

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Nome</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Senha</label>
                        <input type="text" name="password" class="form-control" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Função</label>
                        <select name="role" class="form-control">
                            <option value="user">Usuário</option>
                            <option value="manager">Gerente</option>
                            <option value="admin">Admin da Empresa</option>
                            <?php if ($_SESSION['user_role'] === 'super_admin'): ?>
                                <option value="super_admin">Super Admin</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <?php if ($_SESSION['user_role'] === 'super_admin'): ?>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Empresa</label>
                            <select name="company_id" class="form-control">
                                <?php foreach ($companies as $comp): ?>
                                    <option value="<?= $comp['id'] ?>">
                                        <?= htmlspecialchars($comp['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="company_id" value="<?= $_SESSION['company_id'] ?>">
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary" style="height: 48px;">Adicionar</button>
                </form>
            </div>

            <div class="card">
                <h3>Usuários</h3>
                <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 1px solid #e2e8f0;">
                            <th style="padding: 1rem;">Nome</th>
                            <th style="padding: 1rem;">E-mail</th>
                            <th style="padding: 1rem;">Função</th>
                            <th style="padding: 1rem;">Empresa</th>
                            <th style="padding: 1rem;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 1rem; font-weight: 500;">
                                    <?= htmlspecialchars($u['name']) ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?= htmlspecialchars($u['email']) ?>
                                </td>
                                <td style="padding: 1rem; text-transform: capitalize;">
                                    <?= str_replace('_', ' ', $u['role']) ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?= htmlspecialchars($u['company_name']) ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <!-- Add logic for edit/delete -->
                                    <button class="btn btn-secondary"
                                        style="font-size: 0.8rem; padding: 0.25rem 0.5rem;">Editar</button>
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
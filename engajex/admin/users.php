<?php
require_once '../config.php';
if (!isAdmin()) {
    header("Location: ../login.php");
    exit;
}

$users = $pdo->query("SELECT users.*, companies.name as company_name FROM users LEFT JOIN companies ON users.company_id = companies.id ORDER BY users.id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Gerenciar Usuários - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        th,
        td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--glass-border);
        }

        th {
            color: var(--text-muted);
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1>Usuários do Sistema</h1>
                <a href="user_add.php" class="btn btn-primary">+ Novo Usuário</a>
            </div>

            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Empresa</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($user['name']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </td>
                                <td>
                                    <span
                                        style="padding: 2px 8px; border-radius: 12px; font-size: 0.8rem; background: rgba(255,255,255,0.1);">
                                        <?php echo htmlspecialchars($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($user['company_name'] ?? '-'); ?>
                                </td>
                                <td>
                                    <?php if ($user['role'] !== 'admin'): ?>
                                        <a href="user_delete.php?id=<?php echo $user['id']; ?>" class="btn btn-outline"
                                            style="padding: 0.25rem 0.5rem; color: #fca5a5; border-color: #fca5a5;"
                                            onclick="return confirm('Excluir usuário?');">X</a>
                                    <?php endif; ?>
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
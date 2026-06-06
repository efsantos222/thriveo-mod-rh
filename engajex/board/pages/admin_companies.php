<?php
require_once '../config/config.php';

if (!isLoggedIn() || !hasRole('superadmin')) {
    redirect('../login.php');
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM empresas WHERE id = ?");
    $stmt->execute([$id]);
    redirect('admin_companies.php');
}

// List Companies
$stmt = $pdo->query("SELECT e.*, (SELECT COUNT(*) FROM users u WHERE u.empresa_id = e.id) as user_count FROM empresas e ORDER BY created_at DESC");
$companies = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Gerenciar Empresas - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content">
            <header class="topbar">
                <h2>Gerenciar Empresas</h2>
                <div class="user-profile">Admin</div>
            </header>
            <div class="page-content">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Empresas Cadastradas</h3>
                        <a href="admin_company_form.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nova
                            Empresa</a>
                    </div>
                    <div class="card-body">
                        <table style="width:100%; border-collapse: collapse; text-align: left;">
                            <thead>
                                <tr style="border-bottom: 2px solid #f1f5f9;">
                                    <th style="padding:1rem;">ID</th>
                                    <th style="padding:1rem;">Nome</th>
                                    <th style="padding:1rem;">Domínio</th>
                                    <th style="padding:1rem;">Usuários</th>
                                    <th style="padding:1rem;">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($companies as $comp): ?>
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td style="padding:1rem;"><?php echo $comp['id']; ?></td>
                                        <td style="padding:1rem; font-weight:600;">
                                            <?php echo htmlspecialchars($comp['nome']); ?></td>
                                        <td style="padding:1rem;"><?php echo htmlspecialchars($comp['dominio']); ?></td>
                                        <td style="padding:1rem;"><?php echo $comp['user_count']; ?></td>
                                        <td style="padding:1rem;">
                                            <a href="admin_company_form.php?id=<?php echo $comp['id']; ?>"
                                                style="color:var(--primary-color); margin-right:1rem;"><i
                                                    class="fa-solid fa-pen"></i></a>
                                            <a href="?delete=<?php echo $comp['id']; ?>"
                                                onclick="return confirm('Tem certeza? Isso excluirá todos os usuários desta empresa.');"
                                                style="color:#ef4444;"><i class="fa-solid fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html>
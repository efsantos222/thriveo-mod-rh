<?php
require_once '../config.php';
if (!isAdmin()) {
    header("Location: ../login.php");
    exit;
}

$companies = $pdo->query("SELECT * FROM companies ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Gerenciar Empresas - Admin</title>
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
                <h1>Empresas</h1>
                <a href="company_add.php" class="btn btn-primary">Nova Empresa</a>
            </div>

            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Empresa</th>
                            <th>CNPJ</th>
                            <th>Responsável</th>
                            <th>Email</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($companies as $comp): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($comp['name']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($comp['cnpj']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($comp['responsible_name']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($comp['responsible_email']); ?>
                                </td>
                                <td>
                                    <a href="company_delete.php?id=<?php echo $comp['id']; ?>" class="btn btn-outline"
                                        style="padding: 0.25rem 0.5rem; font-size: 0.8rem; color: #fca5a5; border-color: #fca5a5;"
                                        onclick="return confirm('Tem certeza?');">Excluir</a>
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
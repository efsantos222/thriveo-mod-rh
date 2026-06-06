<?php
require_once '../config/config.php';

if (!isLoggedIn() || !hasRole('superadmin')) {
    redirect('../login.php');
}

$id = isset($_GET['id']) ? intval($_GET['id']) : null;
$company = ['nome' => '', 'dominio' => ''];
$responsible = ['nome' => '', 'email' => '', 'password' => ''];
$error = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $dominio = $_POST['dominio'];

    // Responsible fields (only for new companies usually, or we can allow adding more later)
    $resp_nome = $_POST['resp_nome'] ?? '';
    $resp_email = $_POST['resp_email'] ?? '';
    $resp_pass = $_POST['resp_password'] ?? '';

    try {
        $pdo->beginTransaction();

        if ($id) {
            // Edit
            $stmt = $pdo->prepare("UPDATE empresas SET nome = ?, dominio = ? WHERE id = ?");
            $stmt->execute([$nome, $dominio, $id]);
            $message = "Empresa atualizada com sucesso!";
        } else {
            // Create
            $stmt = $pdo->prepare("INSERT INTO empresas (nome, dominio) VALUES (?, ?)");
            $stmt->execute([$nome, $dominio]);
            $new_comp_id = $pdo->lastInsertId();
            $id = $new_comp_id; // Set ID for view

            // Create Responsible
            if ($resp_email && $resp_pass) {
                // Check if email exists
                $st = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $st->execute([$resp_email]);
                if ($st->rowCount() > 0) {
                    // Email exists, assume linking logic or error. For now, error.
                    $error = "Empresa criada, mas e-mail do responsável já existe.";
                } else {
                    $hash = password_hash($resp_pass, PASSWORD_DEFAULT);
                    $stmt_u = $pdo->prepare("INSERT INTO users (empresa_id, nome, email, password, role) VALUES (?, ?, ?, ?, 'admin')");
                    $stmt_u->execute([$new_comp_id, $resp_nome, $resp_email, $hash]);
                }
            }
            $message = "Empresa e Responsável criados com sucesso!";
        }

        $pdo->commit();
        if (!$error) {
            // Redirect or just show success
            // redirect('admin_companies.php'); 
        }

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Erro: " . $e->getMessage();
    }
} else {
    if ($id) {
        $stmt = $pdo->prepare("SELECT * FROM empresas WHERE id = ?");
        $stmt->execute([$id]);
        $company = $stmt->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title><?php echo $id ? 'Editar' : 'Nova'; ?> Empresa</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content">
            <header class="topbar">
                <h2><?php echo $id ? 'Editar Empresa' : 'Nova Empresa'; ?></h2>
            </header>
            <div class="page-content">
                <div class="card" style="max-width: 600px; margin: 0 auto;">
                    <?php if ($error): ?>
                        <div
                            style="background:#fee2e2; color:#991b1b; padding:1rem; border-radius:0.5rem; margin-bottom:1rem;">
                            <?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if ($message): ?>
                        <div
                            style="background:#dcfce7; color:#166534; padding:1rem; border-radius:0.5rem; margin-bottom:1rem;">
                            <?php echo $message; ?></div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <h4 style="margin-bottom:1rem; border-bottom:1px solid #eee; padding-bottom:0.5rem;">Dados da
                            Empresa</h4>
                        <div class="form-group">
                            <label>Nome da Empresa</label>
                            <input type="text" name="nome" class="form-control"
                                value="<?php echo htmlspecialchars($company['nome']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Domínio (ex: proftest.com.br)</label>
                            <input type="text" name="dominio" class="form-control"
                                value="<?php echo htmlspecialchars($company['dominio']); ?>">
                        </div>

                        <?php if (!$id): // Only show responsible creation on new company ?>
                            <h4
                                style="margin-top:2rem; margin-bottom:1rem; border-bottom:1px solid #eee; padding-bottom:0.5rem;">
                                Responsável Inicial (Admin)</h4>
                            <div class="form-group">
                                <label>Nome do Responsável</label>
                                <input type="text" name="resp_nome" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>E-mail</label>
                                <input type="email" name="resp_email" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Senha</label>
                                <input type="password" name="resp_password" class="form-control">
                            </div>
                        <?php endif; ?>

                        <div style="margin-top:2rem; text-align:right;">
                            <a href="admin_companies.php" class="btn btn-outline"
                                style="color:#333; border-color:#ccc; margin-right:1rem;">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>

</html>
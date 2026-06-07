<?php
require_once '../config/config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('../login.php');
}

$action = $_GET['action'] ?? 'list';
$empresa_id = $_SESSION['empresa_id'];
$error = '';
$message = '';

// Handle Create/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $user_role = 'user'; // Defaults to user, maybe allow admin creation? Prompt says "users system".
    $password = $_POST['password'] ?? '';
    $cargo = $_POST['cargo'] ?? '';

    // Simple validation and insertion logic
    if ($action == 'create') {
        if ($email && $password) {
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$email]);
            if ($check->rowCount() > 0) {
                $error = "E-mail já cadastrado.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (empresa_id, nome, email, password, role, cargo) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$empresa_id, $nome, $email, $hash, $user_role, $cargo]);
                $message = "Usuário criado com sucesso!";
                $action = 'list'; // Go back to list
            }
        }
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    // Verify it belongs to this company
    $chk = $pdo->prepare("SELECT id FROM users WHERE id = ? AND empresa_id = ?");
    $chk->execute([$del_id, $empresa_id]);
    if ($chk->fetch()) {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$del_id]);
        header("Location: company_users.php");
        exit;
    }
}

// Fetch Users
$stmt = $pdo->prepare("SELECT * FROM users WHERE empresa_id = ? AND role = 'user' ORDER BY nome");
$stmt->execute([$empresa_id]);
$users = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Gerenciar Usuários</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content">
            <header class="topbar">
                <h2>Gerenciar Usuários</h2>
            </header>
            <div class="page-content">
                <?php if ($action == 'create'): ?>
                    <div class="card" style="max-width:500px; margin:0 auto;">
                        <h3 class="card-title">Novo Colaborador</h3>
                        <?php if ($error): ?>
                            <div style="color:red; margin-bottom:1rem;"><?php echo $error; ?></div><?php endif; ?>
                        <form method="POST">
                            <div class="form-group">
                                <label>Nome Completo</label>
                                <input type="text" name="nome" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Cargo</label>
                                <input type="text" name="cargo" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>E-mail</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Senha Inicial</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Cadastrar</button>
                            <a href="company_users.php" class="btn btn-outline"
                                style="color:#333; border-color:#ccc;">Cancelar</a>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Equipe</h3>
                            <a href="?action=create" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Novo
                                Usuário</a>
                        </div>
                        <?php if ($message): ?>
                            <div style="color:green; margin-bottom:1rem; padding:0.5rem; background:#dcfce7;">
                                <?php echo $message; ?></div><?php endif; ?>

                        <table style="width:100%; border-collapse: collapse; text-align: left;">
                            <thead>
                                <tr style="border-bottom: 2px solid #f1f5f9;">
                                    <th style="padding:1rem;">Nome</th>
                                    <th style="padding:1rem;">Cargo</th>
                                    <th style="padding:1rem;">E-mail</th>
                                    <th style="padding:1rem;">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($users) == 0): ?>
                                    <tr>
                                        <td colspan="4" style="padding:1rem; text-align:center;">Nenhum usuário cadastrado.</td>
                                    </tr>
                                <?php endif; ?>
                                <?php foreach ($users as $u): ?>
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td style="padding:1rem;">
                                            <div style="font-weight:600;"><?php echo htmlspecialchars($u['nome']); ?></div>
                                        </td>
                                        <td style="padding:1rem;"><?php echo htmlspecialchars($u['cargo'] ?? '-'); ?></td>
                                        <td style="padding:1rem;"><?php echo htmlspecialchars($u['email']); ?></td>
                                        <td style="padding:1rem;">
                                            <!-- Edit could go here -->
                                            <a href="?delete=<?php echo $u['id']; ?>"
                                                onclick="return confirm('Confirmar exclusão?');" style="color:#ef4444;"><i
                                                    class="fa-solid fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>

</html>
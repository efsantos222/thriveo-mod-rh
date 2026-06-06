<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['company_id'] = $user['company_id'];

            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Credenciais inválidas.';
        }
    } else {
        $error = 'Preencha todos os campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ProfTest</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="hero" style="min-height: 100vh;">
        <div class="glass-card" style="width: 100%; max-width: 400px;">
            <h2 style="text-align: center; margin-bottom: 2rem;">Acesso ao Sistema</h2>
            <?php if ($error): ?>
                <div
                    style="background: rgba(239, 68, 68, 0.2); color: #fca5a5; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; text-align: center;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Senha</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Entrar</button>
            </form>
            <div style="margin-top: 1rem; text-align: center;">
                <a href="index.php" style="font-size: 0.9rem; color: var(--text-muted);">Voltar para Início</a>
            </div>
        </div>
    </div>
</body>

</html>
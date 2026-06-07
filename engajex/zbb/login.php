<?php
session_start();
require_once __DIR__ . '/config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login Success
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['company_id'] = $user['company_id'];

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Credenciais inválidas. Tente novamente.";
        }
    } else {
        $error = "Por favor, preencha todos os campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Thriveo ZBB</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body
    style="display: flex; align-items: center; justify-content: center; min-height: 100vh; background-color: var(--background-color);">

    <div class="card" style="width: 100%; max-width: 400px;">
        <div class="text-center mb-4">
            <h2 style="color: var(--primary-color);">Thriveo ZBB</h2>
            <p class="text-secondary">Acesse sua conta</p>
        </div>

        <?php if ($error): ?>
            <div
                style="background-color: #fee2e2; color: #b91c1c; padding: 0.75rem; border-radius: 0.5rem; margin-bottom: 1rem; text-align: center;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" id="email" name="email" class="form-control" required placeholder="seu@email.com">
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Senha</label>
                <input type="password" id="password" name="password" class="form-control" required
                    placeholder="Sua senha">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Entrar</button>
        </form>

        <div class="text-center mt-4">
            <a href="index.php" style="font-size: 0.9rem;">Voltar para a Home</a>
        </div>
    </div>

</body>

</html>
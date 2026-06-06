<?php
// login.php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['company_id'] = $user['company_id'];

            if ($user['role'] === 'admin') {
                header("Location: admin/index.php");
            } else {
                header("Location: app/index.php");
            }
            exit;
        } else {
            $error = "E-mail ou senha inválidos.";
        }
    } else {
        $error = "Preencha todos os campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Thriveo</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="auth-container">
        <h2 style="text-align: center; margin-bottom: 2rem;">Acesso ao Sistema</h2>

        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #b91c1c; padding: 0.75rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Senha</label>
                <input type="password" name="password" class="form-input" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Entrar</button>
        </form>
        <div style="text-align: center; margin-top: 1rem;">
            <a href="index.php" style="font-size: 0.9rem; color: #64748b;">Voltar para Início</a>
        </div>
    </div>
</body>

</html>
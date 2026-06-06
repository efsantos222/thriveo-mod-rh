<?php
// login.php
require_once 'includes/config.php';

// Redirect if already logged
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT u.*, c.name as company_name FROM users u LEFT JOIN companies c ON u.company_id = c.id WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['company_id'] = $user['company_id'];
            $_SESSION['company_name'] = $user['company_name'];

            header('Location: index.php');
            exit;
        } else {
            $error = 'Credenciais inválidas.';
        }
    } else {
        $error = 'Preecha todos os campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Sistema 5W2H</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="login-body">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo">5W2H<span>.</span></div>
                <h1>Bem-vindo de volta</h1>
                <p>Acesse seu painel estratégico para gerenciar os planos.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="login-form">
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" required placeholder="seu@email.com">
                </div>
                <div class="form-group">
                    <label for="password">Senha</label>
                    <input type="password" id="password" name="password" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Entrar no Sistema</button>
            </form>

            <div class="login-footer">
                <p>&copy; 2026 Thriveo - Consultoria MAIA</p>
            </div>
        </div>
    </div>
</body>

</html>
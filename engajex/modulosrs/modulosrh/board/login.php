<?php
require_once 'config/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] !== 'active') {
                $error = 'Sua conta está inativa. Contate o administrador.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nome'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['empresa_id'] = $user['empresa_id'];

                redirect('pages/dashboard.php');
            }
        } else {
            $error = 'E-mail ou senha inválidos.';
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
    <title>Login - Proftest Board</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: var(--background-light);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .login-wrapper {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
        }

        .back-link {
            display: block;
            margin-bottom: 1rem;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .back-link:hover {
            color: var(--primary-color);
        }

        .alert {
            padding: 0.75rem;
            background-color: #fee2e2;
            color: #991b1b;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            border: 1px solid #fecaca;
        }
    </style>
</head>

<body>

    <div class="login-wrapper">
        <a href="index.php" class="back-link">&larr; Voltar para Home</a>

        <div class="login-container">
            <h2 style="text-align: center; margin-bottom: 1.5rem; color: var(--primary-color);">Bem-vindo</h2>

            <?php if ($error): ?>
                <div class="alert"><?php echo $error; ?></div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="email">E-mail Corporativo</label>
                    <input type="email" id="email" name="email" class="form-control" required
                        placeholder="seu.nome@empresa.com">
                </div>

                <div class="form-group">
                    <label for="password">Senha</label>
                    <input type="password" id="password" name="password" class="form-control" required
                        placeholder="••••••••">
                </div>

                <div class="form-group" style="text-align: right;">
                    <!-- <a href="#" style="font-size: 0.85rem; color: var(--primary-color);">Esqueceu a senha?</a> -->
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Entrar</button>
            </form>
        </div>

        <p style="text-align: center; margin-top: 1rem; font-size: 0.9rem; color: var(--text-light);">
            Não tem acesso? <a href="index.php#request" style="color: var(--primary-color);">Solicite aqui</a>.
        </p>
    </div>

</body>

</html>
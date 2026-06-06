<?php
session_start();
require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['company_id'] = $user['company_id'];

            if ($user['role'] === 'admin') {
                header("Location: admin/index.php");
            } else {
                header("Location: app/index.php");
            }
            exit;
        } else {
            $error = "Credenciais inválidas.";
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
    <title>Login - MLPT System</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at center, #1e293b 0%, #0f172a 100%);
        }

        .login-card {
            background: var(--card-bg);
            padding: 40px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h2 {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-dim);
        }

        .form-control {
            width: 100%;
            padding: 12px;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: white;
            font-size: 1rem;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
        }

        .btn-full {
            width: 100%;
            text-align: center;
            margin-top: 10px;
        }

        .error-msg {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 0.9rem;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: var(--text-dim);
            text-decoration: none;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2>Bem-vindo</h2>
                <p>Acesse o painel MLPT</p>
            </div>

            <?php if ($error): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>E-mail</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Senha</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary btn-full">Entrar</button>
            </form>

            <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Voltar ao site</a>
        </div>
    </div>
</body>

</html>
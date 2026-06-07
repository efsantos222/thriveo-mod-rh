<?php
session_start();
require_once dirname(__DIR__) . '/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Preencha todos os campos.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM responsaveis WHERE email = ? AND status = 'ativo'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['senha_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['nome'];
            $_SESSION['empresa_id'] = $user['id_empresa'];
            $_SESSION['user_type'] = 'responsavel';

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Credenciais inválidas ou conta inativa.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Responsável - V2MOM Intelligence</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-box {
            width: 100%;
            max-width: 400px;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-box glass card">
            <h2 style="text-align: center; margin-bottom: 20px;">Acesso Corporativo</h2>

            <?php if ($error): ?>
                <div
                    style="background: rgba(239, 68, 68, 0.2); color: #fca5a5; padding: 10px; border-radius: 6px; margin-bottom: 20px; text-align: center;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">E-mail Corporativo</label>
                    <input type="email" name="email" required
                        style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #334155; background: #0f172a; color: white;">
                </div>
                <div style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 5px;">Senha</label>
                    <input type="password" name="password" required
                        style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #334155; background: #0f172a; color: white;">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Acessar Dashboard</button>
            </form>
            <div style="text-align: center; margin-top: 15px;">
                <a href="../index.php" style="color: #64748b; font-size: 0.9rem;">Voltar ao site</a>
            </div>
        </div>
    </div>
</body>

</html>
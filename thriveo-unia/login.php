<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Thriveo Unia</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <header>
        <div class="container">
            <nav>
                <a href="index.php" class="logo">Thriveo Unia</a>
            </nav>
        </div>
    </header>

    <div class="auth-container">
        <div class="auth-box">
            <div style="text-align: center; margin-bottom: 2rem;">
                <h2>Bem-vindo de volta</h2>
                <p style="color: var(--text-muted);">Acesse sua conta corporativa</p>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div
                    style="background: rgba(220, 38, 38, 0.2); border: 1px solid rgba(220, 38, 38, 0.5); color: #fca5a5; padding: 0.75rem; border-radius: 0.5rem; margin-bottom: 1.5rem; text-align: center;">
                    <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>

            <form action="auth_login.php" method="POST">
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
                    <a href="#" style="font-size: 0.85rem; color: var(--primary-color);">Esqueceu a senha?</a>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Entrar</button>
            </form>
        </div>
    </div>
</body>

</html>
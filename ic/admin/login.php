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
    <title>Login - Proftest IC</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh;">

    <div class="cta-box" style="width: 100%; max-width: 400px; text-align: center;">
        <h2 style="margin-bottom: 2rem;">Acesso Administrativo</h2>

        <?php if (isset($_GET['error'])): ?>
            <?php if ($_GET['error'] == 'no_permission'): ?>
                <div
                    style="background: rgba(239, 68, 68, 0.2); color: #fca5a5; padding: 0.75rem; border-radius: 0.5rem; margin-bottom: 1rem; border: 1px solid rgba(239, 68, 68, 0.3);">
                    Acesso restrito a administradores.
                </div>
            <?php else: ?>
                <div
                    style="background: rgba(239, 68, 68, 0.2); color: #fca5a5; padding: 0.75rem; border-radius: 0.5rem; margin-bottom: 1rem; border: 1px solid rgba(239, 68, 68, 0.3);">
                    Credenciais inválidas.
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <form action="auth.php" method="POST">
            <div style="margin-bottom: 1rem; text-align: left;">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">E-mail</label>
                <input type="email" name="email" required
                    style="width: 100%; padding: 0.75rem; background: rgba(0,0,0,0.3); border: 1px solid var(--glass-border); border-radius: 0.5rem; color: #fff; outline: none;">
            </div>

            <div style="margin-bottom: 2rem; text-align: left;">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">Senha</label>
                <input type="password" name="password" required
                    style="width: 100%; padding: 0.75rem; background: rgba(0,0,0,0.3); border: 1px solid var(--glass-border); border-radius: 0.5rem; color: #fff; outline: none;">
            </div>

            <button type="submit" class="btn-login" style="width: 100%; justify-content: center; cursor: pointer;">
                Entrar
            </button>
        </form>

        <p style="margin-top: 1.5rem; color: var(--text-muted); font-size: 0.9rem;">
            <a href="../index.php">Voltar ao início</a>
        </p>
    </div>

</body>

</html>
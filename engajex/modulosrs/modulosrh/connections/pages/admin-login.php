<?php
require_once '../config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    
    if (adminLogin($password)) {
        header('Location: admin-panel.php');
        exit;
    } else {
        $error = 'Senha incorreta. Tente novamente.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrativo - Virtual Connections</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔒 Painel Administrativo</h1>
            <p>Acesso restrito para administradores</p>
        </div>

        <div class="glass-effect-strong" style="padding: 48px; max-width: 500px; margin: 0 auto;">
            <div style="text-align: center; margin-bottom: 32px;">
                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); border-radius: 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 40px; margin-bottom: 16px;">
                    🛡️
                </div>
                <h2 style="color: white; font-size: 24px;">Login de Administrador</h2>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error" data-testid="alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="input-group">
                    <label for="password">Senha de Administrador</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="input" 
                        placeholder="Digite a senha..."
                        required
                        data-testid="input-password"
                    >
                </div>

                <div style="text-align: center; margin-top: 32px; display: flex; gap: 16px; justify-content: center;">
                    <a href="../index.php" class="btn btn-secondary" data-testid="button-back">
                        ← Voltar
                    </a>
                    <button type="submit" class="btn btn-primary" data-testid="button-login">
                        Entrar →
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$message = '';
$error = '';
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPass = $_POST['current_password'];
    $newPass = $_POST['new_password'];
    $confirmPass = $_POST['confirm_password'];

    if ($newPass !== $confirmPass) {
        $error = "As novas senhas não coincidem.";
    } else {
        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (password_verify($currentPass, $user['password'])) {
            $newHash = password_hash($newPass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$newHash, $userId]);
            $message = "Senha alterada com sucesso!";
        } else {
            $error = "Senha atual incorreta.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Meu Perfil - TestProf Engaja</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .app-layout {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }


        .main-content {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
            background: var(--bg-color);
        }

        .profile-card {
            background: var(--card-bg);
            max-width: 500px;
            padding: 2rem;
            border-radius: 1rem;
            border: 1px solid var(--glass-border);
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <h1 style="margin-bottom: 2rem;">Meu Perfil</h1>

            <div class="profile-card">
                <h3 style="margin-bottom: 1.5rem;">Alterar Senha</h3>

                <?php if ($message): ?>
                    <div
                        style="background: #f0fdf4; color: #15803d; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; border: 1px solid rgba(16, 185, 129, 0.3);">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div
                        style="background: #fef2f2; color: #b91c1c; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; border: 1px solid rgba(239, 68, 68, 0.3);">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Senha Atual</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nova Senha</label>
                        <input type="password" name="new_password" class="form-control" required minlength="6">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirmar Nova Senha</label>
                        <input type="password" name="confirm_password" class="form-control" required minlength="6">
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Atualizar
                        Senha</button>
                </form>
            </div>
        </main>
    </div>
</body>

</html>
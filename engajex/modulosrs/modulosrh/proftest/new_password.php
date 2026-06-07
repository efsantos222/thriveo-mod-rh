<?php
session_start();

// Verificar token
if (!isset($_GET['token'])) {
    header('Location: admin_login.php');
    exit;
}

$token = $_GET['token'];
$token_valid = false;
$user_email = '';

// Verificar se o token é válido
$reset_tokens_file = 'resultados/reset_tokens.csv';
if (file_exists($reset_tokens_file)) {
    $fp = fopen($reset_tokens_file, 'r');
    while (($data = fgetcsv($fp)) !== FALSE) {
        if ($data[1] === $token && $data[2] > time()) {
            $token_valid = true;
            $user_email = $data[0];
            break;
        }
    }
    fclose($fp);
}

if (!$token_valid) {
    header('Location: admin_login.php?error=invalid_token');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Senha - Sistema DISC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { 
            padding-top: 50px; 
            background-color: #f5f5f5;
        }
        .container { max-width: 500px; }
        .form-container { 
            background: #fff; 
            padding: 30px; 
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo i {
            font-size: 48px;
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <div class="logo">
                <i class="bi bi-shield-lock-fill"></i>
                <h2 class="mt-2">Nova Senha</h2>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <?php 
                    switch ($_GET['error']) {
                        case 'match':
                            echo 'As senhas não coincidem.';
                            break;
                        case 'length':
                            echo 'A senha deve ter pelo menos 8 caracteres.';
                            break;
                        default:
                            echo 'Erro ao redefinir a senha.';
                    }
                    ?>
                </div>
            <?php endif; ?>

            <p class="text-muted mb-4">
                Digite sua nova senha abaixo. Use uma senha forte com pelo menos 8 caracteres.
            </p>
            
            <form action="process_new_password.php" method="POST">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($user_email); ?>">
                
                <div class="mb-3">
                    <label for="senha" class="form-label">
                        <i class="bi bi-lock"></i> Nova Senha
                    </label>
                    <input type="password" class="form-control" id="senha" name="senha" 
                           required minlength="8">
                </div>
                
                <div class="mb-4">
                    <label for="confirma_senha" class="form-label">
                        <i class="bi bi-lock-fill"></i> Confirmar Nova Senha
                    </label>
                    <input type="password" class="form-control" id="confirma_senha" 
                           name="confirma_senha" required minlength="8">
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Salvar Nova Senha
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

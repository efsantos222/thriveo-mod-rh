<?php
session_start();
require_once 'includes/menu.php';

// Se já estiver autenticado como superadmin, redireciona
if (isset($_SESSION['superadmin_authenticated']) && $_SESSION['superadmin_authenticated']) {
    header('Location: superadmin_panel.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrativo - Sistema DISC/MBTI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { padding-top: 50px; }
        .container { max-width: 500px; }
        .form-container { 
            background: #f8f9fa; 
            padding: 20px; 
            border-radius: 10px; 
        }
        .password-container {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: none;
            cursor: pointer;
            color: #6c757d;
        }
        .password-toggle:hover {
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <?php renderMenu(); ?>
    
    <div class="container">
        <div class="form-container">
            <h2 class="mb-4 text-center">Login Administrativo</h2>
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">Credenciais inválidas. Tente novamente.</div>
            <?php endif; ?>
            <form action="verify_superadmin.php" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="senha" class="form-label">Senha</label>
                    <div class="password-container">
                        <input type="password" class="form-control" id="senha" name="senha" required>
                        <button type="button" class="password-toggle" onclick="togglePasswordVisibility('senha')">
                            <i class="bi bi-eye" data-password-toggle="senha"></i>
                        </button>
                    </div>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Entrar</button>
                    <a href="index.php" class="btn btn-secondary ms-2">
                        <i class="bi bi-house-door"></i> Home
                    </a>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/form-utils.js"></script>
</body>
</html>

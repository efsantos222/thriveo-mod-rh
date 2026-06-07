<?php
session_start();

// Se já estiver logado, redireciona
if (isset($_SESSION['admin']) && $_SESSION['admin']) {
    header('Location: view_candidates.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - Sistema DISC</title>
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
                <i class="bi bi-key-fill"></i>
                <h2 class="mt-2">Recuperar Senha</h2>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    E-mail não encontrado no sistema.
                </div>
            <?php endif; ?>

            <p class="text-muted mb-4">
                Digite seu e-mail abaixo. Enviaremos instruções para redefinir sua senha.
            </p>
            
            <form action="process_reset_password.php" method="POST">
                <div class="mb-4">
                    <label for="email" class="form-label">
                        <i class="bi bi-envelope"></i> E-mail
                    </label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Enviar Instruções
                    </button>
                    <a href="admin_login.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar ao Login
                    </a>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

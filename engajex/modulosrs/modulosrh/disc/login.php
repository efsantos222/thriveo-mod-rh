<?php
require_once 'includes/auth.php';

$auth = new Auth();

// Se já estiver logado, redireciona
if ($auth->isLoggedIn()) {
    header('Location: ' . $auth->getRedirectPage());
    exit;
}

// Determinar o tipo de login
$role = $_GET['role'] ?? 'candidate';
$role = in_array($role, ['candidate', 'selector', 'superadmin']) ? $role : 'candidate';

// Processar login
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    error_log("Tentando login com - Email: $email, Role: $role");
    
    if ($auth->login($email, $password, $role)) {
        header('Location: ' . $auth->getRedirectPage());
        exit;
    } else {
        $error = 'Email ou senha inválidos';
    }
}

// Título da página
$titles = [
    'superadmin' => 'Login do Administrador',
    'selector' => 'Login do(a) Avaliador(a)',
    'candidate' => 'Login do Candidato',
];
$title = $titles[$role];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - Sistema de Testes</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #fff;
            border-bottom: none;
            text-align: center;
            padding: 30px 20px;
        }
        .login-icon {
            font-size: 48px;
            color: #0d6efd;
            margin-bottom: 15px;
        }
        .form-control {
            border-radius: 5px;
            padding: 12px;
        }
        .btn-login {
            padding: 12px;
            font-weight: 500;
        }
        .login-links {
            text-align: center;
            margin-top: 20px;
        }
        .login-links a {
            margin: 0 10px;
            text-decoration: none;
        }
    </style>
    <script>
        // Limpa os campos de login quando a página carrega
        window.onload = function() {
            document.getElementById('email').value = '';
            document.getElementById('password').value = '';
            
            // Remove dados do autocomplete
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-person-circle login-icon"></i>
                    <h4 class="mb-0"><?php echo $title; ?></h4>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" id="loginForm" autocomplete="off">
                        <input type="hidden" name="role" value="<?php echo htmlspecialchars($role); ?>">
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 btn-login">Entrar</button>
                    </form>
                </div>
            </div>

            <div class="login-links">
                <?php if ($role !== 'candidate'): ?>
                    <a href="login.php">Login do Candidato</a>
                <?php endif; ?>
                
                <?php if ($role !== 'selector'): ?>
                    <a href="login.php?role=selector">Login do(a) Avaliador(a)</a>
                <?php endif; ?>
                
                <?php if ($role !== 'superadmin'): ?>
                    <a href="login.php?role=superadmin">Login do Administrador</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

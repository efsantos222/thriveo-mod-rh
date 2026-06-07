<?php
// Garantir que nenhum output seja enviado antes dos headers
ob_start();

$pageTitle = "Login";
require_once TEMPLATES_PATH . '/base.php';

// Processar o login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once SRC_PATH . '/auth/AuthHandler.php';
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $authHandler = new \PDI\Auth\AuthHandler($pdo);
    
    // Criar admin se não existir
    if ($email === 'admin@proftest.com.br') {
        $authHandler->createAdmin($email, $password);
    }
    
    if ($authHandler->login($email, $password)) {
        header('Location: ?route=home');
        exit;
    } else {
        $error = 'Email ou senha inválidos';
        error_log("Falha no login para o email: " . $email);
    }
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Login</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="post">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Entrar</button>
                        </div>
                    </form>
                    
                    <div class="mt-3 text-center">
                        <p class="mb-0">Não tem uma conta? <a href="?route=register">Registre-se</a></p>
                        <p><a href="?route=forgot-password">Esqueceu sua senha?</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
require_once TEMPLATES_PATH . '/footer.php';
ob_end_flush();
?>

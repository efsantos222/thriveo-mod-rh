<?php
session_start();
require_once 'config.php';

$error = '';
$success = '';

// Se já estiver logado, redireciona
if (checkSession()) {
    $role = getUserRole();
    switch($role) {
        case 'administrador':
            header('Location: admin/dashboard.php');
            break;
        case 'aplicador':
            header('Location: aplicador/dashboard.php');
            break;
        case 'candidato':
            header('Location: candidato/dashboard.php');
            break;
    }
    exit();
}

// Processar login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = sanitize($_POST['email']);
    $senha = $_POST['senha'];
    
    if (empty($email) || empty($senha)) {
        $error = 'Por favor, preencha todos os campos.';
    } else {
        try {
            $pdo = getConnection();
            $stmt = $pdo->prepare("SELECT id, nome, email, senha, role, ativo FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && verifyPassword($senha, $user['senha'])) {
                if (!$user['ativo']) {
                    $error = 'Sua conta está desativada. Entre em contato com o administrador.';
                } else {
                    // Login bem-sucedido
                    $_SESSION[SESSION_NAME] = [
                        'id' => $user['id'],
                        'nome' => $user['nome'],
                        'email' => $user['email'],
                        'role' => $user['role']
                    ];
                    $_SESSION['last_activity'] = time();
                    
                    // Atualizar último login
                    $stmt = $pdo->prepare("UPDATE usuarios SET last_login = NOW() WHERE id = ?");
                    $stmt->execute([$user['id']]);
                    
                    // Log de atividade
                    $stmt = $pdo->prepare("INSERT INTO logs_atividades (usuario_id, acao, ip_address, user_agent) VALUES (?, 'login', ?, ?)");
                    $stmt->execute([$user['id'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);
                    
                    // Redirecionar baseado no papel
                    switch($user['role']) {
                        case 'administrador':
                            header('Location: admin/dashboard.php');
                            break;
                        case 'aplicador':
                            header('Location: aplicador/dashboard.php');
                            break;
                        case 'candidato':
                            header('Location: candidato/dashboard.php');
                            break;
                    }
                    exit();
                }
            } else {
                $error = 'Email ou senha inválidos.';
            }
        } catch(PDOException $e) {
            error_log($e->getMessage());
            $error = 'Erro ao processar login. Tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SYSTEM_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="assets/img/logo.png" alt="ProfTest" class="logo">
                <h1>Sistema de Testes Lógicos</h1>
                <p>Faça login para acessar o sistema</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="login-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required 
                           placeholder="seu@email.com" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" required 
                           placeholder="Digite sua senha">
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="lembrar">
                        <span>Lembrar de mim</span>
                    </label>
                </div>
                
                <button type="submit" name="login" class="btn btn-primary btn-block">
                    Entrar
                </button>
            </form>
            
            <div class="login-footer">
                <a href="recuperar-senha.php">Esqueceu sua senha?</a>
            </div>
            
            <div class="login-info">
                <p><strong>Credenciais de teste:</strong></p>
                <p>Admin: admin@proftest.com.br / Admin@2024</p>
            </div>
        </div>
    </div>
</body>
</html>

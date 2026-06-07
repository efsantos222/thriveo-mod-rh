<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();

// Verifica se está logado
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Processa o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validações
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Todos os campos são obrigatórios';
    } elseif ($new_password !== $confirm_password) {
        $error = 'A nova senha e a confirmação não coincidem';
    } elseif (strlen($new_password) < 8) {
        $error = 'A nova senha deve ter pelo menos 8 caracteres';
    } else {
        try {
            $db = getDbConnection();
            
            // Verifica se é candidato ou avaliador
            $table = $user_role === 'candidate' ? 'candidates' : 'users';
            
            // Busca a senha atual
            $stmt = $db->prepare("SELECT password FROM $table WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                $error = 'Usuário não encontrado';
            } else {
                // Verifica se a senha atual está correta
                $is_valid = false;
                if (strpos($user['password'], '$2y$') === 0) {
                    $is_valid = password_verify($current_password, $user['password']);
                } else {
                    $is_valid = ($current_password === $user['password']);
                }
                
                if (!$is_valid) {
                    $error = 'Senha atual incorreta';
                } else {
                    // Atualiza a senha
                    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE $table SET password = ? WHERE id = ?");
                    $stmt->execute([$new_hash, $user_id]);
                    
                    $success = 'Senha alterada com sucesso!';
                }
            }
        } catch (Exception $e) {
            $error = 'Erro ao alterar a senha. Por favor, tente novamente.';
            error_log($e->getMessage());
        }
    }
}

// Determina para onde voltar
$return_page = $user_role === 'candidate' ? 'candidate_panel.php' : 'selector_panel.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Senha - Sistema de Avaliação</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    
    <style>
        .password-container {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            user-select: none;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body">
                        <h3 class="card-title text-center mb-4">Alterar Senha</h3>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <?php echo $success; ?>
                                <script>
                                    setTimeout(function() {
                                        window.location.href = '<?php echo $return_page; ?>';
                                    }, 2000);
                                </script>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3 password-container">
                                <label for="current_password" class="form-label">Senha Atual</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                                <i class="bi bi-eye-slash toggle-password" data-target="current_password"></i>
                            </div>
                            
                            <div class="mb-3 password-container">
                                <label for="new_password" class="form-label">Nova Senha</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
                                <i class="bi bi-eye-slash toggle-password" data-target="new_password"></i>
                                <div class="form-text">A senha deve ter pelo menos 8 caracteres</div>
                            </div>
                            
                            <div class="mb-3 password-container">
                                <label for="confirm_password" class="form-label">Confirmar Nova Senha</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                <i class="bi bi-eye-slash toggle-password" data-target="confirm_password"></i>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Alterar Senha</button>
                                <a href="<?php echo $return_page; ?>" class="btn btn-outline-secondary">Voltar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(function(toggle) {
            toggle.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                
                if (input.type === 'password') {
                    input.type = 'text';
                    this.classList.remove('bi-eye-slash');
                    this.classList.add('bi-eye');
                } else {
                    input.type = 'password';
                    this.classList.remove('bi-eye');
                    this.classList.add('bi-eye-slash');
                }
            });
        });
        
        // Form validation
        (function () {
            'use strict'
            
            var forms = document.querySelectorAll('.needs-validation')
            
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html>

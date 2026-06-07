<?php
session_start();

// Verificar se está logado como superadmin
if (!isset($_SESSION['superadmin']) || !$_SESSION['superadmin']) {
    header('Location: superadmin_login.php');
    exit;
}

$admins_file = 'resultados/admins.csv';
$success_message = '';
$error_message = '';

// Criar arquivo se não existir
if (!file_exists($admins_file)) {
    $fp = fopen($admins_file, 'w');
    fputcsv($fp, ['nome', 'email', 'senha']);
    fclose($fp);
}

// Processar alteração de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'change_password') {
        $email = $_POST['email'];
        $nova_senha = $_POST['nova_senha'];
        
        if (strlen($nova_senha) >= 6) {
            $temp_file = 'resultados/admins_temp.csv';
            $senha_alterada = false;
            
            $fp_read = fopen($admins_file, 'r');
            $fp_write = fopen($temp_file, 'w');
            
            // Copiar cabeçalho
            fputcsv($fp_write, fgetcsv($fp_read));
            
            while (($data = fgetcsv($fp_read)) !== FALSE) {
                if ($data[1] === $email) {
                    $data[2] = password_hash($nova_senha, PASSWORD_DEFAULT);
                    $senha_alterada = true;
                    
                    // Enviar e-mail com a nova senha
                    $to = $email;
                    $subject = "Nova Senha - Sistema DISC";
                    $message = "Olá {$data[0]},\n\n";
                    $message .= "Sua senha foi alterada pelo administrador.\n\n";
                    $message .= "Nova senha: {$nova_senha}\n\n";
                    $message .= "Por favor, faça login com esta nova senha.\n";
                    $message .= "Recomendamos que você altere esta senha após o primeiro acesso.\n\n";
                    $message .= "Atenciosamente,\nEquipe Sistema DISC";
                    $headers = "From: noreply@sistemadisc.com";
                    
                    mail($to, $subject, $message, $headers);
                }
                fputcsv($fp_write, $data);
            }
            
            fclose($fp_read);
            fclose($fp_write);
            
            if ($senha_alterada) {
                unlink($admins_file);
                rename($temp_file, $admins_file);
                $success_message = "Senha alterada com sucesso! Um e-mail foi enviado para o selecionador com a nova senha.";
            } else {
                unlink($temp_file);
                $error_message = "Selecionador não encontrado.";
            }
        } else {
            $error_message = "A senha deve ter pelo menos 6 caracteres.";
        }
    }
}

// Carregar lista de selecionadores
$admins = [];
if (file_exists($admins_file)) {
    $fp = fopen($admins_file, 'r');
    fgetcsv($fp); // Pular cabeçalho
    
    while (($data = fgetcsv($fp)) !== FALSE) {
        $admins[] = [
            'nome' => $data[0],
            'email' => $data[1]
        ];
    }
    fclose($fp);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Selecionadores - Sistema DISC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { 
            padding-top: 20px; 
            background-color: #f5f5f5;
        }
        .content-container { 
            background: #fff; 
            padding: 30px; 
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .admin-card {
            transition: transform 0.2s;
            margin-bottom: 20px;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .admin-card:hover {
            transform: translateY(-5px);
        }
        .btn-change-password {
            background-color: #e67e22;
            border-color: #e67e22;
            color: white;
        }
        .btn-change-password:hover {
            background-color: #d35400;
            border-color: #d35400;
            color: white;
        }
        .modal-header {
            background-color: #e67e22;
            color: white;
        }
        .modal-header .btn-close {
            color: white;
        }
        .password-info {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-people-fill"></i> Gerenciar Selecionadores</h2>
                <div>
                    <a href="register_admin.php" class="btn btn-primary">
                        <i class="bi bi-person-plus-fill"></i> Novo Selecionador
                    </a>
                    <a href="superadmin_dashboard.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($admins)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle-fill"></i>
                    Nenhum selecionador cadastrado.
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($admins as $admin): ?>
                        <div class="col-md-6">
                            <div class="card admin-card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="bi bi-person-circle"></i>
                                        <?php echo htmlspecialchars($admin['nome']); ?>
                                    </h5>
                                    <p class="card-text">
                                        <i class="bi bi-envelope"></i>
                                        <?php echo htmlspecialchars($admin['email']); ?>
                                    </p>
                                    <button type="button" class="btn btn-change-password" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#senhaModal<?php echo md5($admin['email']); ?>">
                                        <i class="bi bi-key"></i> Alterar Senha
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Modal de Alteração de Senha -->
                        <div class="modal fade" id="senhaModal<?php echo md5($admin['email']); ?>" 
                             tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            <i class="bi bi-key"></i>
                                            Alterar Senha - <?php echo htmlspecialchars($admin['nome']); ?>
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" 
                                                aria-label="Close"></button>
                                    </div>
                                    <form action="" method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="action" value="change_password">
                                            <input type="hidden" name="email" 
                                                   value="<?php echo htmlspecialchars($admin['email']); ?>">
                                            
                                            <div class="mb-3">
                                                <label for="nova_senha" class="form-label">Nova Senha</label>
                                                <input type="text" class="form-control" name="nova_senha" 
                                                       required minlength="6">
                                                <div class="password-info">
                                                    <i class="bi bi-info-circle"></i>
                                                    A senha deve ter pelo menos 6 caracteres.
                                                    O selecionador receberá um e-mail com a nova senha.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" 
                                                    data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-change-password">
                                                <i class="bi bi-check-lg"></i> Salvar Alterações
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

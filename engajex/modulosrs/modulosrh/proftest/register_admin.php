<?php
session_start();

// Verificar se está logado como superadmin
if (!isset($_SESSION['superadmin']) || !$_SESSION['superadmin']) {
    header('Location: superadmin_login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Selecionador - Sistema DISC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { 
            padding-top: 20px; 
            background-color: #f5f5f5;
        }
        .container { max-width: 500px; }
        .content-container { 
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
        <?php include 'header.php'; ?>
        <div class="content-container">
            <div class="logo">
                <i class="bi bi-person-plus-fill"></i>
                <h2 class="mt-2">Cadastrar Selecionador</h2>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <?php 
                    switch ($_GET['error']) {
                        case '1':
                            echo 'E-mail já cadastrado.';
                            break;
                        case '2':
                            echo 'As senhas não coincidem.';
                            break;
                        default:
                            echo 'Erro ao realizar o cadastro.';
                    }
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    Selecionador cadastrado com sucesso!
                </div>
            <?php endif; ?>
            
            <form action="process_admin.php" method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="nome" class="form-label">
                        <i class="bi bi-person"></i> Nome Completo
                    </label>
                    <input type="text" class="form-control" id="nome" name="nome" required>
                    <div class="invalid-feedback">
                        Por favor, informe o nome completo.
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="bi bi-envelope"></i> E-mail
                    </label>
                    <input type="email" class="form-control" id="email" name="email" required>
                    <div class="invalid-feedback">
                        Por favor, informe um e-mail válido.
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="senha" class="form-label">
                        <i class="bi bi-lock"></i> Senha
                    </label>
                    <input type="password" class="form-control" id="senha" name="senha" 
                           required minlength="6">
                    <div class="form-text">
                        A senha deve ter pelo menos 6 caracteres.
                    </div>
                    <div class="invalid-feedback">
                        A senha deve ter pelo menos 6 caracteres.
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="confirma_senha" class="form-label">
                        <i class="bi bi-lock-fill"></i> Confirme a Senha
                    </label>
                    <input type="password" class="form-control" id="confirma_senha" 
                           name="confirma_senha" required minlength="6">
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-person-plus"></i> Cadastrar Selecionador
                    </button>
                    <a href="manage_admins.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/form_utils.js"></script>
    <script>
        // Validação do formulário
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

<?php
session_start();
require_once 'includes/menu.php';

// Verificar se está logado como admin ou superadmin
if ((!isset($_SESSION['admin_authenticated']) || !$_SESSION['admin_authenticated']) && 
    (!isset($_SESSION['superadmin_authenticated']) || !$_SESSION['superadmin_authenticated'])) {
    header('Location: admin_login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Candidato - Sistema DISC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { 
            padding: 20px;
            background-color: #f5f5f5;
        }
        .content-container {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 0 auto;
        }
        .form-label {
            font-weight: 500;
            color: #2c3e50;
        }
        .form-text {
            color: #7f8c8d;
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
    
    <div class="container mt-4">
        <div class="content-container">
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php
                    switch ($_GET['error']) {
                        case 'missing_fields':
                            echo 'Por favor, preencha todos os campos obrigatórios.';
                            break;
                        case 'invalid_email':
                            echo 'Por favor, informe um e-mail válido.';
                            break;
                        case 'email_exists':
                            echo 'Este e-mail já está cadastrado.';
                            break;
                        case 'file_error':
                            echo 'Erro ao salvar os dados. Por favor, tente novamente.';
                            break;
                        case 'invalid_password':
                            echo 'A senha deve ter no mínimo 6 caracteres.';
                            break;
                        default:
                            echo 'Ocorreu um erro. Por favor, tente novamente.';
                    }
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Candidato registrado com sucesso!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <h2 class="mb-4 text-center">Registrar Novo Candidato DISC</h2>

            <form action="process_candidate_disc.php" method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="nome" class="form-label">
                        <i class="bi bi-person"></i> Nome do Candidato
                    </label>
                    <input type="text" class="form-control" id="nome" name="nome" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="bi bi-envelope"></i> E-mail
                    </label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>

                <div class="mb-3">
                    <label for="senha" class="form-label">
                        <i class="bi bi-key"></i> Senha
                    </label>
                    <div class="password-container">
                        <input type="password" class="form-control" id="senha" name="senha" required>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="form-text">A senha deve ter no mínimo 6 caracteres.</div>
                </div>

                <div class="mb-3">
                    <label for="cargo" class="form-label">
                        <i class="bi bi-briefcase"></i> Cargo
                    </label>
                    <input type="text" class="form-control" id="cargo" name="cargo" required>
                </div>

                <div class="mb-3">
                    <label for="solicitante" class="form-label">
                        <i class="bi bi-person-badge"></i> Solicitante
                    </label>
                    <input type="text" class="form-control" id="solicitante" name="solicitante" required>
                </div>

                <div class="mb-3">
                    <label for="observacoes" class="form-label">
                        <i class="bi bi-chat-left-text"></i> Observações
                    </label>
                    <textarea class="form-control" id="observacoes" name="observacoes" rows="3"></textarea>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-person-plus"></i> Registrar Candidato
                    </button>
                    <a href="view_candidates_disc.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const senhaInput = document.getElementById('senha');
            const toggleButton = document.querySelector('.password-toggle i');
            
            if (senhaInput.type === 'password') {
                senhaInput.type = 'text';
                toggleButton.classList.remove('bi-eye');
                toggleButton.classList.add('bi-eye-slash');
            } else {
                senhaInput.type = 'password';
                toggleButton.classList.remove('bi-eye-slash');
                toggleButton.classList.add('bi-eye');
            }
        }
        
        // Validação do formulário
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
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

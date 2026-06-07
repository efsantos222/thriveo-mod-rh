<?php
session_start();

// Verificar se está logado como admin ou superadmin
if (!isset($_SESSION['admin_authenticated']) && !isset($_SESSION['superadmin_authenticated'])) {
    header('Location: admin_login.php');
    exit;
}

$error = '';
$success = '';

// Função para gerar senha aleatória
function gerarSenha($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $password;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $empresa = $_POST['empresa'];
    $cargo = $_POST['cargo'];
    $senha = isset($_POST['senha']) ? $_POST['senha'] : gerarSenha();

    // Validar campos
    if (empty($nome) || empty($email) || empty($empresa) || empty($cargo)) {
        $error = "Todos os campos são obrigatórios.";
    } else {
        $candidatos_file = 'resultados/candidatos_bigfive.csv';
        $senhas_file = 'senhas/senhas_bigfive.csv';
        
        // Criar diretório se não existir
        if (!file_exists('resultados')) {
            mkdir('resultados', 0777, true);
        }
        if (!file_exists('senhas')) {
            mkdir('senhas', 0777, true);
        }

        // Verificar se o arquivo de candidatos existe
        $is_new_file = !file_exists($candidatos_file);
        
        // Verificar se o email já está cadastrado
        $email_exists = false;
        if (!$is_new_file) {
            if (($handle = fopen($candidatos_file, "r")) !== FALSE) {
                while (($data = fgetcsv($handle)) !== FALSE) {
                    if (isset($data[4]) && $data[4] === $email) {
                        $email_exists = true;
                        break;
                    }
                }
                fclose($handle);
            }
        }

        if ($email_exists) {
            $error = "Este email já está cadastrado.";
        } else {
            // Adicionar candidato
            $fp = fopen($candidatos_file, 'a');
            if ($is_new_file) {
                fputcsv($fp, ['data', 'selecionador', 'selecionador_email', 'nome', 'email', 'empresa', 'cargo', 'status']);
            }
            fputcsv($fp, [
                date('Y-m-d H:i:s'),
                $_SESSION['admin_nome'],
                $_SESSION['admin_email'],
                $nome,
                $email,
                $empresa,
                $cargo,
                'Pendente'
            ]);
            fclose($fp);

            // Salvar senha em texto puro (sem hash)
            if (!file_exists($senhas_file)) {
                $fp = fopen($senhas_file, 'w');
                fputcsv($fp, ['email', 'senha']);
                fclose($fp);
            }
            $fp = fopen($senhas_file, 'a');
            fputcsv($fp, [$email, $senha]);
            fclose($fp);

            // Enviar email
            $to = $email;
            $subject = "Avaliação Big Five - Acesso";
            $message = "Olá $nome,\n\n";
            $message .= "Você foi registrado para realizar a avaliação Big Five.\n\n";
            $message .= "Acesse o link abaixo e utilize as credenciais fornecidas:\n";
            $message .= "Link: https://proftest.com.br/disc/login_bigfive.php\n";
            $message .= "Email: $email\n";
            $message .= "Senha: $senha\n\n";
            $message .= "Atenciosamente,\n";
            $message .= $_SESSION['admin_nome'];

            $headers = "From: " . $_SESSION['admin_email'] . "\r\n";
            $headers .= "Reply-To: " . $_SESSION['admin_email'] . "\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();

            if (mail($to, $subject, $message, $headers)) {
                $success = "Candidato registrado com sucesso! Um email foi enviado com as instruções.";
            } else {
                $success = "Candidato registrado com sucesso! Não foi possível enviar o email. Senha: " . $senha;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Candidato Big Five</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <?php include 'header.php'; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Registrar Novo Candidato Big Five</h2>
            </div>
            <div class="card-body">
                <form method="POST" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                            <div class="invalid-feedback">
                                Por favor, informe o nome.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback">
                                Por favor, informe um email válido.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="empresa" class="form-label">Empresa</label>
                            <input type="text" class="form-control" id="empresa" name="empresa" required>
                            <div class="invalid-feedback">
                                Por favor, informe a empresa.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="cargo" class="form-label">Cargo</label>
                            <input type="text" class="form-control" id="cargo" name="cargo" required>
                            <div class="invalid-feedback">
                                Por favor, informe o cargo.
                            </div>
                        </div>
                        <?php if (isset($_SESSION['superadmin_authenticated'])): ?>
                        <div class="col-md-6">
                            <label for="senha" class="form-label">Senha</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="senha" name="senha" value="<?php echo gerarSenha(); ?>">
                                <button type="button" class="btn btn-outline-secondary" onclick="gerarNovaSenha()">
                                    <i class="bi bi-arrow-clockwise"></i> Gerar Nova
                                </button>
                            </div>
                            <div class="form-text">Deixe em branco para gerar automaticamente.</div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-person-plus"></i> Registrar Candidato
                        </button>
                        <a href="<?php echo isset($_SESSION['superadmin_authenticated']) ? 'superadmin_panel.php#candidatos-bigfive' : 'admin_dashboard.php'; ?>" 
                           class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Voltar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
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

        // Gerar nova senha
        function gerarNovaSenha() {
            const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            let senha = '';
            for (let i = 0; i < 8; i++) {
                senha += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            document.getElementById('senha').value = senha;
        }
    </script>
</body>
</html>

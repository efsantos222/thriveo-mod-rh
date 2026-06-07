<?php
session_start();

// Verificar se está logado como admin
if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
    header('Location: admin_login.php');
    exit;
}

// Verificar se o e-mail foi fornecido
if (!isset($_GET['email']) || empty($_GET['email'])) {
    header('Location: view_candidates.php');
    exit;
}

$email = $_GET['email'];
$candidato_nome = '';
$candidato_encontrado = false;

// Verificar se o candidato existe e pertence ao selecionador
$candidatos_file = 'resultados/candidatos.csv';
if (file_exists($candidatos_file)) {
    $fp = fopen($candidatos_file, 'r');
    fgetcsv($fp); // Pular cabeçalho
    
    while (($data = fgetcsv($fp)) !== FALSE) {
        if ($data[4] === $email && $data[2] === $_SESSION['admin_email']) {
            $candidato_nome = $data[3];
            $candidato_encontrado = true;
            break;
        }
    }
    fclose($fp);
}

if (!$candidato_encontrado) {
    header('Location: view_candidates.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nova_senha = $_POST['nova_senha'];
    
    if (strlen($nova_senha) >= 6) {
        $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        $temp_file = 'resultados/candidatos_temp.csv';
        
        $fp_read = fopen($candidatos_file, 'r');
        $fp_write = fopen($temp_file, 'w');
        
        // Copiar cabeçalho
        fputcsv($fp_write, fgetcsv($fp_read));
        
        while (($data = fgetcsv($fp_read)) !== FALSE) {
            if ($data[4] === $email) {
                $data[5] = $senha_hash;
            }
            fputcsv($fp_write, $data);
        }
        
        fclose($fp_read);
        fclose($fp_write);
        
        // Substituir arquivo original
        unlink($candidatos_file);
        rename($temp_file, $candidatos_file);
        
        // Enviar e-mail para o candidato
        $subject = "Sistema DISC - Nova Senha";
        $body = "Olá {$candidato_nome},<br><br>";
        $body .= "Sua senha no Sistema DISC foi alterada pelo selecionador.<br><br>";
        $body .= "Nova senha: {$nova_senha}<br><br>";
        $body .= "Acesse o sistema com seu e-mail e esta nova senha.<br><br>";
        $body .= "Atenciosamente,<br>Sistema DISC";
        
        $headers = [
            'From' => 'sistema@seudominio.com',
            'Reply-To' => 'sistema@seudominio.com',
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Mailer' => 'PHP/' . phpversion()
        ];
        
        mail($email, $subject, $body, $headers);
        
        header('Location: view_candidates.php?password_changed=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Senha do Candidato - Sistema DISC</title>
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
        <div class="content-container">
            <div class="logo">
                <i class="bi bi-key-fill"></i>
                <h2 class="mt-2">Alterar Senha do Candidato</h2>
            </div>

            <div class="alert alert-info">
                <i class="bi bi-info-circle-fill"></i>
                Alterando senha para: <strong><?php echo htmlspecialchars($candidato_nome); ?></strong>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    A senha deve ter pelo menos 6 caracteres.
                </div>
            <?php endif; ?>
            
            <form action="" method="POST" class="needs-validation" novalidate>
                <div class="mb-4">
                    <label for="nova_senha" class="form-label">
                        <i class="bi bi-lock"></i> Nova Senha
                    </label>
                    <input type="text" class="form-control" id="nova_senha" name="nova_senha" 
                           required minlength="6" autocomplete="off">
                    <div class="form-text">
                        A senha deve ter pelo menos 6 caracteres.
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Alterar Senha
                    </button>
                    <a href="view_candidates.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
            </form>
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
    </script>
</body>
</html>

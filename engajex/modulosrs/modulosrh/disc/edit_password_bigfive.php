<?php
session_start();
if (!isset($_SESSION['superadmin_authenticated'])) {
    header('Location: superadmin_login.php');
    exit;
}

$message = '';
$error = '';

if (isset($_GET['email'])) {
    $email = $_GET['email'];
    $candidato = null;
    
    // Verificar se o candidato existe
    $candidatos_file = 'resultados/candidatos_bigfive.csv';
    if (file_exists($candidatos_file)) {
        if (($handle = fopen($candidatos_file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle)) !== FALSE) {
                if ($data[4] === $email) { // Email está na quinta coluna
                    $candidato = [
                        'nome' => $data[3],
                        'email' => $data[4]
                    ];
                    break;
                }
            }
            fclose($handle);
        }
    }
    
    if ($candidato === null) {
        $error = "Candidato não encontrado.";
    }
} else {
    header('Location: superadmin_panel.php#candidatos-bigfive');
    exit;
}

// Processar alteração de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nova_senha'])) {
    $nova_senha = trim($_POST['nova_senha']);
    
    if (strlen($nova_senha) < 6) {
        $error = "A senha deve ter pelo menos 6 caracteres.";
    } else {
        $senhas_file = 'senhas/senhas_bigfive.csv';
        $linhas = [];
        $senha_atualizada = false;
        
        if (file_exists($senhas_file)) {
            if (($handle = fopen($senhas_file, "r")) !== FALSE) {
                while (($data = fgetcsv($handle)) !== FALSE) {
                    if ($data[0] === $email) {
                        $data[1] = $nova_senha;
                        $senha_atualizada = true;
                    }
                    $linhas[] = $data;
                }
                fclose($handle);
                
                if ($senha_atualizada) {
                    $fp = fopen($senhas_file, 'w');
                    foreach ($linhas as $linha) {
                        fputcsv($fp, $linha);
                    }
                    fclose($fp);
                    $message = "Senha atualizada com sucesso!";
                } else {
                    $error = "Erro ao atualizar a senha.";
                }
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
    <title>Alterar Senha - Big Five</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Alterar Senha - Big Five</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($message): ?>
                            <div class="alert alert-success"><?php echo $message; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($candidato): ?>
                            <div class="mb-4">
                                <h5>Candidato:</h5>
                                <p class="mb-1"><strong>Nome:</strong> <?php echo htmlspecialchars($candidato['nome']); ?></p>
                                <p class="mb-0"><strong>Email:</strong> <?php echo htmlspecialchars($candidato['email']); ?></p>
                            </div>
                            
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="nova_senha" class="form-label">Nova Senha</label>
                                    <input type="text" class="form-control" id="nova_senha" name="nova_senha" required minlength="6">
                                    <div class="form-text">A senha deve ter pelo menos 6 caracteres.</div>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <a href="superadmin_panel.php#candidatos-bigfive" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Voltar
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-key"></i> Alterar Senha
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

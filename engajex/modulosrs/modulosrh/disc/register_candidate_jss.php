<?php
session_start();
require_once 'includes/menu.php';

// Verificar se o usuário está autenticado como admin ou superadmin
if ((!isset($_SESSION['admin_authenticated']) || !$_SESSION['admin_authenticated']) && 
    (!isset($_SESSION['superadmin_authenticated']) || !$_SESSION['superadmin_authenticated'])) {
    header('Location: login.php');
    exit;
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar campos obrigatórios
    if (empty($_POST['nome']) || empty($_POST['email']) || empty($_POST['cargo']) || empty($_POST['senha'])) {
        $error_message = 'Todos os campos são obrigatórios.';
    } else {
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $cargo = trim($_POST['cargo']);
        $senha = trim($_POST['senha']);
        $observacoes = trim($_POST['observacoes'] ?? '');
        $data = date('Y-m-d H:i:s');
        $admin_email = $_SESSION['admin_email'];
        $solicitante = $_SESSION['admin_nome'];

        // Validar e-mail
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'E-mail inválido.';
        } else {
            // Verificar se o e-mail já existe
            $candidatos_file = 'resultados/candidatos_jss.csv';
            $email_exists = false;

            if (file_exists($candidatos_file)) {
                $fp = fopen($candidatos_file, 'r');
                while (($data_row = fgetcsv($fp)) !== FALSE) {
                    if ($data_row[4] === $email) {
                        $email_exists = true;
                        break;
                    }
                }
                fclose($fp);
            }

            if ($email_exists) {
                $error_message = 'Este e-mail já está cadastrado.';
            } else {
                // Criar arquivo de candidatos se não existir
                $file_exists = file_exists($candidatos_file);
                $fp = fopen($candidatos_file, 'a');

                if (!$file_exists) {
                    // Escrever cabeçalho se for um novo arquivo
                    fputcsv($fp, ['Data', 'Solicitante', 'Email_Solicitante', 'Nome', 'Email', 'Senha', 'Cargo', 'Observacoes', 'Status']);
                }

                // Adicionar novo candidato
                $new_candidate = [
                    $data,
                    $solicitante,
                    $admin_email,
                    $nome,
                    $email,
                    password_hash($senha, PASSWORD_DEFAULT),
                    $cargo,
                    $observacoes,
                    'Pendente'
                ];

                fputcsv($fp, $new_candidate);
                fclose($fp);

                // Enviar e-mail para o candidato (você pode implementar isso depois)
                // mail($email, 'Avaliação JSS', 'Mensagem com as instruções...');

                $success_message = 'Candidato registrado com sucesso!';
                
                // Limpar os campos após o sucesso
                $_POST = array();
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
    <title>Registrar Novo Candidato - JSS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php renderMenu(); ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Registrar Novo Candidato JSS</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success_message): ?>
                            <div class="alert alert-success"><?php echo $success_message; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome Completo</label>
                                <input type="text" class="form-control" id="nome" name="nome" 
                                       value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="cargo" class="form-label">Cargo</label>
                                <input type="text" class="form-control" id="cargo" name="cargo" 
                                       value="<?php echo htmlspecialchars($_POST['cargo'] ?? ''); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="senha" class="form-label">Senha</label>
                                <input type="password" class="form-control" id="senha" name="senha" 
                                       minlength="6" required>
                                <small class="text-muted">Mínimo de 6 caracteres</small>
                            </div>

                            <div class="mb-3">
                                <label for="observacoes" class="form-label">Observações (opcional)</label>
                                <textarea class="form-control" id="observacoes" name="observacoes" 
                                          rows="3"><?php echo htmlspecialchars($_POST['observacoes'] ?? ''); ?></textarea>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="view_candidates_jss.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Voltar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-person-plus"></i> Registrar Candidato
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

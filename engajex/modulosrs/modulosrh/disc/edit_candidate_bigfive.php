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
    
    // Buscar dados do candidato
    $candidatos_file = 'resultados/candidatos_bigfive.csv';
    if (file_exists($candidatos_file)) {
        if (($handle = fopen($candidatos_file, "r")) !== FALSE) {
            $header = fgetcsv($handle);
            while (($data = fgetcsv($handle)) !== FALSE) {
                if ($data[4] === $email) { // Email está na quinta coluna
                    $candidato = [
                        'data_criacao' => $data[0],
                        'selecionador_nome' => $data[1],
                        'selecionador_email' => $data[2],
                        'nome' => $data[3],
                        'email' => $data[4],
                        'empresa' => $data[5],
                        'cargo' => $data[6],
                        'status' => isset($data[7]) ? $data[7] : 'pendente'
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

// Processar formulário de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novo_nome = $_POST['nome'];
    $nova_empresa = $_POST['empresa'];
    $novo_cargo = $_POST['cargo'];
    $novo_status = $_POST['status'];
    
    $linhas = [];
    $atualizado = false;
    
    if (($handle = fopen($candidatos_file, "r")) !== FALSE) {
        while (($data = fgetcsv($handle)) !== FALSE) {
            if ($data[4] === $email) {
                $data[3] = $novo_nome;
                $data[5] = $nova_empresa;
                $data[6] = $novo_cargo;
                $data[7] = $novo_status;
                $atualizado = true;
            }
            $linhas[] = $data;
        }
        fclose($handle);
        
        if ($atualizado) {
            $fp = fopen($candidatos_file, 'w');
            foreach ($linhas as $linha) {
                fputcsv($fp, $linha);
            }
            fclose($fp);
            $message = "Candidato atualizado com sucesso!";
            
            // Atualizar dados do candidato na página
            $candidato['nome'] = $novo_nome;
            $candidato['empresa'] = $nova_empresa;
            $candidato['cargo'] = $novo_cargo;
            $candidato['status'] = $novo_status;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Candidato Big Five</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Editar Candidato Big Five</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php elseif ($message): ?>
                            <div class="alert alert-success"><?php echo $message; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($candidato): ?>
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="nome" class="form-label">Nome</label>
                                    <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($candidato['nome']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($candidato['email']); ?>" readonly>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="empresa" class="form-label">Empresa</label>
                                    <input type="text" class="form-control" id="empresa" name="empresa" value="<?php echo htmlspecialchars($candidato['empresa']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="cargo" class="form-label">Cargo</label>
                                    <input type="text" class="form-control" id="cargo" name="cargo" value="<?php echo htmlspecialchars($candidato['cargo']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="pendente" <?php echo $candidato['status'] === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                                        <option value="completed" <?php echo $candidato['status'] === 'completed' ? 'selected' : ''; ?>>Concluído</option>
                                    </select>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <a href="superadmin_panel.php#candidatos-bigfive" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Voltar
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Salvar Alterações
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

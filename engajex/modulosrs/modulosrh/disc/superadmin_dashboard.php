<?php
session_start();

// Verificar se está logado como superadmin
if (!isset($_SESSION['superadmin']) || !$_SESSION['superadmin']) {
    header('Location: superadmin_login.php');
    exit;
}

// Processar alteração de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'change_password') {
        $email = $_POST['email'];
        $nova_senha = $_POST['nova_senha'];
        $admins_file = 'resultados/admins.csv';
        
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
                $success_message = "Senha alterada com sucesso! Um e-mail foi enviado para o selecionador.";
            } else {
                unlink($temp_file);
                $error_message = "Selecionador não encontrado.";
            }
        } else {
            $error_message = "A senha deve ter pelo menos 6 caracteres.";
        }
    }
}

// Carregar estatísticas
$total_selecionadores = 0;
$total_candidatos = 0;
$total_avaliacoes = 0;

// Contar selecionadores e carregar lista
$admins_file = 'resultados/admins.csv';
$selecionadores = [];
if (file_exists($admins_file)) {
    $fp = fopen($admins_file, 'r');
    fgetcsv($fp); // Pular cabeçalho
    while (($data = fgetcsv($fp)) !== FALSE) {
        $total_selecionadores++;
        $selecionadores[] = [
            'nome' => $data[0],
            'email' => $data[1]
        ];
    }
    fclose($fp);
}

// Contar candidatos e avaliações
$candidatos = [];
$candidatos_file = 'resultados/candidatos.csv';
if (file_exists($candidatos_file)) {
    $fp = fopen($candidatos_file, 'r');
    fgetcsv($fp); // Pular cabeçalho
    
    while (($data = fgetcsv($fp)) !== FALSE) {
        $total_candidatos++;
        $avaliacao_file = 'resultados/' . str_replace(['@', '.'], '_', $data[1]) . '_avaliacao.csv';
        
        // Adicionar candidato à lista
        $candidatos[] = [
            'nome' => $data[0],
            'email' => $data[1],
            'cargo' => $data[2],
            'selecionador' => $data[3],
            'status' => file_exists($avaliacao_file) ? 'Concluído' : 'Pendente'
        ];
        
        if (file_exists($avaliacao_file)) {
            $total_avaliacoes++;
        }
    }
    fclose($fp);
}

// Ordenar candidatos por status (pendentes primeiro) e nome
usort($candidatos, function($a, $b) {
    if ($a['status'] === $b['status']) {
        return strcmp($a['nome'], $b['nome']);
    }
    return $a['status'] === 'Pendente' ? -1 : 1;
});
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - Sistema DISC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
            margin-bottom: 20px;
        }
        .stats-card {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .stats-card i {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        .stats-card .number {
            font-size: 2rem;
            font-weight: bold;
        }
        .stats-card .label {
            font-size: 1rem;
            opacity: 0.9;
        }
        .action-card {
            border: none;
            border-radius: 10px;
            transition: transform 0.3s;
            height: 100%;
        }
        .action-card:hover {
            transform: translateY(-5px);
        }
        .action-card .card-body {
            padding: 25px;
        }
        .action-card i {
            font-size: 2rem;
            margin-bottom: 15px;
            color: #007bff;
        }
        .status-badge {
            font-size: 0.875rem;
            padding: 0.5em 0.8em;
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
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill"></i> <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Estatísticas -->
        <div class="content-container">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">Painel Administrativo</h2>
                    <p class="text-muted">Gerencie selecionadores, candidatos e visualize estatísticas.</p>
                </div>
                <div>
                    <a href="comparison.php" class="btn btn-info">
                        <i class="bi bi-info-circle"></i> Entenda as diferenças entre DISC e MBTI
                    </a>
                </div>
            </div>

            <!-- Estatísticas -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="stats-card text-center">
                        <i class="bi bi-people"></i>
                        <div class="number"><?php echo $total_selecionadores; ?></div>
                        <div class="label">Selecionadores</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card text-center" style="background: linear-gradient(45deg, #6f42c1, #4e2b89);">
                        <i class="bi bi-person-badge"></i>
                        <div class="number"><?php echo $total_candidatos; ?></div>
                        <div class="label">Candidatos</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card text-center" style="background: linear-gradient(45deg, #28a745, #1e7e34);">
                        <i class="bi bi-check-circle"></i>
                        <div class="number"><?php echo $total_avaliacoes; ?></div>
                        <div class="label">Avaliações Concluídas</div>
                    </div>
                </div>
            </div>

            <!-- Ações Rápidas -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card action-card">
                        <div class="card-body text-center">
                            <i class="bi bi-people-fill"></i>
                            <h5 class="card-title">Selecionadores</h5>
                            <p class="card-text">
                                Gerencie selecionadores e senhas
                            </p>
                            <div class="d-grid gap-2">
                                <a href="register_admin.php" class="btn btn-primary">
                                    <i class="bi bi-person-plus"></i> Novo Selecionador
                                </a>
                                <button type="button" class="btn btn-change-password" data-bs-toggle="modal" data-bs-target="#selecionadoresModal">
                                    <i class="bi bi-key"></i> Gerenciar Senhas
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card action-card">
                        <div class="card-body text-center">
                            <i class="bi bi-info-circle"></i>
                            <h5 class="card-title">DISC vs. MBTI</h5>
                            <p class="card-text">
                                Compare as metodologias
                            </p>
                            <a href="comparison.php" class="btn btn-info">
                                <i class="bi bi-book"></i> Saiba Mais
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card action-card">
                        <div class="card-body text-center">
                            <i class="bi bi-graph-up"></i>
                            <h5 class="card-title">Relatórios</h5>
                            <p class="card-text">
                                Visualize estatísticas e gráficos
                            </p>
                            <a href="reports.php" class="btn btn-primary">
                                <i class="bi bi-file-earmark-text"></i> Ver Relatórios
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Candidatos -->
        <div class="content-container">
            <h3 class="mb-4">
                <i class="bi bi-people"></i> Candidatos
            </h3>
            <div class="table-responsive">
                <table class="table table-hover" id="candidatosTable">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Cargo</th>
                            <th>Selecionador</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($candidatos as $candidato): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($candidato['nome']); ?></td>
                                <td><?php echo htmlspecialchars($candidato['email']); ?></td>
                                <td><?php echo htmlspecialchars($candidato['cargo']); ?></td>
                                <td><?php echo htmlspecialchars($candidato['selecionador']); ?></td>
                                <td>
                                    <span class="badge <?php echo $candidato['status'] === 'Concluído' ? 'bg-success' : 'bg-warning'; ?> status-badge">
                                        <?php echo $candidato['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($candidato['status'] === 'Concluído'): ?>
                                        <a href="view_results.php?email=<?php echo urlencode($candidato['email']); ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> Ver Resultados
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal de Gerenciamento de Senhas -->
    <div class="modal fade" id="selecionadoresModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-key"></i> Gerenciar Senhas dos Selecionadores
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>E-mail</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($selecionadores as $selecionador): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($selecionador['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($selecionador['email']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-change-password"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#senhaModal<?php echo md5($selecionador['email']); ?>">
                                                <i class="bi bi-key"></i> Alterar Senha
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modais de Alteração de Senha para cada Selecionador -->
    <?php foreach ($selecionadores as $selecionador): ?>
        <div class="modal fade" id="senhaModal<?php echo md5($selecionador['email']); ?>" 
             tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-key"></i>
                            Alterar Senha - <?php echo htmlspecialchars($selecionador['nome']); ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="" method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="change_password">
                            <input type="hidden" name="email" 
                                   value="<?php echo htmlspecialchars($selecionador['email']); ?>">
                            
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
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-change-password">
                                <i class="bi bi-check-lg"></i> Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#candidatosTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json'
                }
            });
        });
    </script>
</body>
</html>

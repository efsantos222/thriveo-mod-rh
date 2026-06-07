<?php
session_start();
require_once 'includes/menu.php';

// Verificar se o usuário está autenticado como admin ou superadmin
if ((!isset($_SESSION['admin_authenticated']) || !$_SESSION['admin_authenticated']) && 
    (!isset($_SESSION['superadmin_authenticated']) || !$_SESSION['superadmin_authenticated'])) {
    header('Location: login.php');
    exit;
}

$candidatos = [];
$candidatos_file = 'resultados/candidatos_jss.csv';

if (file_exists($candidatos_file)) {
    $fp = fopen($candidatos_file, 'r');
    
    // Pular a linha de cabeçalho
    fgetcsv($fp);
    
    // Ler os candidatos
    while (($data = fgetcsv($fp)) !== FALSE) {
        // Se for superadmin, mostra todos os candidatos
        // Se for admin normal, mostra apenas seus candidatos
        if (isset($_SESSION['superadmin_authenticated']) || $data[2] === $_SESSION['admin_email']) {
            $avaliacao_file = 'resultados/' . str_replace(['@', '.'], '_', $data[4]) . '_avaliacao_jss.csv';
            $grafico_file = 'resultados/' . str_replace(['@', '.'], '_', $data[4]) . '_grafico_jss.png';
            
            $candidatos[] = [
                'data' => $data[0],
                'solicitante' => $data[1],
                'nome' => $data[3],
                'email' => $data[4],
                'cargo' => $data[6],
                'observacoes' => $data[7],
                'status' => isset($data[8]) ? $data[8] : 'Pendente',
                'avaliacao_file' => $avaliacao_file,
                'grafico' => file_exists($grafico_file) ? $grafico_file : false
            ];
        }
    }
    
    fclose($fp);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Candidatos - JSS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .card {
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #f8f9fa;
        }
        .candidate-info {
            margin-bottom: 15px;
        }
        .candidate-info p {
            margin-bottom: 8px;
        }
        .candidate-info i {
            width: 20px;
            margin-right: 8px;
            color: #6c757d;
        }
        .status-badge {
            font-size: 0.9em;
            padding: 5px 10px;
        }
    </style>
</head>
<body>
    <?php renderMenu(); ?>
    
    <div class="container mt-4">
        <?php include 'header.php'; ?>

        <div class="content-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Candidatos JSS</h2>
                <a href="register_candidate_jss.php" class="btn btn-primary">
                    <i class="bi bi-person-plus-fill"></i> Novo Candidato JSS
                </a>
            </div>

            <?php if (isset($_GET['message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_GET['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (empty($candidatos)): ?>
                <div class="alert alert-info">
                    Nenhum candidato JSS registrado ainda.
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($candidatos as $candidato): ?>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-person-circle"></i>
                                        <?php echo htmlspecialchars($candidato['nome']); ?>
                                        <span class="float-end badge bg-<?php echo $candidato['status'] === 'Concluído' ? 'success' : 'warning'; ?> status-badge">
                                            <?php echo $candidato['status']; ?>
                                        </span>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="candidate-info">
                                        <p>
                                            <i class="bi bi-envelope"></i>
                                            <?php echo htmlspecialchars($candidato['email']); ?>
                                        </p>
                                        <p>
                                            <i class="bi bi-briefcase"></i>
                                            <?php echo htmlspecialchars($candidato['cargo']); ?>
                                        </p>
                                        <p>
                                            <i class="bi bi-calendar"></i>
                                            <?php echo date('d/m/Y', strtotime($candidato['data'])); ?>
                                        </p>
                                        <?php if (!empty($candidato['observacoes'])): ?>
                                            <p>
                                                <i class="bi bi-chat-left-text"></i>
                                                <?php echo htmlspecialchars($candidato['observacoes']); ?>
                                            </p>
                                        <?php endif; ?>
                                        <p>
                                            <i class="bi bi-person"></i>
                                            Solicitante: <?php echo htmlspecialchars($candidato['solicitante']); ?>
                                        </p>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="btn-group">
                                            <?php if ($candidato['status'] === 'Concluído'): ?>
                                                <a href="download_profile_jss.php?email=<?php echo urlencode($candidato['email']); ?>" 
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-file-pdf"></i> Ver Resultado
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-outline-secondary btn-sm" disabled>
                                                    <i class="bi bi-hourglass-split"></i> Aguardando Resposta
                                                </button>
                                            <?php endif; ?>
                                            
                                            <button type="button" 
                                                    class="btn btn-outline-secondary btn-sm"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#senhaModal<?php echo md5($candidato['email']); ?>">
                                                <i class="bi bi-key"></i> Alterar Senha
                                            </button>
                                        </div>
                                        
                                        <form method="POST" action="process_delete.php" style="display: inline;" 
                                              onsubmit="return confirm('Tem certeza que deseja excluir este candidato?')">
                                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($candidato['email']); ?>">
                                            <input type="hidden" name="type" value="jss">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="bi bi-trash"></i> Excluir
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal de Alteração de Senha -->
                        <div class="modal fade" id="senhaModal<?php echo md5($candidato['email']); ?>" 
                             tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Alterar Senha - <?php echo htmlspecialchars($candidato['nome']); ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="change_password.php" method="POST" class="change-password-form">
                                        <div class="modal-body">
                                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($candidato['email']); ?>">
                                            <input type="hidden" name="type" value="jss">
                                            <div class="mb-3">
                                                <label for="nova_senha_<?php echo md5($candidato['email']); ?>" class="form-label">Nova Senha</label>
                                                <input type="password" class="form-control" 
                                                       id="nova_senha_<?php echo md5($candidato['email']); ?>"
                                                       name="nova_senha" required minlength="6">
                                                <div class="form-text">Mínimo de 6 caracteres</div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary">Salvar</button>
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

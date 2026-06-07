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
$candidatos_file = 'resultados/candidatos_disc.csv';

if (file_exists($candidatos_file)) {
    $fp = fopen($candidatos_file, 'r');
    
    // Pular a linha de cabeçalho
    fgetcsv($fp);
    
    // Ler apenas os candidatos do selecionador logado
    while (($data = fgetcsv($fp)) !== FALSE) {
        if ($data[2] === $_SESSION['admin_email']) {
            $avaliacao_file = 'resultados/' . str_replace(['@', '.'], '_', $data[4]) . '_avaliacao.csv';
            $grafico_file = 'resultados/' . str_replace(['@', '.'], '_', $data[4]) . '_grafico.png';
            
            $candidatos[] = [
                'data' => $data[0],
                'solicitante' => $data[1],
                'nome' => $data[3],
                'email' => $data[4],
                'cargo' => $data[6],
                'observacoes' => $data[7],
                'status' => $data[8],
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
    <title>Visualizar Candidatos - DISC</title>
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
        }
        .card {
            transition: transform 0.2s;
            margin-bottom: 20px;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            background-color: #f8f9fa;
        }
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .candidate-info {
            margin-bottom: 15px;
        }
        .candidate-info i {
            width: 20px;
            color: #6c757d;
        }
        .download-links {
            border-top: 1px solid #dee2e6;
            padding-top: 15px;
            margin-top: 15px;
        }
        .btn-group {
            width: 100%;
        }
        .btn-group .btn {
            flex: 1;
        }
    </style>
</head>
<body>
    <?php renderMenu(); ?>
    
    <div class="container mt-4">
        <?php include 'header.php'; ?>

        <div class="content-container">
            <div class="mb-4">
                <a href="register_candidate.php" class="btn btn-primary">
                    <i class="bi bi-person-plus-fill"></i> Novo Candidato DISC
                </a>
            </div>

            <?php if (isset($_GET['password_changed'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle-fill"></i>
                    Senha do candidato alterada com sucesso!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (empty($candidatos)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle-fill"></i>
                    Você ainda não cadastrou nenhum candidato.
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
                                    </h5>
                                    <?php if (file_exists($candidato['avaliacao_file'])): ?>
                                        <span class="badge bg-success status-badge">Concluído</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark status-badge">Pendente</span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <div class="candidate-info">
                                        <p class="mb-2">
                                            <i class="bi bi-envelope"></i>
                                            <?php echo htmlspecialchars($candidato['email']); ?>
                                        </p>
                                        <p class="mb-2">
                                            <i class="bi bi-briefcase"></i>
                                            <?php echo htmlspecialchars($candidato['cargo']); ?>
                                        </p>
                                        <p class="mb-2">
                                            <i class="bi bi-person"></i>
                                            Solicitante: <?php echo htmlspecialchars($candidato['solicitante']); ?>
                                        </p>
                                        <p class="mb-2">
                                            <i class="bi bi-calendar"></i>
                                            <?php echo date('d/m/Y H:i', strtotime($candidato['data'])); ?>
                                        </p>
                                        <?php if (!empty($candidato['observacoes'])): ?>
                                            <p class="mb-2">
                                                <i class="bi bi-chat-left-text"></i>
                                                <?php echo htmlspecialchars($candidato['observacoes']); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="download-links">
                                        <div class="btn-group w-100">
                                            <?php if (file_exists($candidato['avaliacao_file'])): ?>
                                                <a href="view_csv.php?file=<?php echo urlencode(basename($candidato['avaliacao_file'])); ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-file-earmark-text"></i> Ver Avaliação
                                                </a>
                                                <?php if ($candidato['grafico']): ?>
                                                    <a href="<?php echo htmlspecialchars($candidato['grafico']); ?>" 
                                                       class="btn btn-sm btn-outline-success" 
                                                       download>
                                                        <i class="bi bi-graph-up"></i> Gráfico
                                                    </a>
                                                <?php endif; ?>
                                                <a href="download_profile.php?email=<?php echo urlencode($candidato['email']); ?>" 
                                                   class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-file-pdf"></i> Perfil
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-outline-secondary" disabled>
                                                    <i class="bi bi-hourglass-split"></i> Aguardando Avaliação
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-outline-warning w-100"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#senhaModal<?php echo md5($candidato['email']); ?>">
                                                <i class="bi bi-key"></i> Alterar Senha
                                            </button>
                                        </div>
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
                                        <h5 class="modal-title">
                                            <i class="bi bi-key"></i>
                                            Alterar Senha - <?php echo htmlspecialchars($candidato['nome']); ?>
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="reset_candidate_password.php" method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="email" 
                                                   value="<?php echo htmlspecialchars($candidato['email']); ?>">
                                            
                                            <div class="mb-3">
                                                <label for="nova_senha" class="form-label">Nova Senha</label>
                                                <input type="text" class="form-control" name="nova_senha" 
                                                       required minlength="6">
                                                <div class="form-text">
                                                    <i class="bi bi-info-circle"></i>
                                                    A senha deve ter pelo menos 6 caracteres.
                                                    O candidato receberá um e-mail com a nova senha.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                Cancelar
                                            </button>
                                            <button type="submit" class="btn btn-warning">
                                                <i class="bi bi-check-lg"></i> Alterar Senha
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
        <div class="mt-4 text-center">
            <a href="superadmin_panel.php#candidatos-disc" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/form_utils.js"></script>
</body>
</html>

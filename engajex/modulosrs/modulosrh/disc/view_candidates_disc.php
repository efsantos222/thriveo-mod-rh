<?php
session_start();
require_once 'includes/menu.php';

// Verificar se está logado como admin ou superadmin
if ((!isset($_SESSION['admin_authenticated']) || !$_SESSION['admin_authenticated']) && 
    (!isset($_SESSION['superadmin_authenticated']) || !$_SESSION['superadmin_authenticated'])) {
    header('Location: admin_login.php');
    exit;
}

$candidatos = [];
$candidatos_file = 'resultados/candidatos_disc.csv';

error_log("Tentando ler arquivo: " . $candidatos_file);

if (file_exists($candidatos_file)) {
    error_log("Arquivo existe: " . $candidatos_file);
    $fp = fopen($candidatos_file, 'r');
    
    if ($fp !== false) {
        error_log("Arquivo aberto com sucesso");
        // Pular a linha de cabeçalho
        fgetcsv($fp);
        
        // Se for superadmin, mostrar todos os candidatos
        // Se for admin normal, mostrar apenas os seus candidatos
        while (($data = fgetcsv($fp)) !== FALSE) {
            if (isset($_SESSION['superadmin_authenticated']) && $_SESSION['superadmin_authenticated']) {
                $incluir = true;
            } else {
                $incluir = ($data[2] === $_SESSION['admin_email']);
            }
            
            if ($incluir) {
                $avaliacao_file = 'resultados/' . str_replace(['@', '.'], '_', $data[4]) . '_avaliacao.csv';
                $grafico_file = 'resultados/' . str_replace(['@', '.'], '_', $data[4]) . '_grafico.png';
                
                error_log("Adicionando candidato: " . $data[3] . " - " . $data[4]);
                
                $candidatos[] = [
                    'data' => $data[0],
                    'solicitante' => $data[1],
                    'selecionador_email' => $data[2],
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
        error_log("Total de candidatos encontrados: " . count($candidatos));
    } else {
        error_log("Erro ao abrir arquivo");
    }
} else {
    error_log("Arquivo não existe: " . $candidatos_file);
    // Criar arquivo com cabeçalho se não existir
    $fp = fopen($candidatos_file, 'w');
    if ($fp !== false) {
        fputcsv($fp, ['data_cadastro', 'solicitante', 'selecionador_email', 'nome', 'email', 'senha', 'cargo', 'observacoes', 'status']);
        fclose($fp);
        error_log("Arquivo criado com cabeçalho");
    } else {
        error_log("Erro ao criar arquivo");
    }
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
        <div class="content-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Candidatos DISC</h2>
                <a href="register_candidate_disc.php" class="btn btn-primary">
                    <i class="bi bi-person-plus"></i> Novo Candidato
                </a>
            </div>

            <?php if (empty($candidatos)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 
                    Nenhum candidato encontrado.
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
                                    <?php
                                    $status_class = strtolower($candidato['status']) === 'completed' ? 'success' : 'warning';
                                    $status_text = strtolower($candidato['status']) === 'completed' ? 'Concluído' : 'Pendente';
                                    ?>
                                    <span class="badge bg-<?php echo $status_class; ?> status-badge">
                                        <?php echo $status_text; ?>
                                    </span>
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
                                        <?php if (!empty($candidato['observacoes'])): ?>
                                            <p class="mb-2">
                                                <i class="bi bi-chat-left-text"></i>
                                                <?php echo htmlspecialchars($candidato['observacoes']); ?>
                                            </p>
                                        <?php endif; ?>
                                        <p class="mb-2">
                                            <i class="bi bi-calendar"></i>
                                            <?php echo date('d/m/Y H:i', strtotime($candidato['data'])); ?>
                                        </p>
                                    </div>

                                    <?php if (strtolower($candidato['status']) === 'completed'): ?>
                                        <div class="download-links">
                                            <?php if ($candidato['grafico']): ?>
                                                <a href="<?php echo $candidato['grafico']; ?>" class="btn btn-outline-primary btn-sm mb-2" target="_blank">
                                                    <i class="bi bi-bar-chart"></i> Ver Gráfico
                                                </a>
                                            <?php endif; ?>
                                            <?php if (file_exists($candidato['avaliacao_file'])): ?>
                                                <a href="download_profile_disc.php?email=<?php echo urlencode($candidato['email']); ?>" class="btn btn-outline-success btn-sm mb-2">
                                                    <i class="bi bi-file-earmark-text"></i> Baixar Relatório
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="btn-group mt-3">
                                            <button type="button" class="btn btn-outline-primary btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#senhaModal<?php echo md5($candidato['email']); ?>">
                                                <i class="bi bi-key"></i> Alterar Senha
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                                    onclick="if(confirm('Tem certeza que deseja excluir este candidato?')) 
                                                            window.location.href='process_delete_disc.php?email=<?php echo urlencode($candidato['email']); ?>'">
                                                <i class="bi bi-trash"></i> Excluir
                                            </button>
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
                                                    <form action="process_password_disc.php" method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($candidato['email']); ?>">
                                                            <div class="mb-3">
                                                                <label for="nova_senha" class="form-label">Nova Senha</label>
                                                                <input type="text" class="form-control" id="nova_senha" name="nova_senha" required>
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
                                    <?php endif; ?>
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
</body>
</html>

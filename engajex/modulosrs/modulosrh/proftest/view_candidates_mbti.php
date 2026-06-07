<?php
session_start();

// Verificar se está logado como admin ou superadmin
if ((!isset($_SESSION['admin_authenticated']) || !$_SESSION['admin_authenticated']) && 
    (!isset($_SESSION['superadmin_authenticated']) || !$_SESSION['superadmin_authenticated'])) {
    header('Location: admin_login.php');
    exit;
}

$candidatos = [];
$candidatos_file = 'resultados/candidatos_mbti.csv';

if (file_exists($candidatos_file)) {
    $fp = fopen($candidatos_file, 'r');
    fgetcsv($fp); // Pular cabeçalho
    
    while (($data = fgetcsv($fp)) !== FALSE) {
        // Se for superadmin, mostrar todos os candidatos
        // Se for admin, mostrar apenas os seus candidatos
        if (isset($_SESSION['superadmin_authenticated']) || $data[2] === $_SESSION['admin_email']) {
            $avaliacao_file = 'resultados/' . str_replace(['@', '.'], '_', $data[4]) . '_avaliacao_mbti.csv';
            $status = file_exists($avaliacao_file) ? 'Concluído' : 'Pendente';
            
            $candidatos[] = [
                'data' => $data[0],
                'solicitante' => $data[1],
                'nome' => $data[3],
                'email' => $data[4],
                'cargo' => $data[6],
                'observacoes' => $data[7],
                'status' => $status
            ];
        }
    }
    fclose($fp);
}

// Ordenar por data (mais recentes primeiro)
usort($candidatos, function($a, $b) {
    return strtotime($b['data']) - strtotime($a['data']);
});
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidatos MBTI - Sistema DISC/MBTI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .content-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .badge {
            font-size: 0.9em;
        }
        .btn-group-sm > .btn, .btn-sm {
            padding: 0.25rem 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'header.php'; ?>
        
        <div class="content-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Candidatos MBTI</h2>
                <a href="register_candidate_mbti.php" class="btn btn-success">
                    <i class="bi bi-person-plus"></i> Novo Candidato MBTI
                </a>
            </div>

            <?php if (empty($candidatos)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Nenhum candidato MBTI registrado.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="candidatosTable">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Cargo</th>
                                <th>Solicitante</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($candidatos as $candidato): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($candidato['data'])); ?></td>
                                    <td><?php echo htmlspecialchars($candidato['nome']); ?></td>
                                    <td><?php echo htmlspecialchars($candidato['email']); ?></td>
                                    <td><?php echo htmlspecialchars($candidato['cargo']); ?></td>
                                    <td><?php echo htmlspecialchars($candidato['solicitante']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $candidato['status'] === 'Concluído' ? 'success' : 'warning'; ?>">
                                            <?php echo $candidato['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($candidato['status'] === 'Concluído'): ?>
                                            <a href="mbti/view_results.php?email=<?php echo urlencode($candidato['email']); ?>" 
                                               class="btn btn-sm btn-outline-primary" title="Ver Resultado">
                                                <i class="bi bi-graph-up"></i>
                                            </a>
                                            <a href="download_profile_mbti.php?email=<?php echo urlencode($candidato['email']); ?>" 
                                               class="btn btn-sm btn-outline-info" title="Baixar PDF">
                                                <i class="bi bi-file-pdf"></i>
                                            </a>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                data-bs-toggle="modal" data-bs-target="#observacoesModal<?php echo md5($candidato['email']); ?>"
                                                title="Ver Observações">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <a href="reset_candidate_password.php?email=<?php echo urlencode($candidato['email']); ?>&type=mbti" 
                                           class="btn btn-sm btn-outline-warning" title="Redefinir Senha">
                                            <i class="bi bi-key"></i>
                                        </a>
                                    </td>
                                </tr>
                                
                                <!-- Modal de Observações -->
                                <div class="modal fade" id="observacoesModal<?php echo md5($candidato['email']); ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Observações</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <?php if (!empty($candidato['observacoes'])): ?>
                                                    <p><?php echo nl2br(htmlspecialchars($candidato['observacoes'])); ?></p>
                                                <?php else: ?>
                                                    <p class="text-muted">Nenhuma observação registrada.</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#candidatosTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/pt_br.json'
                }
            });
        });
    </script>
</body>
</html>

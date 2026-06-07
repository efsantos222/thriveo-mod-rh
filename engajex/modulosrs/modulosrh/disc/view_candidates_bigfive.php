<?php
session_start();

// Verificar se está logado como admin ou superadmin
if (!isset($_SESSION['admin_authenticated']) && !isset($_SESSION['superadmin_authenticated'])) {
    header('Location: index.php');
    exit;
}

// Configuração da paginação
$items_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start_index = ($current_page - 1) * $items_per_page;

// Carregar candidatos
$candidatos = [];
$total_items = 0;
$file = 'resultados/candidatos_bigfive.csv';

if (file_exists($file)) {
    $fp = fopen($file, 'r');
    if ($fp !== false) {
        // Pular cabeçalho
        fgetcsv($fp);
        
        // Filtrar por selecionador se for admin normal
        $admin_email = isset($_SESSION['superadmin_email']) ? $_SESSION['superadmin_email'] : $_SESSION['admin_email'];
        
        // Primeira passagem para contar total de itens
        while (($data = fgetcsv($fp)) !== FALSE) {
            if (!isset($_SESSION['superadmin_authenticated']) && $data[2] !== $admin_email) {
                continue;
            }
            $total_items++;
        }
        
        // Voltar ao início do arquivo
        rewind($fp);
        fgetcsv($fp); // Pular cabeçalho novamente
        
        // Segunda passagem para pegar os itens da página atual
        $count = 0;
        $added = 0;
        while (($data = fgetcsv($fp)) !== FALSE) {
            if (!isset($_SESSION['superadmin_authenticated']) && $data[2] !== $admin_email) {
                continue;
            }
            
            if ($count >= $start_index && $added < $items_per_page) {
                $avaliacao_file = 'resultados/' . str_replace(['@', '.'], '_', $data[4]) . '_avaliacao_bigfive.csv';
                $status = file_exists($avaliacao_file) ? 'Concluído' : 'Pendente';
                
                $candidatos[] = [
                    'data' => $data[0],
                    'selecionador' => $data[1],
                    'nome' => $data[3],
                    'email' => $data[4],
                    'empresa' => $data[5],
                    'cargo' => $data[6],
                    'status' => $status
                ];
                $added++;
            }
            $count++;
        }
        fclose($fp);
    }
}

$total_pages = ceil($total_items / $items_per_page);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidatos Big Five</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .status-badge {
            font-size: 0.875rem;
        }
        .action-buttons {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <?php include 'header.php'; ?>

        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="bi bi-people"></i> Candidatos Big Five</h4>
                <a href="register_candidate_bigfive.php" class="btn btn-light">
                    <i class="bi bi-person-plus"></i> Novo Candidato
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="candidatosTable">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Empresa</th>
                                <th>Cargo</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($candidatos as $candidato): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i', strtotime($candidato['data'])); ?></td>
                                <td><?php echo htmlspecialchars($candidato['nome']); ?></td>
                                <td><?php echo htmlspecialchars($candidato['email']); ?></td>
                                <td><?php echo htmlspecialchars($candidato['empresa']); ?></td>
                                <td><?php echo htmlspecialchars($candidato['cargo']); ?></td>
                                <td>
                                    <?php if ($candidato['status'] === 'Concluído'): ?>
                                        <span class="badge bg-success status-badge">Concluído</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning status-badge">Pendente</span>
                                    <?php endif; ?>
                                </td>
                                <td class="action-buttons">
                                    <?php if ($candidato['status'] === 'Concluído'): ?>
                                    <a href="download_profile_bigfive.php?email=<?php echo urlencode($candidato['email']); ?>" 
                                       class="btn btn-sm btn-success" title="Baixar Perfil">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="confirmarExclusao('<?php echo $candidato['email']; ?>', 'bigfive')" 
                                            title="Excluir">
                                        <i class="bi bi-trash"></i>
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

    <!-- Modal de Confirmação de Exclusão -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Tem certeza que deseja excluir este candidato?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" onclick="excluirCandidato()">Excluir</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#candidatosTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json'
                },
                order: [[0, 'desc']]
            });
        });

        let emailToDelete = '';
        let testType = '';

        function confirmarExclusao(email, tipo) {
            emailToDelete = email;
            testType = tipo;
            var modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
            modal.show();
        }

        function excluirCandidato() {
            window.location.href = `process_delete.php?email=${emailToDelete}&type=${testType}`;
        }
    </script>
</body>
</html>

<?php
session_start();
require_once 'includes/menu.php';

// Verificar se está logado como superadmin
if (!isset($_SESSION['superadmin_authenticated']) || !$_SESSION['superadmin_authenticated']) {
    header('Location: superadmin_login.php');
    exit;
}

// Configuração da paginação
$items_per_page = 10;
$current_page_disc = isset($_GET['page_disc']) ? (int)$_GET['page_disc'] : 1;
$current_page_mbti = isset($_GET['page_mbti']) ? (int)$_GET['page_mbti'] : 1;

// Carregar lista de selecionadores
$selecionadores = [];
$admins_file = 'resultados/admins.csv';
if (file_exists($admins_file)) {
    $fp = fopen($admins_file, 'r');
    if ($fp !== false) {
        fgetcsv($fp); // Pular cabeçalho
        while (($data = fgetcsv($fp)) !== FALSE) {
            $selecionadores[] = [
                'nome' => $data[0],
                'email' => $data[1],
                'data_cadastro' => isset($data[3]) ? $data[3] : date('Y-m-d H:i:s')
            ];
        }
        fclose($fp);
    }
}

// Função para carregar candidatos com paginação
function carregarCandidatos($arquivo, $page, $items_per_page, $tipo = 'disc') {
    $candidatos = [];
    $total_items = 0;
    
    if (file_exists($arquivo)) {
        $linhas = file($arquivo);
        $total_items = count($linhas) - 1; // -1 para excluir o cabeçalho
        
        $start = ($page - 1) * $items_per_page + 1; // +1 para pular o cabeçalho
        $end = min($start + $items_per_page, count($linhas));
        
        $fp = fopen($arquivo, 'r');
        fgetcsv($fp); // Pular cabeçalho
        
        $count = 1;
        while (($data = fgetcsv($fp)) !== FALSE) {
            if ($count >= $start && $count < $end) {
                $avaliacao_file = 'resultados/' . str_replace(['@', '.'], '_', $data[4]) . 
                                 ($tipo === 'disc' ? '_avaliacao.csv' : '_avaliacao_mbti.csv');
                $status = file_exists($avaliacao_file) ? 'Concluído' : 'Pendente';
                
                $candidatos[] = [
                    'data' => $data[0],
                    'selecionador' => $data[1],
                    'nome' => $data[3],
                    'email' => $data[4],
                    'cargo' => $data[6],
                    'status' => $status
                ];
            }
            $count++;
        }
        fclose($fp);
    }
    
    return [
        'candidatos' => $candidatos,
        'total_items' => $total_items
    ];
}

// Carregar candidatos DISC
$disc_result = carregarCandidatos('resultados/candidatos.csv', $current_page_disc, $items_per_page, 'disc');
$candidatos_disc = $disc_result['candidatos'];
$total_disc = $disc_result['total_items'];
$total_pages_disc = ceil($total_disc / $items_per_page);

// Carregar candidatos MBTI
$mbti_result = carregarCandidatos('resultados/candidatos_mbti.csv', $current_page_mbti, $items_per_page, 'mbti');
$candidatos_mbti = $mbti_result['candidatos'];
$total_mbti = $mbti_result['total_items'];
$total_pages_mbti = ceil($total_mbti / $items_per_page);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Superadmin - Sistema DISC/MBTI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .pagination {
            margin-bottom: 0;
        }
        .pagination .page-link {
            padding: 0.375rem 0.75rem;
        }
        .pagination .active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
    </style>
</head>
<body>
    <?php renderMenu(); ?>
    
    <div class="container mt-4">
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php
                switch ($_GET['error']) {
                    case 'missing_fields':
                        echo 'Por favor, preencha todos os campos obrigatórios.';
                        break;
                    case 'invalid_email':
                        echo 'Por favor, informe um e-mail válido.';
                        break;
                    case 'invalid_password':
                        echo 'A senha deve ter pelo menos 6 caracteres.';
                        break;
                    case 'email_exists':
                        echo 'Este e-mail já está cadastrado.';
                        break;
                    default:
                        echo 'Ocorreu um erro. Por favor, tente novamente.';
                }
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success']) && $_GET['success'] === 'add_selecionador'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Selecionador adicionado com sucesso! Um e-mail foi enviado com as credenciais.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Painel Administrativo</h2>
                <div>
                    <span class="me-3">Olá, <?php echo htmlspecialchars($_SESSION['superadmin_nome']); ?></span>
                    <a href="logout.php" class="btn btn-secondary">Sair</a>
                </div>
            </div>
            <div class="card-body">
                <!-- Abas -->
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#selecionadores">Selecionadores</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#questoes">Questões</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#candidatos-disc">Candidatos DISC</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#candidatos-mbti">Candidatos MBTI</a>
                    </li>
                </ul>

                <!-- Conteúdo das Abas -->
                <div class="tab-content mt-3">
                    <!-- Tab Selecionadores -->
                    <div class="tab-pane fade show active" id="selecionadores">
                        <button type="button" class="btn btn-primary mb-3" onclick="openAddSelecionadorModal()">
                            <i class="bi bi-plus-circle"></i> Novo Selecionador
                        </button>

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>E-mail</th>
                                        <th>Data Cadastro</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($selecionadores as $selecionador): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($selecionador['nome']); ?></td>
                                            <td><?php echo htmlspecialchars($selecionador['email']); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($selecionador['data_cadastro'])); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-danger btn-sm" onclick="excluirSelecionador('<?php echo htmlspecialchars($selecionador['email']); ?>')">
                                                    <i class="bi bi-trash"></i> Excluir
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab Questões -->
                    <div class="tab-pane fade" id="questoes">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3>Gerenciamento de Questões</h3>
                            <div>
                                <a href="manage_questions.php" class="btn btn-primary me-2">
                                    <i class="bi bi-list-check"></i> Questões DISC
                                </a>
                                <a href="manage_questions_mbti.php" class="btn btn-primary">
                                    <i class="bi bi-list-check"></i> Questões MBTI
                                </a>
                            </div>
                        </div>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Clique nos botões acima para gerenciar as questões dos testes DISC e MBTI.
                        </div>
                    </div>

                    <!-- Tab Candidatos DISC -->
                    <div class="tab-pane fade" id="candidatos-disc">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3>Candidatos DISC</h3>
                            <a href="view_candidates.php" class="btn btn-primary">
                                <i class="bi bi-search"></i> Ver Todos os Candidatos DISC
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Nome</th>
                                        <th>Cargo</th>
                                        <th>Selecionador</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($candidatos_disc as $candidato): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($candidato['data'])); ?></td>
                                            <td><?php echo htmlspecialchars($candidato['nome']); ?></td>
                                            <td><?php echo htmlspecialchars($candidato['cargo']); ?></td>
                                            <td><?php echo htmlspecialchars($candidato['selecionador']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $candidato['status'] === 'Concluído' ? 'success' : 'warning'; ?>">
                                                    <?php echo $candidato['status']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($candidato['status'] === 'Concluído'): ?>
                                                    <a href="download_profile.php?email=<?php echo urlencode($candidato['email']); ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-file-pdf"></i> Ver Resultado
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Paginação DISC -->
                        <?php if ($total_pages_disc > 1): ?>
                            <div class="d-flex justify-content-center mt-3">
                                <nav aria-label="Navegação DISC">
                                    <ul class="pagination">
                                        <?php if ($current_page_disc > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page_disc=<?php echo $current_page_disc - 1; ?>#candidatos-disc">Anterior</a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 1; $i <= $total_pages_disc; $i++): ?>
                                            <li class="page-item <?php echo $i === $current_page_disc ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page_disc=<?php echo $i; ?>#candidatos-disc"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($current_page_disc < $total_pages_disc): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page_disc=<?php echo $current_page_disc + 1; ?>#candidatos-disc">Próximo</a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tab Candidatos MBTI -->
                    <div class="tab-pane fade" id="candidatos-mbti">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3>Candidatos MBTI</h3>
                            <a href="view_candidates_mbti.php" class="btn btn-primary">
                                <i class="bi bi-search"></i> Ver Todos os Candidatos MBTI
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Nome</th>
                                        <th>Cargo</th>
                                        <th>Selecionador</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($candidatos_mbti as $candidato): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($candidato['data'])); ?></td>
                                            <td><?php echo htmlspecialchars($candidato['nome']); ?></td>
                                            <td><?php echo htmlspecialchars($candidato['cargo']); ?></td>
                                            <td><?php echo htmlspecialchars($candidato['selecionador']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $candidato['status'] === 'Concluído' ? 'success' : 'warning'; ?>">
                                                    <?php echo $candidato['status']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($candidato['status'] === 'Concluído'): ?>
                                                    <a href="download_profile_mbti.php?email=<?php echo urlencode($candidato['email']); ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-file-pdf"></i> Ver Resultado
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Paginação MBTI -->
                        <?php if ($total_pages_mbti > 1): ?>
                            <div class="d-flex justify-content-center mt-3">
                                <nav aria-label="Navegação MBTI">
                                    <ul class="pagination">
                                        <?php if ($current_page_mbti > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page_mbti=<?php echo $current_page_mbti - 1; ?>#candidatos-mbti">Anterior</a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 1; $i <= $total_pages_mbti; $i++): ?>
                                            <li class="page-item <?php echo $i === $current_page_mbti ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page_mbti=<?php echo $i; ?>#candidatos-mbti"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($current_page_mbti < $total_pages_mbti): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page_mbti=<?php echo $current_page_mbti + 1; ?>#candidatos-mbti">Próximo</a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Adicionar Selecionador -->
    <div class="modal" id="modalAddSelecionador" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Novo Selecionador</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="process_superadmin.php" method="POST">
                    <input type="hidden" name="action" value="add_selecionador">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="senha" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="senha" name="senha" required>
                            <div class="form-text">A senha deve ter no mínimo 6 caracteres.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Adicionar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let modalAddSelecionador;
        
        document.addEventListener('DOMContentLoaded', function() {
            modalAddSelecionador = new bootstrap.Modal(document.getElementById('modalAddSelecionador'));
            
            // Adicionar evento para limpar o formulário após o envio
            document.querySelector('#modalAddSelecionador form').addEventListener('submit', function(e) {
                // Aguarda um pequeno delay para garantir que o formulário foi enviado
                setTimeout(() => {
                    this.reset();
                }, 100);
            });

            // Ativar a aba correta baseado no hash da URL
            let hash = window.location.hash;
            if (hash) {
                const tab = new bootstrap.Tab(document.querySelector('a[href="' + hash + '"]'));
                tab.show();
            }

            // Atualizar hash quando mudar de aba
            document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(tab => {
                tab.addEventListener('shown.bs.tab', function(e) {
                    window.location.hash = e.target.getAttribute('href');
                });
            });
        });

        function openAddSelecionadorModal() {
            modalAddSelecionador.show();
        }

        function excluirSelecionador(email) {
            if (confirm('Tem certeza que deseja excluir este selecionador?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'process_superadmin.php';

                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete_selecionador';

                const emailInput = document.createElement('input');
                emailInput.type = 'hidden';
                emailInput.name = 'email';
                emailInput.value = email;

                form.appendChild(actionInput);
                form.appendChild(emailInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
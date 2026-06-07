<?php
require_once '../config/config.php';
require_once '../config/database.php';
checkAuth();

$db = Database::getInstance()->getConnection();

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        try {
            $db->beginTransaction();
            
            $texto = sanitize($_POST['texto_pergunta']);
            $tipo = sanitize($_POST['tipo_resposta']);
            $recorrente = isset($_POST['recorrente']) ? 1 : 0;
            $periodicidade = sanitize($_POST['periodicidade'] ?? 'semanal');
            
            $stmt = $db->prepare("INSERT INTO perguntas (texto_pergunta, tipo_resposta, recorrente, periodicidade) VALUES (?, ?, ?, ?)");
            $stmt->execute([$texto, $tipo, $recorrente, $periodicidade]);
            
            $perguntaId = $db->lastInsertId();
            
            // Se for múltipla escolha, salvar as opções
            if ($tipo === 'multipla_escolha' && !empty($_POST['opcoes'])) {
                $stmt = $db->prepare("INSERT INTO opcoes_resposta (id_pergunta, texto_opcao, ordem) VALUES (?, ?, ?)");
                $ordem = 1;
                $opcoes = array_filter(explode("\n", $_POST['opcoes']));
                foreach ($opcoes as $opcao) {
                    if (!empty(trim($opcao))) {
                        $stmt->execute([$perguntaId, sanitize(trim($opcao)), $ordem++]);
                    }
                }
            }
            
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
        
    } elseif ($action === 'update') {
        try {
            $db->beginTransaction();
            
            $id = (int)$_POST['id'];
            $texto = sanitize($_POST['texto_pergunta']);
            $tipo = sanitize($_POST['tipo_resposta']);
            $ativa = isset($_POST['ativa']) ? 1 : 0;
            $recorrente = isset($_POST['recorrente']) ? 1 : 0;
            $periodicidade = sanitize($_POST['periodicidade'] ?? 'semanal');
            
            $stmt = $db->prepare("UPDATE perguntas SET texto_pergunta = ?, tipo_resposta = ?, ativa = ?, recorrente = ?, periodicidade = ? WHERE id = ?");
            $stmt->execute([$texto, $tipo, $ativa, $recorrente, $periodicidade, $id]);
            
            // Atualizar opções se for múltipla escolha
            if ($tipo === 'multipla_escolha') {
                // Remover opções antigas
                $stmt = $db->prepare("DELETE FROM opcoes_resposta WHERE id_pergunta = ?");
                $stmt->execute([$id]);
                
                // Inserir novas opções
                if (!empty($_POST['opcoes'])) {
                    $stmt = $db->prepare("INSERT INTO opcoes_resposta (id_pergunta, texto_opcao, ordem) VALUES (?, ?, ?)");
                    $ordem = 1;
                    $opcoes = array_filter(explode("\n", $_POST['opcoes']));
                    foreach ($opcoes as $opcao) {
                        if (!empty(trim($opcao))) {
                            $stmt->execute([$id, sanitize(trim($opcao)), $ordem++]);
                        }
                    }
                }
            }
            
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
        
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        // A exclusão das opções é automática devido à constraint ON DELETE CASCADE
        $stmt = $db->prepare("DELETE FROM perguntas WHERE id = ?");
        $stmt->execute([$id]);
    }
    
    redirect('/admin/perguntas.php');
}

// Buscar perguntas com suas opções
$stmt = $db->query("
    SELECT p.*, 
           GROUP_CONCAT(o.texto_opcao ORDER BY o.ordem ASC SEPARATOR '||') as opcoes,
           GROUP_CONCAT(o.id ORDER BY o.ordem ASC SEPARATOR '||') as opcoes_ids
    FROM perguntas p 
    LEFT JOIN opcoes_resposta o ON p.id = o.id_pergunta 
    GROUP BY p.id 
    ORDER BY p.created_at DESC
");
$perguntas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Perguntas - Área Administrativa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Painel Administrativo</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="perguntas.php">Perguntas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="respondentes.php">Respondentes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="relatorios.php">Relatórios</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Sair</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestão de Perguntas</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#novaPerguntaModal">
                Nova Pergunta
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Pergunta</th>
                        <th>Tipo</th>
                        <th>Opções</th>
                        <th>Status</th>
                        <th>Recorrente</th>
                        <th>Periodicidade</th>
                        <th>Criada em</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($perguntas as $pergunta): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($pergunta['texto_pergunta']); ?></td>
                        <td>
                            <span class="badge bg-info">
                                <?php echo $pergunta['tipo_resposta'] === 'multipla_escolha' ? 'Múltipla Escolha' : 'Aberta'; ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                            if ($pergunta['tipo_resposta'] === 'multipla_escolha' && !empty($pergunta['opcoes'])) {
                                $opcoes = explode('||', $pergunta['opcoes']);
                                echo '<ul class="list-unstyled mb-0">';
                                foreach ($opcoes as $opcao) {
                                    echo '<li><small>' . htmlspecialchars($opcao) . '</small></li>';
                                }
                                echo '</ul>';
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo $pergunta['ativa'] ? 'success' : 'danger'; ?>">
                                <?php echo $pergunta['ativa'] ? 'Ativa' : 'Inativa'; ?>
                            </span>
                        </td>
                        <td><?php echo $pergunta['recorrente'] ? 'Sim' : 'Não'; ?></td>
                        <td><?php echo htmlspecialchars($pergunta['periodicidade']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($pergunta['created_at'])); ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary" 
                                    onclick="editarPergunta(<?php echo htmlspecialchars(json_encode($pergunta)); ?>)">
                                Editar
                            </button>
                            <button class="btn btn-sm btn-danger" 
                                    onclick="confirmarExclusao(<?php echo $pergunta['id']; ?>)">
                                Excluir
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Nova Pergunta -->
    <div class="modal fade" id="novaPerguntaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nova Pergunta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <div class="mb-3">
                            <label for="texto_pergunta" class="form-label">Texto da Pergunta</label>
                            <textarea class="form-control" id="texto_pergunta" name="texto_pergunta" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="tipo_resposta" class="form-label">Tipo de Resposta</label>
                            <select class="form-select" id="tipo_resposta" name="tipo_resposta">
                                <option value="aberta">Aberta</option>
                                <option value="multipla_escolha">Múltipla Escolha</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="recorrente" name="recorrente">
                                <label class="form-check-label" for="recorrente">
                                    Pergunta Recorrente
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="periodicidade" class="form-label">Periodicidade</label>
                            <select class="form-select" id="periodicidade" name="periodicidade">
                                <option value="semanal">Semanal</option>
                                <option value="quinzenal">Quinzenal</option>
                                <option value="mensal">Mensal</option>
                            </select>
                        </div>
                        <div id="opcoes_resposta" style="display: none;">
                            <div class="mb-3">
                                <label for="opcoes" class="form-label">Opções de Resposta</label>
                                <textarea class="form-control" id="opcoes" name="opcoes" placeholder="Insira as opções, uma por linha"></textarea>
                            </div>
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

    <!-- Modal Editar Pergunta -->
    <div class="modal fade" id="editarPerguntaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Pergunta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label for="edit_texto_pergunta" class="form-label">Texto da Pergunta</label>
                            <textarea class="form-control" id="edit_texto_pergunta" name="texto_pergunta" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_tipo_resposta" class="form-label">Tipo de Resposta</label>
                            <select class="form-select" id="edit_tipo_resposta" name="tipo_resposta">
                                <option value="aberta">Aberta</option>
                                <option value="multipla_escolha">Múltipla Escolha</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_ativa" name="ativa">
                                <label class="form-check-label" for="edit_ativa">
                                    Pergunta Ativa
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_recorrente" name="recorrente">
                                <label class="form-check-label" for="edit_recorrente">
                                    Pergunta Recorrente
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_periodicidade" class="form-label">Periodicidade</label>
                            <select class="form-select" id="edit_periodicidade" name="periodicidade">
                                <option value="semanal">Semanal</option>
                                <option value="quinzenal">Quinzenal</option>
                                <option value="mensal">Mensal</option>
                            </select>
                        </div>
                        <div id="edit_opcoes_resposta" style="display: none;">
                            <div class="mb-3">
                                <label for="edit_opcoes" class="form-label">Opções de Resposta</label>
                                <textarea class="form-control" id="edit_opcoes" name="opcoes" placeholder="Insira as opções, uma por linha"></textarea>
                            </div>
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

    <!-- Modal Confirmar Exclusão -->
    <div class="modal fade" id="confirmarExclusaoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Tem certeza que deseja excluir esta pergunta?
                </div>
                <div class="modal-footer">
                    <form method="POST">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editarPergunta(pergunta) {
            document.getElementById('edit_id').value = pergunta.id;
            document.getElementById('edit_texto_pergunta').value = pergunta.texto_pergunta;
            document.getElementById('edit_tipo_resposta').value = pergunta.tipo_resposta;
            document.getElementById('edit_ativa').checked = pergunta.ativa == 1;
            document.getElementById('edit_recorrente').checked = pergunta.recorrente == 1;
            document.getElementById('edit_periodicidade').value = pergunta.periodicidade;

            // Processar opções de resposta
            if (pergunta.tipo_resposta === 'multipla_escolha') {
                document.getElementById('edit_opcoes_resposta').style.display = 'block';
                if (pergunta.opcoes) {
                    const opcoes = pergunta.opcoes.split('||');
                    document.getElementById('edit_opcoes').value = opcoes.join('\n');
                }
            } else {
                document.getElementById('edit_opcoes_resposta').style.display = 'none';
                document.getElementById('edit_opcoes').value = '';
            }

            new bootstrap.Modal(document.getElementById('editarPerguntaModal')).show();
        }

        function confirmarExclusao(id) {
            document.getElementById('delete_id').value = id;
            new bootstrap.Modal(document.getElementById('confirmarExclusaoModal')).show();
        }

        // Mostrar/ocultar campos de opções baseado no tipo de resposta
        document.getElementById('tipo_resposta').addEventListener('change', function() {
            const opcoesDiv = document.getElementById('opcoes_resposta');
            opcoesDiv.style.display = this.value === 'multipla_escolha' ? 'block' : 'none';
        });

        document.getElementById('edit_tipo_resposta').addEventListener('change', function() {
            const opcoesDiv = document.getElementById('edit_opcoes_resposta');
            opcoesDiv.style.display = this.value === 'multipla_escolha' ? 'block' : 'none';
        });
    </script>
</body>
</html>

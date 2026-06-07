<?php
session_start();

// Verificar se está logado como superadmin
if (!isset($_SESSION['superadmin_authenticated']) || !$_SESSION['superadmin_authenticated']) {
    header('Location: superadmin_login.php');
    exit;
}

// Função para carregar questões MBTI
function carregarQuestoesMBTI() {
    $questoes = [];
    $arquivo = 'questoes/questoes_mbti.csv';
    
    if (file_exists($arquivo)) {
        $fp = fopen($arquivo, 'r');
        fgetcsv($fp); // Pular cabeçalho
        
        while (($data = fgetcsv($fp)) !== FALSE) {
            $questoes[] = [
                'id' => $data[0],
                'questao' => $data[1],
                'opcao_1' => $data[2],
                'tipo_1' => $data[3],
                'opcao_2' => $data[4],
                'tipo_2' => $data[5],
                'dimensao' => $data[6] // E/I, S/N, T/F, ou J/P
            ];
        }
        fclose($fp);
    }
    
    return $questoes;
}

$questoes = carregarQuestoesMBTI();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Questões MBTI - Sistema DISC/MBTI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .question-card {
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .option-label {
            font-weight: bold;
            color: #0d6efd;
        }
        .dimensao-badge {
            font-size: 0.9em;
            padding: 0.3em 0.6em;
            border-radius: 0.25rem;
            background-color: #e9ecef;
            color: #495057;
        }
    </style>
</head>
<body>
    <?php require_once 'includes/menu.php'; renderMenu(); ?>
    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gerenciar Questões MBTI</h2>
            <div>
                <a href="superadmin_panel.php#questoes" class="btn btn-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
                <a href="index.php" class="btn btn-secondary me-2">
                    <i class="bi bi-house-door"></i> Home
                </a>
                <button type="button" class="btn btn-primary" onclick="openAddQuestionModal()">
                    <i class="bi bi-plus-circle"></i> Nova Questão
                </button>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php
                switch ($_GET['success']) {
                    case 'add':
                        echo 'Questão adicionada com sucesso!';
                        break;
                    case 'edit':
                        echo 'Questão atualizada com sucesso!';
                        break;
                    case 'delete':
                        echo 'Questão excluída com sucesso!';
                        break;
                }
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php
                switch ($_GET['error']) {
                    case 'missing_fields':
                        echo 'Por favor, preencha todos os campos.';
                        break;
                    default:
                        echo 'Ocorreu um erro. Por favor, tente novamente.';
                }
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Lista de Questões -->
        <?php foreach ($questoes as $questao): ?>
            <div class="card question-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Questão <?php echo htmlspecialchars($questao['id']); ?></h5>
                        <span class="dimensao-badge">Dimensão: <?php echo htmlspecialchars($questao['dimensao']); ?></span>
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                onclick="editQuestion(<?php echo htmlspecialchars(json_encode($questao)); ?>)">
                            <i class="bi bi-pencil"></i> Editar
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" 
                                onclick="deleteQuestion(<?php echo $questao['id']; ?>)">
                            <i class="bi bi-trash"></i> Excluir
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <p class="card-text"><?php echo htmlspecialchars($questao['questao']); ?></p>
                    <div class="row">
                        <div class="col-md-6">
                            <p>
                                <span class="option-label"><?php echo htmlspecialchars($questao['tipo_1']); ?>:</span> 
                                <?php echo htmlspecialchars($questao['opcao_1']); ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p>
                                <span class="option-label"><?php echo htmlspecialchars($questao['tipo_2']); ?>:</span> 
                                <?php echo htmlspecialchars($questao['opcao_2']); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Modal Adicionar/Editar Questão -->
    <div class="modal fade" id="questionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Nova Questão MBTI</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="questionForm" action="process_questions_mbti.php" method="POST">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="id" id="questionId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="questao" class="form-label">Questão</label>
                            <textarea class="form-control" id="questao" name="questao" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="dimensao" class="form-label">Dimensão MBTI</label>
                            <select class="form-select" id="dimensao" name="dimensao" required>
                                <option value="E/I">Extroversão/Introversão (E/I)</option>
                                <option value="S/N">Sensação/Intuição (S/N)</option>
                                <option value="T/F">Pensamento/Sentimento (T/F)</option>
                                <option value="J/P">Julgamento/Percepção (J/P)</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="opcao_1" class="form-label">Primeira Opção</label>
                                <input type="text" class="form-control" id="opcao_1" name="opcao_1" required>
                                <input type="hidden" id="tipo_1" name="tipo_1" value="E">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="opcao_2" class="form-label">Segunda Opção</label>
                                <input type="text" class="form-control" id="opcao_2" name="opcao_2" required>
                                <input type="hidden" id="tipo_2" name="tipo_2" value="I">
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let questionModal;
        
        document.addEventListener('DOMContentLoaded', function() {
            questionModal = new bootstrap.Modal(document.getElementById('questionModal'));
            
            // Atualizar tipos baseado na dimensão selecionada
            document.getElementById('dimensao').addEventListener('change', function() {
                const tipos = {
                    'E/I': ['E', 'I'],
                    'S/N': ['S', 'N'],
                    'T/F': ['T', 'F'],
                    'J/P': ['J', 'P']
                };
                const [tipo1, tipo2] = tipos[this.value];
                document.getElementById('tipo_1').value = tipo1;
                document.getElementById('tipo_2').value = tipo2;
            });
        });

        function openAddQuestionModal() {
            document.querySelector('#questionModal .modal-title').textContent = 'Adicionar Nova Questão MBTI';
            document.querySelector('#questionForm').reset();
            document.querySelector('#questionForm [name="action"]').value = 'add';
            document.querySelector('#questionId').value = '';
            questionModal.show();
        }

        function editQuestion(questao) {
            document.querySelector('#questionModal .modal-title').textContent = 'Editar Questão MBTI';
            document.querySelector('#questionForm [name="action"]').value = 'edit';
            document.querySelector('#questionId').value = questao.id;
            document.querySelector('#questao').value = questao.questao;
            document.querySelector('#dimensao').value = questao.dimensao;
            document.querySelector('#opcao_1').value = questao.opcao_1;
            document.querySelector('#opcao_2').value = questao.opcao_2;
            document.querySelector('#tipo_1').value = questao.tipo_1;
            document.querySelector('#tipo_2').value = questao.tipo_2;
            questionModal.show();
        }

        function deleteQuestion(id) {
            if (confirm('Tem certeza que deseja excluir esta questão?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'process_questions_mbti.php';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete';
                
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = id;
                
                form.appendChild(actionInput);
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>

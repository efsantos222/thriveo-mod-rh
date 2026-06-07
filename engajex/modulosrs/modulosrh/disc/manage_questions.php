<?php
session_start();

// Verificar se está logado como superadmin
if (!isset($_SESSION['superadmin_authenticated']) || !$_SESSION['superadmin_authenticated']) {
    header('Location: superadmin_login.php');
    exit;
}

// Função para carregar questões
function carregarQuestoes() {
    $questoes = [];
    $arquivo = 'questoes/questoes_disc.csv';
    
    if (file_exists($arquivo)) {
        $fp = fopen($arquivo, 'r');
        fgetcsv($fp); // Pular cabeçalho
        
        while (($data = fgetcsv($fp)) !== FALSE) {
            $questoes[] = [
                'id' => $data[0],
                'questao' => $data[1],
                'opcao_d' => $data[2],
                'opcao_i' => $data[3],
                'opcao_s' => $data[4],
                'opcao_c' => $data[5]
            ];
        }
        fclose($fp);
    }
    
    return $questoes;
}

$questoes = carregarQuestoes();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Questões DISC - Sistema DISC/MBTI</title>
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
    </style>
</head>
<body>
    <?php require_once 'includes/menu.php'; renderMenu(); ?>
    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gerenciar Questões DISC</h2>
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
                    <h5 class="mb-0">Questão <?php echo htmlspecialchars($questao['id']); ?></h5>
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
                        <div class="col-md-3">
                            <p><span class="option-label">D:</span> <?php echo htmlspecialchars($questao['opcao_d']); ?></p>
                        </div>
                        <div class="col-md-3">
                            <p><span class="option-label">I:</span> <?php echo htmlspecialchars($questao['opcao_i']); ?></p>
                        </div>
                        <div class="col-md-3">
                            <p><span class="option-label">S:</span> <?php echo htmlspecialchars($questao['opcao_s']); ?></p>
                        </div>
                        <div class="col-md-3">
                            <p><span class="option-label">C:</span> <?php echo htmlspecialchars($questao['opcao_c']); ?></p>
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
                    <h5 class="modal-title">Adicionar Nova Questão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="questionForm" action="process_questions.php" method="POST">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="id" id="questionId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="questao" class="form-label">Questão</label>
                            <textarea class="form-control" id="questao" name="questao" rows="2" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="opcao_d" class="form-label">Opção D (Dominância)</label>
                                <input type="text" class="form-control" id="opcao_d" name="opcao_d" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="opcao_i" class="form-label">Opção I (Influência)</label>
                                <input type="text" class="form-control" id="opcao_i" name="opcao_i" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="opcao_s" class="form-label">Opção S (Estabilidade)</label>
                                <input type="text" class="form-control" id="opcao_s" name="opcao_s" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="opcao_c" class="form-label">Opção C (Conformidade)</label>
                                <input type="text" class="form-control" id="opcao_c" name="opcao_c" required>
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
        });

        function openAddQuestionModal() {
            document.querySelector('#questionModal .modal-title').textContent = 'Adicionar Nova Questão';
            document.querySelector('#questionForm').reset();
            document.querySelector('#questionForm [name="action"]').value = 'add';
            document.querySelector('#questionId').value = '';
            questionModal.show();
        }

        function editQuestion(questao) {
            document.querySelector('#questionModal .modal-title').textContent = 'Editar Questão';
            document.querySelector('#questionForm [name="action"]').value = 'edit';
            document.querySelector('#questionId').value = questao.id;
            document.querySelector('#questao').value = questao.questao;
            document.querySelector('#opcao_d').value = questao.opcao_d;
            document.querySelector('#opcao_i').value = questao.opcao_i;
            document.querySelector('#opcao_s').value = questao.opcao_s;
            document.querySelector('#opcao_c').value = questao.opcao_c;
            questionModal.show();
        }

        function deleteQuestion(id) {
            if (confirm('Tem certeza que deseja excluir esta questão?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'process_questions.php';

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

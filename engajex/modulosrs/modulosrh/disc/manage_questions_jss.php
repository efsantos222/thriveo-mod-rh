<?php
session_start();
require_once 'includes/menu.php';

// Verificar se está logado como superadmin
if (!isset($_SESSION['superadmin_authenticated']) || !$_SESSION['superadmin_authenticated']) {
    header('Location: superadmin_login.php');
    exit;
}

// Arquivo de questões
$questions_file = 'questoes/questoes_jss.csv';

// Carregar questões existentes
$questions = [];
if (file_exists($questions_file)) {
    $fp = fopen($questions_file, 'r');
    if ($fp !== false) {
        // Pular o cabeçalho
        fgetcsv($fp);
        while (($data = fgetcsv($fp)) !== FALSE) {
            $questions[] = [
                'id' => $data[0],
                'texto' => $data[1],
                'categoria' => $data[2]
            ];
        }
        fclose($fp);
    }
}

// Processar adição de nova questão
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add':
            if (!empty($_POST['texto']) && !empty($_POST['categoria'])) {
                $new_id = count($questions) + 1;
                $new_question = [
                    [$new_id, $_POST['texto'], $_POST['categoria']]
                ];
                
                $fp = fopen($questions_file, 'a');
                foreach ($new_question as $fields) {
                    fputcsv($fp, $fields);
                }
                fclose($fp);
                
                header('Location: manage_questions_jss.php?success=1');
                exit;
            }
            break;
            
        case 'edit':
            if (!empty($_POST['id']) && !empty($_POST['texto']) && !empty($_POST['categoria'])) {
                $id = $_POST['id'];
                $updated_questions = [];
                
                // Reabrir o arquivo para leitura
                $fp = fopen($questions_file, 'r');
                if ($fp !== false) {
                    // Guardar o cabeçalho
                    $header = fgetcsv($fp);
                    $updated_questions[] = $header;
                    
                    // Atualizar a questão específica
                    while (($data = fgetcsv($fp)) !== FALSE) {
                        if ($data[0] == $id) {
                            $updated_questions[] = [$id, $_POST['texto'], $_POST['categoria']];
                        } else {
                            $updated_questions[] = $data;
                        }
                    }
                    fclose($fp);
                    
                    // Reescrever o arquivo com as questões atualizadas
                    $fp = fopen($questions_file, 'w');
                    foreach ($updated_questions as $fields) {
                        fputcsv($fp, $fields);
                    }
                    fclose($fp);
                    
                    header('Location: manage_questions_jss.php?success=2');
                    exit;
                }
            }
            break;
            
        case 'delete':
            if (!empty($_POST['id'])) {
                $id = $_POST['id'];
                $updated_questions = [];
                
                // Reabrir o arquivo para leitura
                $fp = fopen($questions_file, 'r');
                if ($fp !== false) {
                    // Guardar o cabeçalho
                    $header = fgetcsv($fp);
                    $updated_questions[] = $header;
                    
                    // Excluir a questão específica
                    while (($data = fgetcsv($fp)) !== FALSE) {
                        if ($data[0] != $id) {
                            $updated_questions[] = $data;
                        }
                    }
                    fclose($fp);
                    
                    // Reescrever o arquivo com as questões atualizadas
                    $fp = fopen($questions_file, 'w');
                    foreach ($updated_questions as $fields) {
                        fputcsv($fp, $fields);
                    }
                    fclose($fp);
                    
                    header('Location: manage_questions_jss.php?success=3');
                    exit;
                }
            }
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Questões JSS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php renderMenu(); ?>
    
    <div class="container mt-4">
        <?php include 'header.php'; ?>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gerenciar Questões JSS</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
                <i class="bi bi-plus-circle"></i> Nova Questão
            </button>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php
                switch ($_GET['success']) {
                    case '1':
                        echo 'Questão adicionada com sucesso!';
                        break;
                    case '2':
                        echo 'Questão atualizada com sucesso!';
                        break;
                    case '3':
                        echo 'Questão excluída com sucesso!';
                        break;
                }
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($questions)): ?>
            <div class="alert alert-info">
                Nenhuma questão cadastrada ainda.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Texto da Questão</th>
                            <th>Categoria</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($questions as $question): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($question['id']); ?></td>
                                <td><?php echo htmlspecialchars($question['texto']); ?></td>
                                <td><?php echo htmlspecialchars($question['categoria']); ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary me-2" 
                                            onclick="openEditModal('<?php echo $question['id']; ?>', '<?php echo htmlspecialchars($question['texto']); ?>', '<?php echo htmlspecialchars($question['categoria']); ?>')">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger"
                                            onclick="confirmDelete('<?php echo $question['id']; ?>')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Modal Adicionar Questão -->
        <div class="modal fade" id="addQuestionModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Nova Questão JSS</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="manage_questions_jss.php" method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="add">
                            <div class="mb-3">
                                <label for="texto" class="form-label">Texto da Questão</label>
                                <textarea class="form-control" id="texto" name="texto" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="categoria" class="form-label">Categoria</label>
                                <input type="text" class="form-control" id="categoria" name="categoria" required>
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

        <!-- Modal Editar Questão -->
        <div class="modal fade" id="editQuestionModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Questão JSS</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="manage_questions_jss.php" method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" id="edit_id">
                            <div class="mb-3">
                                <label for="edit_texto" class="form-label">Texto da Questão</label>
                                <textarea class="form-control" id="edit_texto" name="texto" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="edit_categoria" class="form-label">Categoria</label>
                                <input type="text" class="form-control" id="edit_categoria" name="categoria" required>
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

        <!-- Form para excluir questão -->
        <form id="deleteForm" action="manage_questions_jss.php" method="POST" style="display: none;">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" id="delete_id">
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openEditModal(id, texto, categoria) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_texto').value = texto;
            document.getElementById('edit_categoria').value = categoria;
            new bootstrap.Modal(document.getElementById('editQuestionModal')).show();
        }

        function confirmDelete(id) {
            if (confirm('Tem certeza que deseja excluir esta questão?')) {
                document.getElementById('delete_id').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</body>
</html>

<?php
session_start();

// Verificar se está logado como superadmin
if (!isset($_SESSION['superadmin_authenticated'])) {
    header('Location: superadmin_login.php');
    exit;
}

$questions_file = 'questoes/questoes_bigfive.csv';

// Criar arquivo de questões se não existir
if (!file_exists($questions_file)) {
    $fp = fopen($questions_file, 'w');
    fputcsv($fp, ['id', 'questao', 'dimensao', 'inverso']);
    fclose($fp);
}

// Processar adição de nova questão
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $questao = $_POST['questao'];
        $dimensao = $_POST['dimensao'];
        $inverso = isset($_POST['inverso']) ? '1' : '0';

        // Ler questões existentes para determinar próximo ID
        $questions = array_map('str_getcsv', file($questions_file));
        $header = array_shift($questions);
        $next_id = count($questions) + 1;

        // Adicionar nova questão
        $fp = fopen($questions_file, 'a');
        fputcsv($fp, [$next_id, $questao, $dimensao, $inverso]);
        fclose($fp);

        header('Location: manage_questions_bigfive.php?success=1');
        exit;
    } elseif ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        // Ler todas as questões
        $questions = array_map('str_getcsv', file($questions_file));
        $header = array_shift($questions);
        
        // Remover a questão selecionada
        $questions = array_filter($questions, function($q) {
            return $q[0] != $_POST['id'];
        });
        
        // Reescrever o arquivo
        $fp = fopen($questions_file, 'w');
        fputcsv($fp, $header);
        foreach ($questions as $question) {
            fputcsv($fp, $question);
        }
        fclose($fp);

        header('Location: manage_questions_bigfive.php?success=2');
        exit;
    }
}

// Carregar questões existentes
$questions = array_map('str_getcsv', file($questions_file));
$header = array_shift($questions); // Remover cabeçalho
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Questões Big Five</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .question-form {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .questions-list {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .dimension-badge {
            font-size: 0.8em;
            padding: 5px 10px;
        }
        .table th {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <?php include 'header.php'; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php if ($_GET['success'] == 1): ?>
                    Questão adicionada com sucesso!
                <?php elseif ($_GET['success'] == 2): ?>
                    Questão removida com sucesso!
                <?php endif; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="question-form">
            <h3 class="mb-4">Adicionar Nova Questão</h3>
            <form method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="add">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="questao" class="form-label">Questão</label>
                        <textarea class="form-control" id="questao" name="questao" rows="3" required></textarea>
                        <div class="invalid-feedback">
                            Por favor, insira a questão.
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="dimensao" class="form-label">Dimensão</label>
                        <select class="form-select" id="dimensao" name="dimensao" required>
                            <option value="">Selecione...</option>
                            <option value="Abertura">Abertura à Experiência</option>
                            <option value="Conscienciosidade">Conscienciosidade</option>
                            <option value="Extroversao">Extroversão</option>
                            <option value="Amabilidade">Amabilidade</option>
                            <option value="Neuroticismo">Neuroticismo</option>
                        </select>
                        <div class="invalid-feedback">
                            Por favor, selecione a dimensão.
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-block">&nbsp;</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="inverso" name="inverso">
                            <label class="form-check-label" for="inverso">
                                Pontuação Inversa
                            </label>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Adicionar Questão
                    </button>
                </div>
            </form>
        </div>

        <div class="questions-list">
            <h3 class="mb-4">Questões Existentes</h3>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Questão</th>
                            <th>Dimensão</th>
                            <th>Inverso</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($questions as $question): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($question[0]); ?></td>
                            <td><?php echo htmlspecialchars($question[1]); ?></td>
                            <td>
                                <?php
                                $badge_class = match($question[2]) {
                                    'Abertura' => 'bg-primary',
                                    'Conscienciosidade' => 'bg-success',
                                    'Extroversao' => 'bg-info',
                                    'Amabilidade' => 'bg-warning',
                                    'Neuroticismo' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                                ?>
                                <span class="badge <?php echo $badge_class; ?> dimension-badge">
                                    <?php echo htmlspecialchars($question[2]); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($question[3] === '1'): ?>
                                    <span class="badge bg-secondary">Sim</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir esta questão?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($question[0]); ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validação do formulário
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html>

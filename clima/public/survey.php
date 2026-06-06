<?php
require_once '../config/config.php';
require_once '../config/database.php';

// Verificar se o respondente está autenticado
if (!isset($_SESSION['respondente_id'])) {
    redirect('/public/');
}

$db = Database::getInstance()->getConnection();

// Verificar se já respondeu a pesquisa desta semana
$stmt = $db->prepare("
    SELECT COUNT(*) as total 
    FROM respostas r 
    WHERE r.id_respondente = ? 
    AND YEARWEEK(r.data_resposta) = YEARWEEK(NOW())
");
$stmt->execute([$_SESSION['respondente_id']]);
$jaRespondeu = $stmt->fetch()['total'] > 0;

if ($jaRespondeu) {
    session_destroy();
    $_SESSION['error'] = "Você já respondeu a pesquisa desta semana.";
    redirect('/public/');
}

// Buscar perguntas ativas
$stmt = $db->prepare("
    SELECT id, texto_pergunta, tipo_resposta 
    FROM perguntas 
    WHERE ativa = 1 
    AND (
        recorrente = 1 
        OR (recorrente = 0 AND YEARWEEK(created_at) = YEARWEEK(NOW()))
    )
    ORDER BY created_at ASC
");
$stmt->execute();
$perguntas = $stmt->fetchAll();

if (empty($perguntas)) {
    session_destroy();
    $_SESSION['error'] = "Não há perguntas disponíveis no momento.";
    redirect('/public/');
}

// Buscar opções de resposta para perguntas de múltipla escolha
$opcoes = [];
$stmt = $db->prepare("
    SELECT id_pergunta, texto_opcao as texto, ordem
    FROM opcoes_resposta
    WHERE id_pergunta IN (
        SELECT id FROM perguntas 
        WHERE ativa = 1 
        AND tipo_resposta = 'multipla_escolha'
    )
    ORDER BY ordem ASC
");
$stmt->execute();
while ($row = $stmt->fetch()) {
    $opcoes[$row['id_pergunta']][] = $row;
}

// Processar respostas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();
        
        foreach ($_POST['respostas'] as $pergunta_id => $resposta) {
            if (empty(trim($resposta))) {
                throw new Exception("Todas as perguntas devem ser respondidas.");
            }
            
            $stmt = $db->prepare("
                INSERT INTO respostas (id_pergunta, id_respondente, resposta) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$pergunta_id, $_SESSION['respondente_id'], trim($resposta)]);
        }
        
        $db->commit();
        session_destroy();
        $_SESSION['success'] = "Obrigado por participar da pesquisa!";
        redirect('/public/');
        
    } catch (Exception $e) {
        $db->rollBack();
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesquisa de Clima</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .question-card {
            transition: transform 0.2s;
        }
        .question-card:hover {
            transform: translateY(-5px);
        }
        textarea {
            resize: vertical;
            min-height: 100px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Pesquisa de Clima Semanal</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="needs-validation" novalidate>
                            <?php foreach ($perguntas as $pergunta): ?>
                                <div class="mb-4 question-card">
                                    <label class="form-label fw-bold">
                                        <?php echo htmlspecialchars($pergunta['texto_pergunta']); ?>
                                    </label>
                                    
                                    <?php if ($pergunta['tipo_resposta'] === 'multipla_escolha' && isset($opcoes[$pergunta['id']])): ?>
                                        <?php foreach ($opcoes[$pergunta['id']] as $opcao): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" 
                                                       name="respostas[<?php echo $pergunta['id']; ?>]" 
                                                       value="<?php echo htmlspecialchars($opcao['texto']); ?>" 
                                                       required>
                                                <label class="form-check-label">
                                                    <?php echo htmlspecialchars($opcao['texto']); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <textarea class="form-control" 
                                                  name="respostas[<?php echo $pergunta['id']; ?>]" 
                                                  required></textarea>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    Enviar Respostas
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validação do formulário
        document.querySelector('form').addEventListener('submit', function(event) {
            if (!this.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            this.classList.add('was-validated');
        });
    </script>
</body>
</html>

<?php
session_start();

// Verificar se o usuário está autenticado
if (!isset($_SESSION['user_authenticated']) || !$_SESSION['user_authenticated']) {
    header('Location: login.php');
    exit;
}

// Verificar se há testes disponíveis
if (!isset($_SESSION['available_tests']) || empty($_SESSION['available_tests'])) {
    header('Location: login.php?error=no_tests');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selecionar Teste</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
            padding: 20px;
        }
        .content-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .test-card {
            transition: transform 0.2s;
            cursor: pointer;
        }
        .test-card:hover {
            transform: translateY(-5px);
        }
        .test-card.completed {
            opacity: 0.7;
            cursor: not-allowed;
        }
        .test-icon {
            font-size: 2rem;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content-container">
            <div class="text-center mb-4">
                <h2>Bem-vindo(a), <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>
                <p class="text-muted">Selecione o teste que deseja realizar</p>
            </div>

            <div class="row g-4">
                <?php foreach ($_SESSION['available_tests'] as $test): ?>
                    <div class="col-md-6">
                        <div class="card h-100 test-card <?php echo $test['status'] === 'completed' ? 'completed' : ''; ?>">
                            <div class="card-body text-center">
                                <div class="test-icon">
                                    <?php
                                    $icon = match($test['type']) {
                                        'DISC' => 'bi-pie-chart',
                                        'MBTI' => 'bi-person-badge',
                                        'Big Five' => 'bi-stars',
                                        'JSS' => 'bi-graph-up',
                                        default => 'bi-question-circle'
                                    };
                                    ?>
                                    <i class="bi <?php echo $icon; ?>"></i>
                                </div>
                                <h4 class="card-title"><?php echo htmlspecialchars($test['type']); ?></h4>
                                <p class="card-text">
                                    Status: 
                                    <span class="badge bg-<?php echo $test['status'] === 'completed' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($test['status']); ?>
                                    </span>
                                </p>
                                <?php if ($test['status'] === 'pending'): ?>
                                    <a href="<?php echo $test['url']; ?>" class="btn btn-primary">
                                        Iniciar Teste
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-secondary" disabled>
                                        Teste Concluído
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-4">
                <a href="logout.php" class="btn btn-outline-danger">
                    <i class="bi bi-box-arrow-right"></i> Sair
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

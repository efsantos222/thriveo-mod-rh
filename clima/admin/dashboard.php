<?php
require_once '../config/config.php';
require_once '../config/database.php';
checkAuth();

$db = Database::getInstance()->getConnection();

// Estatísticas básicas
$stats = [
    'total_respondentes' => $db->query("SELECT COUNT(*) FROM respondentes")->fetchColumn(),
    'total_perguntas' => $db->query("SELECT COUNT(*) FROM perguntas WHERE ativa = 1")->fetchColumn(),
    'respostas_semana' => $db->query("SELECT COUNT(*) FROM respostas WHERE YEARWEEK(data_resposta) = YEARWEEK(NOW())")->fetchColumn()
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Área Administrativa</title>
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
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="perguntas.php">Perguntas</a>
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
        <h2>Bem-vindo, <?php echo htmlspecialchars($_SESSION['admin_nome']); ?>!</h2>
        
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Total de Respondentes</h5>
                        <p class="card-text display-4"><?php echo $stats['total_respondentes']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Perguntas Ativas</h5>
                        <p class="card-text display-4"><?php echo $stats['total_perguntas']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Respostas esta Semana</h5>
                        <p class="card-text display-4"><?php echo $stats['respostas_semana']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Ações Rápidas
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="perguntas.php?action=new" class="btn btn-primary">Nova Pergunta</a>
                            <a href="respondentes.php?action=import" class="btn btn-success">Importar Respondentes</a>
                            <a href="relatorios.php" class="btn btn-info">Gerar Relatório</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Últimas Respostas
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <?php
                            try {
                                $stmt = $db->query("
                                    SELECT 
                                        r.data_resposta, 
                                        re.codigo,
                                        re.area
                                    FROM respostas r 
                                    JOIN respondentes re ON r.id_respondente = re.id 
                                    ORDER BY r.data_resposta DESC 
                                    LIMIT 5
                                ");
                                $ultimas_respostas = $stmt->fetchAll();
                            } catch (PDOException $e) {
                                error_log("Erro ao buscar últimas respostas no dashboard: " . $e->getMessage());
                                $ultimas_respostas = [];
                            }
                            ?>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Código</th>
                                        <th>Área</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($ultimas_respostas)): ?>
                                        <?php foreach ($ultimas_respostas as $resp): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y H:i', strtotime($resp['data_resposta'])); ?></td>
                                            <td><?php echo htmlspecialchars($resp['codigo']); ?></td>
                                            <td><?php echo htmlspecialchars($resp['area']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center">Nenhuma resposta registrada ainda.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

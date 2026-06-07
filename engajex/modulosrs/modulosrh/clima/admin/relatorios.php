<?php
require_once '../config/config.php';
require_once '../config/database.php';
checkAuth();

$db = Database::getInstance()->getConnection();

// Processar exportação CSV
if (isset($_POST['export_csv'])) {
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    
    // Validar datas
    if (empty($start_date) || empty($end_date)) {
        $_SESSION['error'] = "Por favor, selecione o período para exportação.";
    } else {
        // Preparar query
        $query = "
            SELECT 
                r.data_resposta,
                re.codigo as respondente_codigo,
                re.area as respondente_area,
                p.texto_pergunta,
                CASE
                    WHEN p.tipo_resposta = 'multipla_escolha' THEN 
                        (SELECT texto_opcao FROM opcoes_resposta WHERE id = CAST(r.resposta AS UNSIGNED))
                    ELSE r.resposta
                END as resposta
            FROM respostas r
            JOIN respondentes re ON r.id_respondente = re.id
            JOIN perguntas p ON r.id_pergunta = p.id
            WHERE DATE(r.data_resposta) BETWEEN ? AND ?
            ORDER BY r.data_resposta ASC, re.codigo ASC
        ";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$start_date, $end_date]);
        $results = $stmt->fetchAll();
        
        if (!empty($results)) {
            // Gerar CSV
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=pesquisa_clima_' . date('Y-m-d') . '.csv');
            
            $output = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Cabeçalho
            fputcsv($output, ['Data', 'Código', 'Área', 'Pergunta', 'Resposta']);
            
            // Dados
            foreach ($results as $row) {
                fputcsv($output, [
                    date('d/m/Y H:i', strtotime($row['data_resposta'])),
                    $row['respondente_codigo'],
                    $row['respondente_area'],
                    $row['texto_pergunta'],
                    $row['resposta']
                ]);
            }
            
            fclose($output);
            exit;
        } else {
            $_SESSION['error'] = "Nenhum dado encontrado para o período selecionado.";
        }
    }
}

// Buscar estatísticas por semana
$stats_query = "
    SELECT 
        YEARWEEK(r.data_resposta) as semana,
        MIN(DATE(r.data_resposta)) as inicio_semana,
        COUNT(DISTINCT r.id_respondente) as total_respondentes,
        COUNT(*) as total_respostas
    FROM respostas r
    GROUP BY YEARWEEK(r.data_resposta)
    ORDER BY semana DESC
    LIMIT 10
";

try {
    $stats = $db->query($stats_query)->fetchAll();
} catch (PDOException $e) {
    $stats = [];
}

// Buscar últimas respostas
$ultimas_query = "
    SELECT 
        r.data_resposta,
        re.codigo,
        re.area,
        p.texto_pergunta,
        CASE
            WHEN p.tipo_resposta = 'multipla_escolha' THEN 
                (SELECT texto_opcao FROM opcoes_resposta WHERE id = CAST(r.resposta AS UNSIGNED))
            ELSE r.resposta
        END as resposta_exibir
    FROM respostas r
    JOIN respondentes re ON r.id_respondente = re.id
    JOIN perguntas p ON r.id_pergunta = p.id
    ORDER BY r.data_resposta DESC
    LIMIT 10
";

try {
    $ultimas_respostas = $db->query($ultimas_query)->fetchAll();
    
    // Debug
    error_log("Query das últimas respostas: " . $ultimas_query);
    error_log("Número de respostas encontradas: " . count($ultimas_respostas));
    if (!empty($ultimas_respostas)) {
        error_log("Primeira resposta: " . print_r($ultimas_respostas[0], true));
    }
} catch (PDOException $e) {
    error_log("Erro ao buscar últimas respostas: " . $e->getMessage());
    $ultimas_respostas = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - Área Administrativa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
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
                        <a class="nav-link" href="perguntas.php">Perguntas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="respondentes.php">Respondentes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="relatorios.php">Relatórios</a>
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
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Exportar Relatório</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <div class="col-md-4">
                                <label for="start_date" class="form-label">Data Inicial</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" required>
                            </div>
                            <div class="col-md-4">
                                <label for="end_date" class="form-label">Data Final</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" name="export_csv" class="btn btn-primary d-block">
                                    <i class="bi bi-download"></i> Exportar CSV
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Estatísticas por Semana</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Semana</th>
                                        <th>Respondentes</th>
                                        <th>Total Respostas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($stats)): ?>
                                        <?php foreach ($stats as $stat): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($stat['inicio_semana'])); ?></td>
                                            <td><?php echo $stat['total_respondentes']; ?></td>
                                            <td><?php echo $stat['total_respostas']; ?></td>
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

            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Últimas Respostas</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Código</th>
                                        <th>Área</th>
                                        <th>Pergunta</th>
                                        <th>Resposta</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($ultimas_respostas)): ?>
                                        <?php foreach ($ultimas_respostas as $resposta): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y H:i', strtotime($resposta['data_resposta'])); ?></td>
                                            <td><?php echo htmlspecialchars($resposta['codigo']); ?></td>
                                            <td><?php echo htmlspecialchars($resposta['area']); ?></td>
                                            <td><?php echo htmlspecialchars($resposta['texto_pergunta']); ?></td>
                                            <td><?php echo htmlspecialchars($resposta['resposta_exibir']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">Nenhuma resposta registrada ainda.</td>
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
    <script>
        // Definir data máxima como hoje
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('start_date').max = today;
        document.getElementById('end_date').max = today;

        // Validar datas
        document.querySelector('form').addEventListener('submit', function(e) {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;

            if (startDate > endDate) {
                e.preventDefault();
                alert('A data inicial não pode ser maior que a data final.');
            }
        });
    </script>
</body>
</html>

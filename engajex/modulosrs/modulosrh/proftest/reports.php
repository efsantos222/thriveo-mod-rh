<?php
session_start();

// Verificar se está logado como superadmin
if (!isset($_SESSION['superadmin']) || !$_SESSION['superadmin']) {
    header('Location: superadmin_login.php');
    exit;
}

// Carregar estatísticas
$total_selecionadores = 0;
$total_candidatos = 0;
$total_avaliacoes = 0;
$avaliacoes_por_mes = [];
$candidatos_por_selecionador = [];

// Contar selecionadores e inicializar contadores
$admins_file = 'resultados/admins.csv';
if (file_exists($admins_file)) {
    $fp = fopen($admins_file, 'r');
    fgetcsv($fp); // Pular cabeçalho
    while (($data = fgetcsv($fp)) !== FALSE) {
        $total_selecionadores++;
        $candidatos_por_selecionador[$data[1]] = 0;
    }
    fclose($fp);
}

// Contar candidatos e avaliações
$candidatos_file = 'resultados/candidatos.csv';
if (file_exists($candidatos_file)) {
    $fp = fopen($candidatos_file, 'r');
    fgetcsv($fp); // Pular cabeçalho
    
    while (($data = fgetcsv($fp)) !== FALSE) {
        $total_candidatos++;
        
        // Contar candidatos por selecionador
        if (isset($candidatos_por_selecionador[$data[3]])) {
            $candidatos_por_selecionador[$data[3]]++;
        }
        
        // Verificar avaliação
        $avaliacao_file = 'resultados/' . str_replace(['@', '.'], '_', $data[1]) . '_avaliacao.csv';
        if (file_exists($avaliacao_file)) {
            $total_avaliacoes++;
            
            // Contar avaliações por mês
            $mes = date('Y-m', filemtime($avaliacao_file));
            if (!isset($avaliacoes_por_mes[$mes])) {
                $avaliacoes_por_mes[$mes] = 0;
            }
            $avaliacoes_por_mes[$mes]++;
        }
    }
    fclose($fp);
}

// Ordenar avaliações por mês
krsort($avaliacoes_por_mes);

// Calcular taxa de conclusão
$taxa_conclusao = $total_candidatos > 0 ? round(($total_avaliacoes / $total_candidatos) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - Sistema DISC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { 
            padding-top: 20px; 
            background-color: #f5f5f5;
        }
        .content-container { 
            background: #fff; 
            padding: 30px; 
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .stats-card {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .stats-card i {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        .stats-card .number {
            font-size: 2rem;
            font-weight: bold;
        }
        .stats-card .label {
            font-size: 1rem;
            opacity: 0.9;
        }
        .chart-container {
            position: relative;
            margin: auto;
            height: 300px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content-container">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">Relatórios e Estatísticas</h2>
                    <p class="text-muted mb-0">
                        <i class="bi bi-clock"></i> 
                        Atualizado em: <?php echo date('d/m/Y H:i'); ?>
                    </p>
                </div>
                <a href="superadmin_dashboard.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>

            <!-- Estatísticas Gerais -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <i class="bi bi-people"></i>
                        <div class="number"><?php echo $total_selecionadores; ?></div>
                        <div class="label">Selecionadores</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center" style="background: linear-gradient(45deg, #6f42c1, #4e2b89);">
                        <i class="bi bi-person-badge"></i>
                        <div class="number"><?php echo $total_candidatos; ?></div>
                        <div class="label">Candidatos</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center" style="background: linear-gradient(45deg, #28a745, #1e7e34);">
                        <i class="bi bi-check-circle"></i>
                        <div class="number"><?php echo $total_avaliacoes; ?></div>
                        <div class="label">Avaliações</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center" style="background: linear-gradient(45deg, #fd7e14, #b35900);">
                        <i class="bi bi-graph-up"></i>
                        <div class="number"><?php echo $taxa_conclusao; ?>%</div>
                        <div class="label">Taxa de Conclusão</div>
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="row">
                <!-- Avaliações por Mês -->
                <div class="col-md-8">
                    <div class="content-container">
                        <h4 class="mb-4">Avaliações por Mês</h4>
                        <div class="chart-container">
                            <canvas id="avaliacoesChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Candidatos por Selecionador -->
                <div class="col-md-4">
                    <div class="content-container">
                        <h4 class="mb-4">Candidatos por Selecionador</h4>
                        <div class="chart-container">
                            <canvas id="selecionadoresChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Configurar gráfico de avaliações por mês
        const avaliacoesCtx = document.getElementById('avaliacoesChart').getContext('2d');
        new Chart(avaliacoesCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_map(function($mes) {
                    return date('M/Y', strtotime($mes));
                }, array_keys($avaliacoes_por_mes))); ?>,
                datasets: [{
                    label: 'Avaliações Concluídas',
                    data: <?php echo json_encode(array_values($avaliacoes_por_mes)); ?>,
                    backgroundColor: 'rgba(40, 167, 69, 0.5)',
                    borderColor: 'rgb(40, 167, 69)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Configurar gráfico de candidatos por selecionador
        const selecionadoresCtx = document.getElementById('selecionadoresChart').getContext('2d');
        new Chart(selecionadoresCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_keys($candidatos_por_selecionador)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($candidatos_por_selecionador)); ?>,
                    backgroundColor: [
                        'rgba(0, 123, 255, 0.5)',
                        'rgba(111, 66, 193, 0.5)',
                        'rgba(40, 167, 69, 0.5)',
                        'rgba(253, 126, 20, 0.5)',
                        'rgba(220, 53, 69, 0.5)'
                    ],
                    borderColor: [
                        'rgb(0, 123, 255)',
                        'rgb(111, 66, 193)',
                        'rgb(40, 167, 69)',
                        'rgb(253, 126, 20)',
                        'rgb(220, 53, 69)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
    </script>
</body>
</html>

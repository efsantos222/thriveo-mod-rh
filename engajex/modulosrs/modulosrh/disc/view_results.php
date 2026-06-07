<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/batch_manager.php';

$auth = new Auth();
$role = $auth->getCurrentUserRole();
$db = getDbConnection();
$batchManager = new BatchManager($db);

if (!isset($_GET['candidate_id'])) {
    die('ID do candidato não fornecido');
}

$candidate_id = $_GET['candidate_id'];
$selected_batch = $_GET['batch_id'] ?? null;

// Buscar informações do candidato
$stmt = $db->prepare("
    SELECT 
        c.id,
        c.name,
        c.email,
        c.cargo,
        s.name as selector_name
    FROM candidates c
    LEFT JOIN users s ON c.selector_id = s.id
    WHERE c.id = ?
");
$stmt->execute([$candidate_id]);
$candidate = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$candidate) {
    die('Candidato não encontrado');
}

// Buscar todos os batches do candidato
$batches = $batchManager->getCandidateBatches($candidate_id);

// Se não houver batch selecionado, usar o mais recente
if (!$selected_batch && !empty($batches)) {
    $selected_batch = $batches[0]['id'];
}

// Buscar resultados do batch selecionado
$testResults = [];
if ($selected_batch) {
    $testResults = $batchManager->getBatchResults($selected_batch, $candidate_id);
}

error_log("Processing results for candidate {$candidate['name']}, batch {$selected_batch}:");
foreach ($testResults as $result) {
    error_log("Test type: {$result['test_type']}");
    error_log("Results: " . print_r(json_decode($result['results'], true), true));
}

// Função para formatar os resultados do teste
function formatTestResults($test_type, $results) {
    $results = json_decode($results, true);
    if (!$results) {
        error_log("Error decoding results for test type $test_type: " . json_last_encode_error());
        return ['error' => 'Erro ao processar resultados'];
    }

    switch ($test_type) {
        case 'disc':
            // Contar as respostas para cada tipo
            $counts = array_count_values($results);
            error_log("DISC counts: " . print_r($counts, true));
            return [
                'Dominância (D)' => ($counts['D'] ?? 0) * 5,
                'Influência (I)' => ($counts['I'] ?? 0) * 5,
                'Estabilidade (S)' => ($counts['S'] ?? 0) * 5,
                'Conformidade (C)' => ($counts['C'] ?? 0) * 5
            ];

        case 'mbti':
            return [
                'E/I' => $results['dimensions']['E'] - $results['dimensions']['I'],
                'S/N' => $results['dimensions']['S'] - $results['dimensions']['N'],
                'T/F' => $results['dimensions']['T'] - $results['dimensions']['F'],
                'J/P' => $results['dimensions']['J'] - $results['dimensions']['P']
            ];

        case 'bigfive':
            return [
                'Abertura' => $results['dimensions']['Abertura'],
                'Conscienciosidade' => $results['dimensions']['Conscienciosidade'],
                'Extroversao' => $results['dimensions']['Extroversao'],
                'Amabilidade' => $results['dimensions']['Amabilidade'],
                'Neuroticismo' => $results['dimensions']['Neuroticismo']
            ];

        case 'jss':
            return [
                'Sobrecarga' => $results['scores']['Sobrecarga']['average'],
                'Pressão Temporal' => $results['scores']['Pressão Temporal']['average'],
                'Pressão por Desempenho' => $results['scores']['Pressão por Desempenho']['average']
            ];

        default:
            return ['error' => 'Tipo de teste desconhecido'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados - <?php echo htmlspecialchars($candidate['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .results-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .close-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="results-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="bi bi-graph-up"></i>
                    Resultados do Teste
                </h2>
                <button type="button" class="btn btn-outline-secondary" onclick="window.close()">
                    <i class="bi bi-x-lg"></i>
                    Fechar
                </button>
            </div>
            <?php if (empty($testResults)): ?>
                <div class="alert alert-warning">
                    Nenhum resultado encontrado para este candidato.
                </div>
            <?php else: ?>
                <!-- Seletor de Batch -->
                <?php if (count($batches) > 1): ?>
                <div class="mb-4">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" class="row align-items-center">
                                <input type="hidden" name="candidate_id" value="<?php echo $candidate_id; ?>">
                                <div class="col-auto">
                                    <label class="form-label mb-0"><i class="bi bi-clock-history me-1"></i>Histórico de Avaliações:</label>
                                </div>
                                <div class="col-auto">
                                    <select name="batch_id" class="form-select" onchange="this.form.submit()">
                                        <?php foreach ($batches as $batch): ?>
                                            <option value="<?php echo $batch['id']; ?>" 
                                                    <?php echo $batch['id'] == $selected_batch ? 'selected' : ''; ?>>
                                                <?php 
                                                    echo date('d/m/Y H:i', strtotime($batch['created_at']));
                                                    if ($batch['notes']) echo ' - ' . htmlspecialchars($batch['notes']);
                                                ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php if ($selected_batch !== $batches[0]['id']): ?>
                                <div class="col-auto">
                                    <div class="alert alert-warning py-2 px-3 mb-0">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        Você está visualizando resultados históricos
                                    </div>
                                </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <div class="row">
                    <?php foreach ($testResults as $result): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <?php 
                                        switch ($result['test_type']) {
                                            case 'disc':
                                                echo 'Perfil DISC';
                                                break;
                                            case 'mbti':
                                                echo 'Perfil MBTI';
                                                break;
                                            case 'bigfive':
                                                echo 'Big Five';
                                                break;
                                            case 'jss':
                                                echo 'Satisfação no Trabalho (JSS)';
                                                break;
                                            default:
                                                echo strtoupper($result['test_type']);
                                        }
                                        ?>
                                        <small class="text-muted ms-2">
                                            (<?php echo date('d/m/Y', strtotime($result['completed_at'])); ?>)
                                        </small>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php
                                    try {
                                        $chartData = formatTestResults($result['test_type'], $result['results']);
                                        if (isset($chartData['error'])) {
                                            echo '<div class="alert alert-danger">' . htmlspecialchars($chartData['error']) . '</div>';
                                            continue;
                                        }
                                        $chartId = "chart_" . $result['test_type'] . "_" . uniqid();
                                    ?>
                                    <canvas id="<?php echo $chartId; ?>" style="min-height: 300px;"></canvas>
                                    <script>
                                    try {
                                        const chartData = <?php echo json_encode($chartData); ?>;
                                        console.log('Chart Data:', chartData); // Debug
                                        new Chart(document.getElementById('<?php echo $chartId; ?>'), {
                                            type: '<?php 
                                                switch ($result['test_type']) {
                                                    case 'disc':
                                                        echo 'radar';
                                                        break;
                                                    case 'mbti':
                                                        echo 'bar';
                                                        break;
                                                    case 'bigfive':
                                                        echo 'radar';
                                                        break;
                                                    case 'jss':
                                                        echo 'bar';
                                                        break;
                                                    default:
                                                        echo 'bar';
                                                }
                                            ?>',
                                            data: {
                                                labels: Object.keys(chartData),
                                                datasets: [{
                                                    label: '<?php 
                                                        switch ($result['test_type']) {
                                                            case 'disc':
                                                                echo 'Perfil DISC';
                                                                break;
                                                            case 'mbti':
                                                                echo 'Perfil MBTI';
                                                                break;
                                                            case 'bigfive':
                                                                echo 'Big Five';
                                                                break;
                                                            case 'jss':
                                                                echo 'Satisfação no Trabalho';
                                                                break;
                                                            default:
                                                                echo strtoupper($result['test_type']);
                                                        }
                                                    ?>',
                                                    data: Object.values(chartData),
                                                    backgroundColor: '<?php 
                                                        switch ($result['test_type']) {
                                                            case 'disc':
                                                                echo 'rgba(75, 108, 183, 0.5)';
                                                                break;
                                                            case 'mbti':
                                                                echo 'rgba(40, 167, 69, 0.5)';
                                                                break;
                                                            case 'bigfive':
                                                                echo 'rgba(255, 193, 7, 0.5)';
                                                                break;
                                                            case 'jss':
                                                                echo 'rgba(23, 162, 184, 0.5)';
                                                                break;
                                                            default:
                                                                echo 'rgba(75, 108, 183, 0.5)';
                                                        }
                                                    ?>',
                                                    borderColor: '<?php 
                                                        switch ($result['test_type']) {
                                                            case 'disc':
                                                                echo 'rgba(75, 108, 183, 1)';
                                                                break;
                                                            case 'mbti':
                                                                echo 'rgba(40, 167, 69, 1)';
                                                                break;
                                                            case 'bigfive':
                                                                echo 'rgba(255, 193, 7, 1)';
                                                                break;
                                                            case 'jss':
                                                                echo 'rgba(23, 162, 184, 1)';
                                                                break;
                                                            default:
                                                                echo 'rgba(75, 108, 183, 1)';
                                                        }
                                                    ?>',
                                                    borderWidth: 1,
                                                    pointBackgroundColor: '#fff',
                                                    pointBorderColor: '#000',
                                                    pointHoverBackgroundColor: '#fff',
                                                    pointHoverBorderColor: '#000'
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                scales: {
                                                    r: {
                                                        beginAtZero: true,
                                                        max: <?php 
                                                            switch ($result['test_type']) {
                                                                case 'disc':
                                                                    echo '100';
                                                                    break;
                                                                case 'mbti':
                                                                    echo '10';
                                                                    break;
                                                                case 'bigfive':
                                                                case 'jss':
                                                                    echo '5';
                                                                    break;
                                                                default:
                                                                    echo '100';
                                                            }
                                                        ?>
                                                    }
                                                },
                                                plugins: {
                                                    legend: {
                                                        display: true,
                                                        position: 'top'
                                                    }
                                                }
                                            }
                                        });
                                    } catch (error) {
                                        console.error('Erro ao criar gráfico:', error);
                                        document.getElementById('<?php echo $chartId; ?>').insertAdjacentHTML('beforebegin', 
                                            '<div class="alert alert-danger">Erro ao criar gráfico: ' + error.message + '</div>');
                                    }
                                    </script>
                                    <?php
                                    } catch (Exception $e) {
                                        echo '<div class="alert alert-danger">Erro ao processar resultados: ' . htmlspecialchars($e->getMessage()) . '</div>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Botão flutuante para fechar -->
        <button type="button" class="btn btn-primary close-button" onclick="window.close()">
            <i class="bi bi-x-lg me-2"></i>
            Fechar Resultados
        </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Ao clicar no botão voltar, garantir que a aba correta será ativada
        document.querySelectorAll('a[href$="selector_panel.php"]').forEach(function(link) {
            link.addEventListener('click', function(e) {
                if (this.getAttribute('href').indexOf('#') === -1) {
                    sessionStorage.setItem('activeTab', '#results');
                }
            });
        });
    </script>
</body>
</html>

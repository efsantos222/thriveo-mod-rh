<?php
require_once 'includes/auth.php';

$auth = new Auth();
$auth->requireAuth();

$db = getDbConnection();

// Verificar o tipo de teste
$testType = $_GET['type'] ?? '';
if (!in_array($testType, ['disc', 'mbti', 'bigfive', 'jss'])) {
    header('Location: candidate_panel.php');
    exit;
}

// Buscar informações do candidato e resultado do teste
$stmt = $db->prepare("
    SELECT c.*, tr.results, tr.completed_at
    FROM candidates c
    INNER JOIN test_results tr ON c.id = tr.candidate_id
    WHERE c.email = ? AND tr.test_type = ?
");
$stmt->execute([$_SESSION['user_email'], $testType]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    header('Location: candidate_panel.php');
    exit;
}

$results = json_decode($data['results'], true);

// Funções para interpretar resultados
function getDiscInterpretation($scores) {
    $interpretations = [
        'D' => [
            'title' => 'Dominância',
            'high' => 'Você tende a ser direto, decisivo e focado em resultados.',
            'low' => 'Você tende a ser cooperativo, evitando conflitos.'
        ],
        'I' => [
            'title' => 'Influência',
            'high' => 'Você tende a ser sociável, persuasivo e otimista.',
            'low' => 'Você tende a ser mais reservado e focado em fatos.'
        ],
        'S' => [
            'title' => 'Estabilidade',
            'high' => 'Você tende a ser paciente, consistente e bom ouvinte.',
            'low' => 'Você tende a ser flexível e gosta de mudanças.'
        ],
        'C' => [
            'title' => 'Conformidade',
            'high' => 'Você tende a ser preciso, analítico e sistemático.',
            'low' => 'Você tende a ser independente e não convencional.'
        ]
    ];

    $result = [];
    foreach ($scores as $type => $score) {
        $result[$type] = [
            'title' => $interpretations[$type]['title'],
            'score' => $score,
            'interpretation' => $score > 5 ? $interpretations[$type]['high'] : $interpretations[$type]['low']
        ];
    }
    return $result;
}

function getMbtiInterpretation($type) {
    $interpretations = [
        'E' => 'Extrovertido - Você ganha energia interagindo com outras pessoas.',
        'I' => 'Introvertido - Você ganha energia tendo tempo sozinho para reflexão.',
        'S' => 'Sensorial - Você prefere informações concretas e detalhadas.',
        'N' => 'Intuitivo - Você prefere ideias abstratas e possibilidades futuras.',
        'T' => 'Pensamento - Você toma decisões baseadas em lógica e análise objetiva.',
        'F' => 'Sentimento - Você toma decisões baseadas em valores e considerações pessoais.',
        'J' => 'Julgamento - Você prefere ambientes estruturados e planejados.',
        'P' => 'Percepção - Você prefere ambientes flexíveis e adaptáveis.'
    ];

    $result = [];
    foreach ($type as $dimension => $preference) {
        $result[$dimension] = [
            'preference' => $preference,
            'interpretation' => $interpretations[$preference]
        ];
    }
    return $result;
}

function getBigFiveInterpretation($scores) {
    $interpretations = [
        'Abertura' => [
            'high' => 'Você é muito aberto a novas experiências, criativo e curioso.',
            'medium' => 'Você tem um equilíbrio entre tradição e inovação.',
            'low' => 'Você prefere rotinas e abordagens convencionais.'
        ],
        'Conscienciosidade' => [
            'high' => 'Você é muito organizado, responsável e focado em objetivos.',
            'medium' => 'Você tem um equilíbrio entre flexibilidade e organização.',
            'low' => 'Você tende a ser mais flexível e espontâneo.'
        ],
        'Extroversao' => [
            'high' => 'Você é muito sociável, energético e assertivo.',
            'medium' => 'Você tem um equilíbrio entre socialização e introspecção.',
            'low' => 'Você prefere ambientes mais calmos e interações mais reservadas.'
        ],
        'Amabilidade' => [
            'high' => 'Você é muito cooperativo, compassivo e considerado.',
            'medium' => 'Você tem um equilíbrio entre cooperação e assertividade.',
            'low' => 'Você tende a ser mais direto e focado em objetivos.'
        ],
        'Neuroticismo' => [
            'high' => 'Você tende a experimentar emoções mais intensamente.',
            'medium' => 'Você tem um equilíbrio emocional moderado.',
            'low' => 'Você tende a ser mais calmo e emocionalmente estável.'
        ]
    ];

    $result = [];
    foreach ($scores as $dimension => $score) {
        $level = $score > 70 ? 'high' : ($score > 30 ? 'medium' : 'low');
        $result[$dimension] = [
            'score' => $score,
            'interpretation' => $interpretations[$dimension][$level]
        ];
    }
    return $result;
}

function getJssInterpretation($scores) {
    $interpretations = [
        'Pressão Temporal' => [
            'high' => 'Você está experimentando um alto nível de estresse relacionado a prazos e tempo.',
            'medium' => 'Você está lidando razoavelmente bem com as pressões de tempo.',
            'low' => 'Você está gerenciando bem as demandas temporais.'
        ],
        'Pressão por Desempenho' => [
            'high' => 'Você está sentindo muita pressão para atingir expectativas.',
            'medium' => 'Você está lidando moderadamente bem com as expectativas de desempenho.',
            'low' => 'Você está confortável com as expectativas de desempenho.'
        ],
        'Sobrecarga' => [
            'high' => 'Você está experimentando uma sobrecarga significativa de trabalho.',
            'medium' => 'Você está lidando razoavelmente bem com a carga de trabalho.',
            'low' => 'Você está gerenciando bem a quantidade de trabalho.'
        ]
    ];

    $result = [];
    foreach ($scores as $category => $score) {
        $level = $score > 70 ? 'high' : ($score > 30 ? 'medium' : 'low');
        $result[$category] = [
            'score' => $score,
            'interpretation' => $interpretations[$category][$level]
        ];
    }
    return $result;
}

// Obter interpretação baseada no tipo de teste
switch ($testType) {
    case 'disc':
        $interpretation = getDiscInterpretation($results);
        break;
    case 'mbti':
        $interpretation = getMbtiInterpretation($results);
        break;
    case 'bigfive':
        $interpretation = getBigFiveInterpretation($results);
        break;
    case 'jss':
        $interpretation = getJssInterpretation($results);
        break;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados <?php echo strtoupper($testType); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Resultados <?php echo strtoupper($testType); ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="candidate_panel.php">
                            <i class="bi bi-arrow-left"></i> Voltar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Sair</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col">
                <h2>Seus Resultados</h2>
                <p class="text-muted">
                    Teste realizado em: <?php echo date('d/m/Y H:i', strtotime($data['completed_at'])); ?>
                </p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Gráfico</h5>
                        <canvas id="resultsChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Interpretação</h5>
                        
                        <?php if ($testType === 'disc'): ?>
                            <?php foreach ($interpretation as $type => $data): ?>
                            <div class="mb-3">
                                <h6><?php echo $data['title']; ?></h6>
                                <div class="progress mb-2">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: <?php echo ($data['score'] * 10); ?>%">
                                        <?php echo $data['score']; ?>
                                    </div>
                                </div>
                                <p class="small"><?php echo $data['interpretation']; ?></p>
                            </div>
                            <?php endforeach; ?>

                        <?php elseif ($testType === 'mbti'): ?>
                            <?php foreach ($interpretation as $dimension => $data): ?>
                            <div class="mb-3">
                                <h6><?php echo $dimension; ?></h6>
                                <p><?php echo $data['interpretation']; ?></p>
                            </div>
                            <?php endforeach; ?>

                        <?php elseif ($testType === 'bigfive'): ?>
                            <?php foreach ($interpretation as $dimension => $data): ?>
                            <div class="mb-3">
                                <h6><?php echo $dimension; ?></h6>
                                <div class="progress mb-2">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: <?php echo $data['score']; ?>%">
                                        <?php echo round($data['score']); ?>%
                                    </div>
                                </div>
                                <p class="small"><?php echo $data['interpretation']; ?></p>
                            </div>
                            <?php endforeach; ?>

                        <?php elseif ($testType === 'jss'): ?>
                            <?php foreach ($interpretation as $category => $data): ?>
                            <div class="mb-3">
                                <h6><?php echo $category; ?></h6>
                                <div class="progress mb-2">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: <?php echo $data['score']; ?>%">
                                        <?php echo round($data['score']); ?>%
                                    </div>
                                </div>
                                <p class="small"><?php echo $data['interpretation']; ?></p>
                            </div>
                            <?php endforeach; ?>

                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Configurar gráfico baseado no tipo de teste
    const ctx = document.getElementById('resultsChart').getContext('2d');
    
    <?php if ($testType === 'disc'): ?>
    new Chart(ctx, {
        type: 'radar',
        data: {
            labels: ['Dominância', 'Influência', 'Estabilidade', 'Conformidade'],
            datasets: [{
                label: 'DISC',
                data: [
                    <?php echo $results['D']; ?>,
                    <?php echo $results['I']; ?>,
                    <?php echo $results['S']; ?>,
                    <?php echo $results['C']; ?>
                ],
                fill: true,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgb(54, 162, 235)',
                pointBackgroundColor: 'rgb(54, 162, 235)',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgb(54, 162, 235)'
            }]
        }
    });

    <?php elseif ($testType === 'bigfive'): ?>
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_keys($results)); ?>,
            datasets: [{
                label: 'Pontuação',
                data: <?php echo json_encode(array_values($results)); ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgb(75, 192, 192)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });

    <?php elseif ($testType === 'jss'): ?>
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_keys($results)); ?>,
            datasets: [{
                label: 'Nível de Estresse',
                data: <?php echo json_encode(array_values($results)); ?>,
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgb(255, 99, 132)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });

    <?php endif; ?>
    </script>
</body>
</html>

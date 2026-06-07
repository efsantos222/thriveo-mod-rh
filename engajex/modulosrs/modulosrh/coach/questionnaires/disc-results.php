<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: ../index.php');
    exit;
}

// Get assessment results
$config = require '../config/database.php';
try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}",
        $config['username'],
        $config['password'],
        $config['options']
    );
    
    $stmt = $pdo->prepare("
        SELECT r.*, q.titulo
        FROM respostas_questionario r
        JOIN questionarios q ON r.id_questionario = q.id_questionario
        WHERE r.id_questionario = :id
        AND r.id_usuario = :user_id
        AND q.tipo = 'DISC'
    ");
    
    $stmt->execute([
        'id' => $_GET['id'],
        'user_id' => $_SESSION['user_id']
    ]);
    
    $result = $stmt->fetch();
    
    if (!$result) {
        header('Location: index.php');
        exit;
    }
    
    $assessment_data = json_decode($result['resposta'], true);
    
} catch (Exception $e) {
    die('Erro ao carregar resultados: ' . $e->getMessage());
}

// DISC profile descriptions
$profiles = [
    'D' => [
        'title' => 'Dominância',
        'traits' => ['Direto', 'Decisivo', 'Determinado', 'Dinâmico'],
        'strengths' => ['Liderança', 'Iniciativa', 'Resolução de problemas'],
        'challenges' => ['Impaciência', 'Insensibilidade', 'Delegação']
    ],
    'I' => [
        'title' => 'Influência',
        'traits' => ['Influente', 'Inspirador', 'Interativo', 'Interessado'],
        'strengths' => ['Comunicação', 'Entusiasmo', 'Networking'],
        'challenges' => ['Desorganização', 'Superficialidade', 'Foco']
    ],
    'S' => [
        'title' => 'Estabilidade',
        'traits' => ['Estável', 'Solidário', 'Sereno', 'Sistemático'],
        'strengths' => ['Consistência', 'Cooperação', 'Suporte'],
        'challenges' => ['Resistência a mudanças', 'Passividade', 'Decisão']
    ],
    'C' => [
        'title' => 'Conformidade',
        'traits' => ['Cauteloso', 'Correto', 'Calculista', 'Criterioso'],
        'strengths' => ['Análise', 'Precisão', 'Organização'],
        'challenges' => ['Perfeccionismo', 'Indecisão', 'Crítica']
    ]
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados DISC - Sistema de Coaching</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/questionnaires.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="content">
            <header class="dashboard-header">
                <h1>Resultados da Avaliação DISC</h1>
                <div class="header-actions">
                    <button class="btn-secondary" onclick="window.print()">Imprimir</button>
                    <a href="disc.php" class="btn-primary">Nova Avaliação</a>
                </div>
            </header>
            
            <div class="results-container">
                <div class="profile-summary card">
                    <h2>Seu Perfil DISC</h2>
                    <div class="profile-type">
                        <?php 
                        echo $assessment_data['primary_style'];
                        if ($assessment_data['secondary_style']) {
                            echo '/' . $assessment_data['secondary_style'];
                        }
                        ?>
                    </div>
                    <p class="profile-description">
                        <?php
                        echo $profiles[$assessment_data['primary_style']]['title'];
                        if ($assessment_data['secondary_style']) {
                            echo ' com ' . strtolower($profiles[$assessment_data['secondary_style']]['title']);
                        }
                        ?>
                    </p>
                </div>
                
                <div class="card">
                    <h3>Gráfico DISC</h3>
                    <canvas id="discChart"></canvas>
                </div>
                
                <div class="characteristics-grid">
                    <div class="card">
                        <h3>Características Principais</h3>
                        <ul>
                            <?php foreach ($profiles[$assessment_data['primary_style']]['traits'] as $trait): ?>
                            <li><?php echo $trait; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="card">
                        <h3>Pontos Fortes</h3>
                        <ul>
                            <?php foreach ($profiles[$assessment_data['primary_style']]['strengths'] as $strength): ?>
                            <li><?php echo $strength; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="card">
                        <h3>Desafios</h3>
                        <ul>
                            <?php foreach ($profiles[$assessment_data['primary_style']]['challenges'] as $challenge): ?>
                            <li><?php echo $challenge; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <h3>Recomendações de Desenvolvimento</h3>
                    <div class="recommendations">
                        <?php
                        $recommendations = [
                            'D' => [
                                'Desenvolva a escuta ativa',
                                'Pratique a paciência',
                                'Aprimore habilidades de delegação'
                            ],
                            'I' => [
                                'Desenvolva habilidades de organização',
                                'Aprimore o foco em detalhes',
                                'Pratique a conclusão de tarefas'
                            ],
                            'S' => [
                                'Desenvolva assertividade',
                                'Pratique a adaptabilidade',
                                'Aprimore a tomada de decisão'
                            ],
                            'C' => [
                                'Desenvolva flexibilidade',
                                'Pratique a comunicação empática',
                                'Aprimore a tomada de decisão rápida'
                            ]
                        ];
                        
                        foreach ($recommendations[$assessment_data['primary_style']] as $recommendation):
                        ?>
                        <div class="recommendation-item">
                            <span class="recommendation-icon">✓</span>
                            <?php echo $recommendation; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
    // Create DISC chart
    const ctx = document.getElementById('discChart').getContext('2d');
    new Chart(ctx, {
        type: 'radar',
        data: {
            labels: ['Dominância', 'Influência', 'Estabilidade', 'Conformidade'],
            datasets: [{
                label: 'Seu Perfil DISC',
                data: [
                    <?php echo $assessment_data['scores']['D']; ?>,
                    <?php echo $assessment_data['scores']['I']; ?>,
                    <?php echo $assessment_data['scores']['S']; ?>,
                    <?php echo $assessment_data['scores']['C']; ?>
                ],
                backgroundColor: 'rgba(52, 152, 219, 0.2)',
                borderColor: 'rgba(52, 152, 219, 1)',
                pointBackgroundColor: 'rgba(52, 152, 219, 1)',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgba(52, 152, 219, 1)'
            }]
        },
        options: {
            scales: {
                r: {
                    angleLines: {
                        display: true
                    },
                    suggestedMin: 0,
                    suggestedMax: 100
                }
            }
        }
    });
    </script>
</body>
</html>

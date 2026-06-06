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
        AND q.tipo = 'MBTI'
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

// MBTI type descriptions
$types = [
    'E' => ['title' => 'Extroversão', 'description' => 'Foco no mundo exterior e nas pessoas'],
    'I' => ['title' => 'Introversão', 'description' => 'Foco no mundo interior e nas ideias'],
    'S' => ['title' => 'Sensação', 'description' => 'Foco em informações concretas e detalhes'],
    'N' => ['title' => 'Intuição', 'description' => 'Foco em padrões e possibilidades'],
    'T' => ['title' => 'Pensamento', 'description' => 'Decisões baseadas na lógica'],
    'F' => ['title' => 'Sentimento', 'description' => 'Decisões baseadas em valores'],
    'J' => ['title' => 'Julgamento', 'description' => 'Preferência por organização e planejamento'],
    'P' => ['title' => 'Percepção', 'description' => 'Preferência por flexibilidade e adaptação']
];

// Full type descriptions
$fullTypes = [
    'ISTJ' => ['title' => 'O Inspetor', 'keywords' => ['Responsável', 'Organizado', 'Prático']],
    'ISFJ' => ['title' => 'O Protetor', 'keywords' => ['Dedicado', 'Prestativo', 'Detalhista']],
    'INFJ' => ['title' => 'O Conselheiro', 'keywords' => ['Idealista', 'Complexo', 'Profundo']],
    'INTJ' => ['title' => 'O Arquiteto', 'keywords' => ['Estratégico', 'Independente', 'Inovador']],
    'ISTP' => ['title' => 'O Artesão', 'keywords' => ['Versátil', 'Prático', 'Objetivo']],
    'ISFP' => ['title' => 'O Artista', 'keywords' => ['Sensível', 'Gentil', 'Espontâneo']],
    'INFP' => ['title' => 'O Mediador', 'keywords' => ['Idealista', 'Criativo', 'Empático']],
    'INTP' => ['title' => 'O Lógico', 'keywords' => ['Analítico', 'Teórico', 'Inovador']],
    'ESTP' => ['title' => 'O Empresário', 'keywords' => ['Energético', 'Pragmático', 'Flexível']],
    'ESFP' => ['title' => 'O Entertainer', 'keywords' => ['Espontâneo', 'Entusiasta', 'Social']],
    'ENFP' => ['title' => 'O Defensor', 'keywords' => ['Criativo', 'Entusiasta', 'Versátil']],
    'ENTP' => ['title' => 'O Inovador', 'keywords' => ['Inventivo', 'Curioso', 'Versátil']],
    'ESTJ' => ['title' => 'O Executivo', 'keywords' => ['Organizado', 'Lógico', 'Objetivo']],
    'ESFJ' => ['title' => 'O Cônsul', 'keywords' => ['Cooperativo', 'Leal', 'Tradicional']],
    'ENFJ' => ['title' => 'O Protagonista', 'keywords' => ['Carismático', 'Idealista', 'Líder']],
    'ENTJ' => ['title' => 'O Comandante', 'keywords' => ['Líder', 'Estratégico', 'Eficiente']]
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados MBTI - Sistema de Coaching</title>
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
                <h1>Resultados MBTI</h1>
                <div class="header-actions">
                    <button class="btn-secondary" onclick="window.print()">Imprimir</button>
                    <a href="mbti.php" class="btn-primary">Nova Avaliação</a>
                </div>
            </header>
            
            <div class="results-container">
                <div class="profile-summary card">
                    <h2>Seu Tipo MBTI</h2>
                    <div class="profile-type">
                        <?php echo $assessment_data['type']; ?>
                    </div>
                    <p class="profile-description">
                        <?php echo $fullTypes[$assessment_data['type']]['title']; ?>
                    </p>
                    <div class="profile-keywords">
                        <?php foreach ($fullTypes[$assessment_data['type']]['keywords'] as $keyword): ?>
                        <span class="keyword"><?php echo $keyword; ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="preferences-grid">
                    <?php
                    $dimensions = [
                        ['E', 'I'],
                        ['S', 'N'],
                        ['T', 'F'],
                        ['J', 'P']
                    ];
                    
                    foreach ($dimensions as $pair):
                        $left = $pair[0];
                        $right = $pair[1];
                    ?>
                    <div class="card preference-card">
                        <div class="preference-bar">
                            <div class="preference-label">
                                <strong><?php echo $left; ?></strong>
                                <span><?php echo $types[$left]['title']; ?></span>
                            </div>
                            <div class="bar-container">
                                <div class="bar" style="width: <?php echo $assessment_data['percentages'][$left]; ?>%"></div>
                            </div>
                            <div class="preference-label text-right">
                                <strong><?php echo $right; ?></strong>
                                <span><?php echo $types[$right]['title']; ?></span>
                            </div>
                        </div>
                        <div class="preference-description">
                            <p><strong><?php echo $types[$left]['title']; ?>:</strong> <?php echo $types[$left]['description']; ?></p>
                            <p><strong><?php echo $types[$right]['title']; ?>:</strong> <?php echo $types[$right]['description']; ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="card">
                    <h3>Recomendações de Desenvolvimento</h3>
                    <div class="recommendations">
                        <?php
                        $typeRecommendations = [
                            'E' => [
                                'Pratique a escuta ativa',
                                'Reserve momentos para reflexão',
                                'Desenvolva atividades individuais'
                            ],
                            'I' => [
                                'Participe mais de atividades em grupo',
                                'Compartilhe mais suas ideias',
                                'Desenvolva networking'
                            ],
                            'S' => [
                                'Explore novas possibilidades',
                                'Pratique pensamento abstrato',
                                'Considere diferentes perspectivas'
                            ],
                            'N' => [
                                'Foque em detalhes práticos',
                                'Estabeleça rotinas',
                                'Mantenha os pés no chão'
                            ],
                            'T' => [
                                'Considere o impacto emocional',
                                'Desenvolva empatia',
                                'Pratique inteligência emocional'
                            ],
                            'F' => [
                                'Pratique análise objetiva',
                                'Tome decisões baseadas em dados',
                                'Desenvolva pensamento crítico'
                            ],
                            'J' => [
                                'Seja mais flexível',
                                'Aceite mudanças de planos',
                                'Explore alternativas'
                            ],
                            'P' => [
                                'Estabeleça metas claras',
                                'Desenvolva planejamento',
                                'Crie rotinas produtivas'
                            ]
                        ];
                        
                        foreach (str_split($assessment_data['type']) as $letter) {
                            foreach ($typeRecommendations[$letter] as $recommendation):
                            ?>
                            <div class="recommendation-item">
                                <span class="recommendation-icon">✓</span>
                                <?php echo $recommendation; ?>
                            </div>
                            <?php
                            endforeach;
                        }
                        ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

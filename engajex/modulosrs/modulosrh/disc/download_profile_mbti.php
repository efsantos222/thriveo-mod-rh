<?php
session_start();

// Verificar se está logado como superadmin ou admin
if ((!isset($_SESSION['admin_authenticated']) || !$_SESSION['admin_authenticated']) && 
    (!isset($_SESSION['superadmin_authenticated']) || !$_SESSION['superadmin_authenticated'])) {
    header('Location: admin_login.php');
    exit;
}

// Verificar se o email foi especificado
if (!isset($_GET['email'])) {
    header('Location: view_candidates_mbti.php');
    exit;
}

$email = $_GET['email'];
$avaliacao_file = 'resultados/' . str_replace(['@', '.'], '_', $email) . '_avaliacao_mbti.csv';

if (!file_exists($avaliacao_file)) {
    header('Location: view_candidates_mbti.php?error=no_results');
    exit;
}

// Carregar dados do candidato
$candidatos_file = 'resultados/candidatos_mbti.csv';
$candidato_nome = '';
$candidato_cargo = '';
$selecionador_nome = '';
$data_avaliacao = '';

if (($handle = fopen($candidatos_file, "r")) !== FALSE) {
    fgetcsv($handle); // Pular cabeçalho
    while (($data = fgetcsv($handle)) !== FALSE) {
        if ($data[4] === $email) {
            $candidato_nome = $data[3];
            $candidato_cargo = $data[6];
            $selecionador_nome = $data[1];
            $data_avaliacao = $data[0];
            break;
        }
    }
    fclose($handle);
}

// Carregar resultados da avaliação
$resultado = [];
if (($handle = fopen($avaliacao_file, "r")) !== FALSE) {
    fgetcsv($handle); // Pular cabeçalho
    if (($data = fgetcsv($handle)) !== FALSE) {
        $resultado = [
            'data' => $data[0],
            'tipo' => $data[1],
            'pontuacoes' => [
                'E/I' => intval($data[2]),
                'S/N' => intval($data[3]),
                'T/F' => intval($data[4]),
                'J/P' => intval($data[5])
            ]
        ];
    }
    fclose($handle);
}

if (empty($resultado)) {
    header('Location: view_candidates_mbti.php?error=no_results');
    exit;
}

$tipo_mbti = $resultado['tipo'];

// Descrições dos tipos MBTI
$tipos_mbti = [
    'ISTJ' => [
        'titulo' => 'O Inspetor',
        'caracteristicas' => [
            'Responsável e comprometido',
            'Prático e factual',
            'Organizado e metódico',
            'Leal e confiável'
        ]
    ],
    'ISFJ' => [
        'titulo' => 'O Protetor',
        'caracteristicas' => [
            'Dedicado e protetor',
            'Prestativo e atencioso',
            'Detalhista e consciente',
            'Tradicional e responsável'
        ]
    ],
    'INFJ' => [
        'titulo' => 'O Conselheiro',
        'caracteristicas' => [
            'Idealista e criativo',
            'Comprometido com valores',
            'Busca significado e conexão',
            'Profundo e complexo'
        ]
    ],
    'INTJ' => [
        'titulo' => 'O Arquiteto',
        'caracteristicas' => [
            'Estratégico e lógico',
            'Independente e determinado',
            'Inovador e analítico',
            'Perfeccionista e visionário'
        ]
    ],
    'ISTP' => [
        'titulo' => 'O Artesão',
        'caracteristicas' => [
            'Observador e prático',
            'Lógico e eficiente',
            'Adaptável e versátil',
            'Orientado para ação'
        ]
    ],
    'ISFP' => [
        'titulo' => 'O Artista',
        'caracteristicas' => [
            'Sensível e estético',
            'Gentil e harmonioso',
            'Presente e espontâneo',
            'Flexível e compreensivo'
        ]
    ],
    'INFP' => [
        'titulo' => 'O Mediador',
        'caracteristicas' => [
            'Idealista e criativo',
            'Empático e autêntico',
            'Curioso e adaptável',
            'Dedicado a valores'
        ]
    ],
    'INTP' => [
        'titulo' => 'O Lógico',
        'caracteristicas' => [
            'Analítico e teórico',
            'Lógico e preciso',
            'Original e inventivo',
            'Adaptável e questionador'
        ]
    ],
    'ESTP' => [
        'titulo' => 'O Empresário',
        'caracteristicas' => [
            'Energético e prático',
            'Espontâneo e adaptável',
            'Focado em resultados',
            'Realista e presente'
        ]
    ],
    'ESFP' => [
        'titulo' => 'O Animador',
        'caracteristicas' => [
            'Entusiasta e espontâneo',
            'Amigável e divertido',
            'Prático e adaptável',
            'Orientado para pessoas'
        ]
    ],
    'ENFP' => [
        'titulo' => 'O Inspirador',
        'caracteristicas' => [
            'Entusiasta e criativo',
            'Empático e caloroso',
            'Espontâneo e flexível',
            'Inovador e inspirador'
        ]
    ],
    'ENTP' => [
        'titulo' => 'O Inovador',
        'caracteristicas' => [
            'Inovador e estratégico',
            'Versátil e analítico',
            'Questionador e criativo',
            'Adaptável e empreendedor'
        ]
    ],
    'ESTJ' => [
        'titulo' => 'O Supervisor',
        'caracteristicas' => [
            'Organizado e eficiente',
            'Lógico e objetivo',
            'Prático e decisivo',
            'Focado em resultados'
        ]
    ],
    'ESFJ' => [
        'titulo' => 'O Provedor',
        'caracteristicas' => [
            'Amigável e cooperativo',
            'Responsável e confiável',
            'Organizado e prático',
            'Focado em harmonia'
        ]
    ],
    'ENFJ' => [
        'titulo' => 'O Professor',
        'caracteristicas' => [
            'Carismático e empático',
            'Organizado e decisivo',
            'Altruísta e confiável',
            'Focado em desenvolvimento'
        ]
    ],
    'ENTJ' => [
        'titulo' => 'O Comandante',
        'caracteristicas' => [
            'Lógico e estratégico',
            'Decisivo e confiante',
            'Eficiente e organizado',
            'Focado em liderança'
        ]
    ]
];

// Descrições das dimensões
$dimensoes = [
    'E/I' => [
        'titulo' => 'Extroversão (E) vs. Introversão (I)',
        'descricao' => 'Como você interage com o mundo e direciona sua energia'
    ],
    'S/N' => [
        'titulo' => 'Sensação (S) vs. Intuição (N)',
        'descricao' => 'Como você processa informações e percebe o mundo'
    ],
    'T/F' => [
        'titulo' => 'Pensamento (T) vs. Sentimento (F)',
        'descricao' => 'Como você toma decisões e avalia situações'
    ],
    'J/P' => [
        'titulo' => 'Julgamento (J) vs. Percepção (P)',
        'descricao' => 'Como você se organiza e lida com o mundo exterior'
    ]
];

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil MBTI - <?php echo htmlspecialchars($candidato_nome); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none;
            }
            .page-break {
                page-break-before: always;
            }
        }
        body {
            background-color: #f8f9fa;
        }
        .profile-header {
            background-color: white;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .profile-section {
            background-color: white;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .type-badge {
            font-size: 2.5rem;
            font-weight: bold;
            color: #0d6efd;
        }
        .type-name {
            font-size: 1.5rem;
            color: #6c757d;
        }
        .preference-card {
            background-color: #f8f9fa;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
        }
        .preference-score {
            font-size: 1.2em;
            font-weight: bold;
            color: #0d6efd;
        }
        .characteristic-item {
            background-color: #e9ecef;
            padding: 10px 15px;
            border-radius: 20px;
            margin: 5px;
            display: inline-block;
        }
    </style>
</head>
<body class="container py-4">
    <div class="no-print mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <a href="view_candidates_mbti.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> Imprimir / Salvar PDF
            </button>
        </div>
    </div>

    <div class="profile-header">
        <h1 class="text-center mb-4">Relatório de Perfil MBTI</h1>
        <div class="row">
            <div class="col-md-6">
                <h5>Informações do Candidato</h5>
                <p><strong>Nome:</strong> <?php echo htmlspecialchars($candidato_nome); ?></p>
                <p><strong>Cargo:</strong> <?php echo htmlspecialchars($candidato_cargo); ?></p>
                <p><strong>Data da Avaliação:</strong> <?php echo date('d/m/Y H:i', strtotime($resultado['data'])); ?></p>
                <p><strong>Solicitante:</strong> <?php echo htmlspecialchars($selecionador_nome); ?></p>
            </div>
            <div class="col-md-6 text-center">
                <h5>Tipo MBTI</h5>
                <div class="type-badge"><?php echo $tipo_mbti; ?></div>
                <div class="type-name"><?php echo $tipos_mbti[$tipo_mbti]['titulo']; ?></div>
            </div>
        </div>
    </div>

    <div class="profile-section">
        <h3 class="mb-4">Características Principais</h3>
        <div class="d-flex flex-wrap gap-2">
            <?php foreach ($tipos_mbti[$tipo_mbti]['caracteristicas'] as $caracteristica): ?>
                <span class="characteristic-item"><?php echo htmlspecialchars($caracteristica); ?></span>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="profile-section">
        <h3 class="mb-4">Análise das Dimensões</h3>
        <div class="row">
            <?php foreach ($dimensoes as $dim => $info): ?>
                <div class="col-md-6 mb-4">
                    <div class="preference-card">
                        <h5><?php echo $info['titulo']; ?></h5>
                        <p class="text-muted"><?php echo $info['descricao']; ?></p>
                        <?php
                        $pontuacao = abs($resultado['pontuacoes'][$dim]);
                        $preferencia = $resultado['pontuacoes'][$dim] > 0 ? 
                                     substr($dim, 0, 1) : 
                                     substr($dim, 2, 1);
                        ?>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><?php echo substr($dim, 0, 1); ?></span>
                                <span class="preference-score"><?php echo $pontuacao; ?>%</span>
                                <span><?php echo substr($dim, 2, 1); ?></span>
                            </div>
                            <div class="progress mt-2">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: <?php echo $pontuacao; ?>%" 
                                     aria-valuenow="<?php echo $pontuacao; ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100"></div>
                            </div>
                            <p class="mt-2 mb-0 text-center">
                                Preferência: <strong><?php echo $preferencia; ?></strong>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

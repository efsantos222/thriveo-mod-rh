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
    header('Location: view_candidates_mbti.php');
    exit;
}

// Carregar dados do candidato
$candidatos_file = 'resultados/candidatos_mbti.csv';
$candidato_nome = '';
$candidato_cargo = '';
$selecionador_nome = '';
$data_avaliacao = '';

if (($handle = fopen($candidatos_file, "r")) !== FALSE) {
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
$resultados = [];
$tipo_mbti = '';

if (($handle = fopen($avaliacao_file, "r")) !== FALSE) {
    // Pular o cabeçalho
    fgetcsv($handle);
    
    // Ler os resultados
    while (($data = fgetcsv($handle)) !== FALSE) {
        if ($data[0] === 'E-I') {
            $resultados['E-I'] = $data[1];
            $tipo_mbti .= intval($data[1]) > 0 ? 'E' : 'I';
        } elseif ($data[0] === 'S-N') {
            $resultados['S-N'] = $data[1];
            $tipo_mbti .= intval($data[1]) > 0 ? 'S' : 'N';
        } elseif ($data[0] === 'T-F') {
            $resultados['T-F'] = $data[1];
            $tipo_mbti .= intval($data[1]) > 0 ? 'T' : 'F';
        } elseif ($data[0] === 'J-P') {
            $resultados['J-P'] = $data[1];
            $tipo_mbti .= intval($data[1]) > 0 ? 'J' : 'P';
        }
    }
    fclose($handle);
}

// Descrições dos tipos MBTI
$descricoes = [
    'E' => [
        'titulo' => 'Extroversão',
        'descricao' => 'Focado no mundo exterior, pessoas e atividades. Aprende melhor fazendo, discutindo e interagindo.'
    ],
    'I' => [
        'titulo' => 'Introversão',
        'descricao' => 'Focado no mundo interior de ideias e reflexões. Aprende melhor através da reflexão e análise mental.'
    ],
    'S' => [
        'titulo' => 'Sensação',
        'descricao' => 'Focado em informações concretas e detalhes. Prefere fatos e experiências práticas.'
    ],
    'N' => [
        'titulo' => 'Intuição',
        'descricao' => 'Focado em possibilidades e significados. Valoriza imaginação e inovação.'
    ],
    'T' => [
        'titulo' => 'Pensamento',
        'descricao' => 'Toma decisões baseadas em lógica e análise objetiva. Valoriza justiça e consistência.'
    ],
    'F' => [
        'titulo' => 'Sentimento',
        'descricao' => 'Toma decisões baseadas em valores pessoais e impacto nas pessoas. Valoriza harmonia.'
    ],
    'J' => [
        'titulo' => 'Julgamento',
        'descricao' => 'Prefere um estilo de vida planejado e organizado. Gosta de estrutura e decisões definidas.'
    ],
    'P' => [
        'titulo' => 'Percepção',
        'descricao' => 'Prefere um estilo de vida flexível e adaptável. Gosta de manter opções em aberto.'
    ]
];

// Descrições dos 16 tipos MBTI
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
            'Sociável e expressivo',
            'Inovador e inspirador',
            'Versátil e otimista'
        ]
    ],
    'ENTP' => [
        'titulo' => 'O Inovador',
        'caracteristicas' => [
            'Inovador e estratégico',
            'Versátil e analítico',
            'Questionador e criativo',
            'Energético e engenhoso'
        ]
    ],
    'ESTJ' => [
        'titulo' => 'O Executivo',
        'caracteristicas' => [
            'Organizado e eficiente',
            'Lógico e objetivo',
            'Dedicado e sistemático',
            'Focado em resultados'
        ]
    ],
    'ESFJ' => [
        'titulo' => 'O Provedor',
        'caracteristicas' => [
            'Prestativo e cooperativo',
            'Social e organizado',
            'Tradicional e responsável',
            'Harmonioso e dedicado'
        ]
    ],
    'ENFJ' => [
        'titulo' => 'O Protagonista',
        'caracteristicas' => [
            'Carismático e empático',
            'Organizado e decisivo',
            'Altruísta e expressivo',
            'Focado em desenvolvimento'
        ]
    ],
    'ENTJ' => [
        'titulo' => 'O Comandante',
        'caracteristicas' => [
            'Estratégico e decisivo',
            'Eficiente e organizado',
            'Natural líder',
            'Direto e objetivo'
        ]
    ]
];

// Gerar HTML
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil MBTI - <?php echo htmlspecialchars($candidato_nome); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none;
            }
            .page-break {
                page-break-before: always;
            }
        }
        .profile-header {
            background-color: #f8f9fa;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 10px;
        }
        .profile-section {
            margin-bottom: 30px;
        }
        .type-description {
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .preference-card {
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .preference-score {
            font-size: 1.2em;
            font-weight: bold;
            color: #0d6efd;
        }
    </style>
</head>
<body class="container mt-4 mb-4">
    <div class="no-print mb-4">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer"></i> Imprimir / Salvar PDF
        </button>
        <a href="view_candidates_mbti.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="profile-header">
        <h1 class="text-center mb-4">Relatório de Perfil MBTI</h1>
        <div class="row">
            <div class="col-md-6">
                <h5>Informações do Candidato</h5>
                <p><strong>Nome:</strong> <?php echo htmlspecialchars($candidato_nome); ?></p>
                <p><strong>Cargo:</strong> <?php echo htmlspecialchars($candidato_cargo); ?></p>
                <p><strong>Data da Avaliação:</strong> <?php echo date('d/m/Y H:i', strtotime($data_avaliacao)); ?></p>
            </div>
            <div class="col-md-6">
                <h5>Tipo MBTI</h5>
                <h2 class="display-4 text-center"><?php echo $tipo_mbti; ?></h2>
                <h4 class="text-center"><?php echo $tipos_mbti[$tipo_mbti]['titulo']; ?></h4>
            </div>
        </div>
    </div>

    <div class="profile-section">
        <h3 class="mb-4">Preferências Individuais</h3>
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="preference-card">
                    <h5>Energia</h5>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Extroversão (E)</span>
                        <span class="preference-score"><?php echo abs($resultados['E-I']); ?>%</span>
                        <span>Introversão (I)</span>
                    </div>
                    <p class="mt-3">
                        <?php echo $descricoes[$tipo_mbti[0]]['descricao']; ?>
                    </p>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="preference-card">
                    <h5>Informação</h5>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Sensação (S)</span>
                        <span class="preference-score"><?php echo abs($resultados['S-N']); ?>%</span>
                        <span>Intuição (N)</span>
                    </div>
                    <p class="mt-3">
                        <?php echo $descricoes[$tipo_mbti[1]]['descricao']; ?>
                    </p>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="preference-card">
                    <h5>Decisões</h5>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Pensamento (T)</span>
                        <span class="preference-score"><?php echo abs($resultados['T-F']); ?>%</span>
                        <span>Sentimento (F)</span>
                    </div>
                    <p class="mt-3">
                        <?php echo $descricoes[$tipo_mbti[2]]['descricao']; ?>
                    </p>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="preference-card">
                    <h5>Estilo de Vida</h5>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Julgamento (J)</span>
                        <span class="preference-score"><?php echo abs($resultados['J-P']); ?>%</span>
                        <span>Percepção (P)</span>
                    </div>
                    <p class="mt-3">
                        <?php echo $descricoes[$tipo_mbti[3]]['descricao']; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="page-break"></div>

    <div class="profile-section">
        <h3 class="mb-4">Análise do Tipo <?php echo $tipo_mbti; ?> - <?php echo $tipos_mbti[$tipo_mbti]['titulo']; ?></h3>
        <div class="type-description">
            <h5>Características Principais</h5>
            <ul class="list-group list-group-flush">
                <?php foreach ($tipos_mbti[$tipo_mbti]['caracteristicas'] as $caracteristica): ?>
                    <li class="list-group-item"><?php echo htmlspecialchars($caracteristica); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

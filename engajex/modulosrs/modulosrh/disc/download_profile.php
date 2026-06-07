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
    header('Location: view_candidates.php');
    exit;
}

$email = $_GET['email'];
$avaliacao_file = 'resultados/' . str_replace(['@', '.'], '_', $email) . '_avaliacao.csv';
$grafico_file = 'resultados/' . str_replace(['@', '.'], '_', $email) . '_grafico.png';

if (!file_exists($avaliacao_file)) {
    header('Location: view_candidates.php');
    exit;
}

// Carregar dados do candidato
$candidatos_file = 'resultados/candidatos.csv';
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
if (($handle = fopen($avaliacao_file, "r")) !== FALSE) {
    // Pular o cabeçalho
    fgetcsv($handle);
    
    // Ler os resultados
    while (($data = fgetcsv($handle)) !== FALSE) {
        if ($data[0] === 'Perfil D') {
            $resultados['D'] = intval($data[1]);
        } elseif ($data[0] === 'Perfil I') {
            $resultados['I'] = intval($data[1]);
        } elseif ($data[0] === 'Perfil S') {
            $resultados['S'] = intval($data[1]);
        } elseif ($data[0] === 'Perfil C') {
            $resultados['C'] = intval($data[1]);
        } elseif ($data[0] === 'Perfil Predominante') {
            $perfil_predominante = $data[1];
        }
    }
    fclose($handle);
}

// Descrições dos perfis
$descricoes = [
    'D' => [
        'titulo' => 'Dominante',
        'caracteristicas' => [
            'Direto e decisivo',
            'Focado em resultados',
            'Competitivo e determinado',
            'Gosta de desafios',
            'Assume riscos calculados'
        ],
        'pontos_fortes' => [
            'Liderança natural',
            'Capacidade de tomar decisões rápidas',
            'Orientação para objetivos',
            'Iniciativa e proatividade',
            'Habilidade para resolver problemas'
        ],
        'areas_desenvolvimento' => [
            'Pode ser percebido como muito direto ou agressivo',
            'Pode ter dificuldade em demonstrar empatia',
            'Pode ser impaciente com processos lentos',
            'Pode ter dificuldade em delegar',
            'Pode ignorar detalhes importantes'
        ]
    ],
    'I' => [
        'titulo' => 'Influente',
        'caracteristicas' => [
            'Comunicativo e expressivo',
            'Entusiasta e otimista',
            'Sociável e carismático',
            'Persuasivo',
            'Motivador'
        ],
        'pontos_fortes' => [
            'Excelente comunicação',
            'Habilidade de networking',
            'Criatividade',
            'Capacidade de inspirar outros',
            'Adaptabilidade social'
        ],
        'areas_desenvolvimento' => [
            'Pode ser muito falante',
            'Pode ter dificuldade com detalhes',
            'Pode ser desorganizado',
            'Pode ter dificuldade em focar',
            'Pode tomar decisões baseadas em emoções'
        ]
    ],
    'S' => [
        'titulo' => 'Estável',
        'caracteristicas' => [
            'Paciente e consistente',
            'Cooperativo e prestativo',
            'Calmo e estável',
            'Bom ouvinte',
            'Leal e confiável'
        ],
        'pontos_fortes' => [
            'Trabalho em equipe',
            'Confiabilidade',
            'Paciência',
            'Capacidade de mediação',
            'Estabilidade emocional'
        ],
        'areas_desenvolvimento' => [
            'Pode resistir a mudanças',
            'Pode ser muito passivo',
            'Pode ter dificuldade em expressar opiniões',
            'Pode evitar conflitos necessários',
            'Pode demorar para tomar decisões'
        ]
    ],
    'C' => [
        'titulo' => 'Conforme',
        'caracteristicas' => [
            'Analítico e preciso',
            'Sistemático e organizado',
            'Detalhista',
            'Cauteloso',
            'Diplomático'
        ],
        'pontos_fortes' => [
            'Atenção aos detalhes',
            'Organização',
            'Pensamento lógico',
            'Qualidade no trabalho',
            'Habilidades analíticas'
        ],
        'areas_desenvolvimento' => [
            'Pode ser muito crítico',
            'Pode ser perfeccionista',
            'Pode ter dificuldade com prazos',
            'Pode ser muito cauteloso',
            'Pode ter dificuldade em lidar com mudanças'
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
    <title>Perfil DISC - <?php echo htmlspecialchars($candidato_nome); ?></title>
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
        .chart-container {
            text-align: center;
            margin: 30px 0;
        }
        .chart-container img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body class="container mt-4 mb-4">
    <div class="no-print mb-4">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer"></i> Imprimir / Salvar PDF
        </button>
        <div class="mt-4 text-center">
            <?php
            $return_url = isset($_SESSION['superadmin_authenticated']) ? 'superadmin_panel.php#candidatos-disc' : 'view_candidates.php';
            ?>
            <a href="<?php echo $return_url; ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <div class="profile-header">
        <h1 class="text-center mb-4">Relatório de Perfil DISC</h1>
        <div class="row">
            <div class="col-md-6">
                <h5>Informações do Candidato</h5>
                <p><strong>Nome:</strong> <?php echo htmlspecialchars($candidato_nome); ?></p>
                <p><strong>Cargo:</strong> <?php echo htmlspecialchars($candidato_cargo); ?></p>
                <p><strong>Data da Avaliação:</strong> <?php echo date('d/m/Y H:i', strtotime($data_avaliacao)); ?></p>
            </div>
            <div class="col-md-6">
                <h5>Resultados DISC</h5>
                <p><strong>D (Dominância):</strong> <?php echo $resultados['D']; ?>%</p>
                <p><strong>I (Influência):</strong> <?php echo $resultados['I']; ?>%</p>
                <p><strong>S (Estabilidade):</strong> <?php echo $resultados['S']; ?>%</p>
                <p><strong>C (Conformidade):</strong> <?php echo $resultados['C']; ?>%</p>
                <p><strong>Perfil Predominante:</strong> <?php echo $perfil_predominante; ?></p>
            </div>
        </div>
    </div>

    <?php if (file_exists($grafico_file)): ?>
    <div class="chart-container">
        <img src="<?php echo htmlspecialchars($grafico_file); ?>" alt="Gráfico DISC">
    </div>
    <?php endif; ?>

    <div class="page-break"></div>

    <?php
    $perfil_letra = substr($perfil_predominante, 0, 1);
    if (isset($descricoes[$perfil_letra])):
        $perfil = $descricoes[$perfil_letra];
    ?>
    <div class="profile-section">
        <h2 class="mb-4">Análise Detalhada do Perfil <?php echo htmlspecialchars($perfil['titulo']); ?></h2>
        
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0">Características Principais</h4>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <?php foreach ($perfil['caracteristicas'] as $carac): ?>
                        <li class="list-group-item"><?php echo htmlspecialchars($carac); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0">Pontos Fortes</h4>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <?php foreach ($perfil['pontos_fortes'] as $ponto): ?>
                        <li class="list-group-item"><?php echo htmlspecialchars($ponto); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Áreas de Desenvolvimento</h4>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <?php foreach ($perfil['areas_desenvolvimento'] as $area): ?>
                        <li class="list-group-item"><?php echo htmlspecialchars($area); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

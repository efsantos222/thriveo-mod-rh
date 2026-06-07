<?php
session_start();

// Verificar se está logado como admin ou superadmin
if (!isset($_SESSION['admin_authenticated']) && !isset($_SESSION['superadmin_authenticated'])) {
    header('Location: index.php');
    exit;
}

if (!isset($_GET['email'])) {
    header('Location: view_candidates_bigfive.php');
    exit;
}

$email = $_GET['email'];
$candidatos_file = 'resultados/candidatos_bigfive.csv';
$avaliacao_file = 'resultados/' . str_replace(['@', '.'], '_', $email) . '_avaliacao_bigfive.csv';

// Verificar se os arquivos existem
if (!file_exists($candidatos_file) || !file_exists($avaliacao_file)) {
    die("Arquivo não encontrado.");
}

// Carregar dados do candidato
$candidato = null;
$fp = fopen($candidatos_file, 'r');
fgetcsv($fp); // Pular cabeçalho
while (($data = fgetcsv($fp)) !== FALSE) {
    if ($data[4] === $email) {
        $candidato = [
            'data' => $data[0],
            'selecionador' => $data[1],
            'nome' => $data[3],
            'email' => $data[4],
            'empresa' => $data[5],
            'cargo' => $data[6]
        ];
        break;
    }
}
fclose($fp);

if (!$candidato) {
    die("Candidato não encontrado.");
}

// Carregar respostas
$respostas = [];
$fp = fopen($avaliacao_file, 'r');
fgetcsv($fp); // Pular cabeçalho
while (($data = fgetcsv($fp)) !== FALSE) {
    $respostas[] = [
        'questao' => $data[0],
        'resposta' => $data[1],
        'dimensao' => $data[2],
        'inverso' => $data[3]
    ];
}
fclose($fp);

// Calcular pontuações por dimensão
$dimensoes = [
    'Abertura' => ['pontos' => 0, 'count' => 0],
    'Conscienciosidade' => ['pontos' => 0, 'count' => 0],
    'Extroversao' => ['pontos' => 0, 'count' => 0],
    'Amabilidade' => ['pontos' => 0, 'count' => 0],
    'Neuroticismo' => ['pontos' => 0, 'count' => 0]
];

foreach ($respostas as $resposta) {
    if ($resposta['dimensao'] !== 'Extra') {
        $valor = (int)$resposta['resposta'];
        if ($resposta['inverso'] === '1') {
            $valor = 6 - $valor; // Inverter a escala (1->5, 2->4, etc.)
        }
        $dimensoes[$resposta['dimensao']]['pontos'] += $valor;
        $dimensoes[$resposta['dimensao']]['count']++;
    }
}

// Calcular médias
foreach ($dimensoes as &$dimensao) {
    $dimensao['media'] = $dimensao['count'] > 0 ? 
        round($dimensao['pontos'] / $dimensao['count'], 2) : 0;
}

// Função para interpretar a pontuação
function interpretarPontuacao($dimensao, $media) {
    $interpretacoes = [
        'Abertura' => [
            'baixo' => 'Tende a ser mais tradicional e prefere rotinas estabelecidas.',
            'medio' => 'Equilibra tradição com novas experiências.',
            'alto' => 'Muito criativo(a) e aberto(a) a novas experiências e ideias.'
        ],
        'Conscienciosidade' => [
            'baixo' => 'Tende a ser mais flexível e espontâneo(a).',
            'medio' => 'Mantém um equilíbrio entre organização e flexibilidade.',
            'alto' => 'Muito organizado(a) e focado(a) em objetivos.'
        ],
        'Extroversao' => [
            'baixo' => 'Tende a ser mais reservado(a) e reflexivo(a).',
            'medio' => 'Equilibra momentos sociais com momentos de introspecção.',
            'alto' => 'Muito sociável e energizado(a) por interações sociais.'
        ],
        'Amabilidade' => [
            'baixo' => 'Tende a ser mais direto(a) e objetivo(a) nas relações.',
            'medio' => 'Equilibra cooperação com assertividade.',
            'alto' => 'Muito cooperativo(a) e focado(a) no bem-estar dos outros.'
        ],
        'Neuroticismo' => [
            'baixo' => 'Tende a ser mais estável emocionalmente e resiliente.',
            'medio' => 'Apresenta reações emocionais moderadas.',
            'alto' => 'Mais sensível a estresse e mudanças emocionais.'
        ]
    ];

    if ($media < 2.5) return $interpretacoes[$dimensao]['baixo'];
    if ($media < 3.5) return $interpretacoes[$dimensao]['medio'];
    return $interpretacoes[$dimensao]['alto'];
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil Big Five - <?php echo htmlspecialchars($candidato['nome']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .profile-header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .dimension-card {
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .score-bar {
            height: 20px;
            border-radius: 10px;
            background-color: #e9ecef;
            margin: 10px 0;
            overflow: hidden;
        }
        .score-fill {
            height: 100%;
            background-color: #0d6efd;
            transition: width 0.5s ease;
        }
        @media print {
            .no-print {
                display: none;
            }
            .container {
                width: 100%;
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="no-print mb-4">
            <a href="<?php echo isset($_SESSION['superadmin_authenticated']) ? 'superadmin_panel.php#candidatos-bigfive' : 'view_candidates_bigfive.php'; ?>" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
            <button onclick="window.print()" class="btn btn-primary ms-2">
                <i class="bi bi-printer"></i> Imprimir
            </button>
        </div>

        <div class="profile-header">
            <div class="row">
                <div class="col-md-6">
                    <h2><?php echo htmlspecialchars($candidato['nome']); ?></h2>
                    <p class="text-muted mb-0">
                        <?php echo htmlspecialchars($candidato['cargo']); ?> - 
                        <?php echo htmlspecialchars($candidato['empresa']); ?>
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">
                        <strong>Data da Avaliação:</strong><br>
                        <?php echo date('d/m/Y H:i', strtotime($candidato['data'])); ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="row">
            <?php foreach ($dimensoes as $nome => $dados): ?>
            <div class="col-md-6">
                <div class="card dimension-card">
                    <div class="card-body">
                        <h4 class="card-title"><?php echo htmlspecialchars($nome); ?></h4>
                        <div class="score-bar">
                            <div class="score-fill" style="width: <?php echo ($dados['media'] / 5) * 100; ?>%"></div>
                        </div>
                        <p class="text-center mb-2">
                            <strong>Pontuação: <?php echo $dados['media']; ?> / 5</strong>
                        </p>
                        <p class="card-text">
                            <?php echo interpretarPontuacao($nome, $dados['media']); ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <h4>Interpretação Geral</h4>
                <p>Este relatório apresenta os resultados do teste Big Five Personality Traits, que avalia cinco dimensões principais da personalidade:</p>
                <ul>
                    <li><strong>Abertura à Experiência:</strong> Criatividade, curiosidade e abertura a novas ideias</li>
                    <li><strong>Conscienciosidade:</strong> Organização, responsabilidade e foco em objetivos</li>
                    <li><strong>Extroversão:</strong> Sociabilidade, assertividade e energia</li>
                    <li><strong>Amabilidade:</strong> Cooperação, empatia e consideração pelos outros</li>
                    <li><strong>Neuroticismo:</strong> Estabilidade emocional e resposta ao estresse</li>
                </ul>
                <p class="mb-0">As pontuações representam tendências comportamentais e não devem ser interpretadas como limitações absolutas.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

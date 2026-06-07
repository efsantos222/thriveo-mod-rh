<?php
session_start();

// Verificar se é admin ou superadmin
if ((!isset($_SESSION['admin_authenticated']) || !$_SESSION['admin_authenticated']) && 
    (!isset($_SESSION['superadmin_authenticated']) || !$_SESSION['superadmin_authenticated'])) {
    header('Location: ../admin_login.php');
    exit;
}

// Verificar se foi fornecido um email
$email = isset($_GET['email']) ? $_GET['email'] : null;
if (!$email) {
    header('Location: ../view_candidates_mbti.php');
    exit;
}

// Carregar resultados
$results_file = '../resultados/' . str_replace(['@', '.'], '_', $email) . '_avaliacao_mbti.csv';
if (!file_exists($results_file)) {
    header('Location: ../view_candidates_mbti.php?error=no_results');
    exit;
}

// Carregar dados do candidato
$candidato_file = '../resultados/candidatos_mbti.csv';
$nome_candidato = '';
$cargo_candidato = '';

if (file_exists($candidato_file)) {
    $fp = fopen($candidato_file, 'r');
    fgetcsv($fp); // Pular cabeçalho
    while (($data = fgetcsv($fp)) !== FALSE) {
        if ($data[4] === $email) {
            $nome_candidato = $data[3];
            $cargo_candidato = $data[6];
            break;
        }
    }
    fclose($fp);
}

// Carregar resultado do teste
$fp = fopen($results_file, 'r');
fgetcsv($fp); // Pular cabeçalho
$data = fgetcsv($fp);
fclose($fp);

$data_teste = $data[0];
$mbti_type = $data[1];

// Definições dos tipos MBTI
$mbti_descriptions = [
    'ISTJ' => [
        'name' => 'O Inspetor',
        'description' => 'Prático, responsável e organizado. Valoriza tradições e lealdade.',
        'strengths' => ['Confiável', 'Prático', 'Organizado', 'Comprometido', 'Honesto'],
        'workplace' => 'Prefere ambientes estruturados com regras e procedimentos claros.'
    ],
    'ISFJ' => [
        'name' => 'O Protetor',
        'description' => 'Dedicado, caloroso e protetor. Gosta de servir e proteger os outros.',
        'strengths' => ['Leal', 'Paciente', 'Detalhista', 'Dedicado', 'Prestativo'],
        'workplace' => 'Trabalha bem em ambientes que valorizam a estabilidade e o cuidado com as pessoas.'
    ],
    'INFJ' => [
        'name' => 'O Conselheiro',
        'description' => 'Idealista, organizado e insightful. Busca significado e conexão.',
        'strengths' => ['Criativo', 'Dedicado', 'Determinado', 'Altruísta', 'Compassivo'],
        'workplace' => 'Prospera em ambientes que permitem criatividade e foco no desenvolvimento humano.'
    ],
    'INTJ' => [
        'name' => 'O Arquiteto',
        'description' => 'Inovador, independente e estratégico. Guiado por suas próprias ideias e visões.',
        'strengths' => ['Estratégico', 'Independente', 'Inovador', 'Analítico', 'Determinado'],
        'workplace' => 'Se destaca em ambientes que valorizam inovação e pensamento estratégico.'
    ],
    'ISTP' => [
        'name' => 'O Artesão',
        'description' => 'Tolerante e flexível, observador quieto até que um problema apareça.',
        'strengths' => ['Adaptável', 'Prático', 'Observador', 'Lógico', 'Espontâneo'],
        'workplace' => 'Prefere ambientes que oferecem liberdade para resolver problemas práticos.'
    ],
    'ISFP' => [
        'name' => 'O Artista',
        'description' => 'Amigável, sensível e gentil. Gosta de explorar e experimentar.',
        'strengths' => ['Criativo', 'Sensível', 'Gentil', 'Adaptável', 'Observador'],
        'workplace' => 'Se sente bem em ambientes que permitem expressão pessoal e criatividade.'
    ],
    'INFP' => [
        'name' => 'O Mediador',
        'description' => 'Idealista, curioso e adaptável. Leal aos seus valores e pessoas importantes.',
        'strengths' => ['Empático', 'Criativo', 'Apaixonado', 'Dedicado', 'Curioso'],
        'workplace' => 'Prospera em ambientes que alinham com seus valores pessoais.'
    ],
    'INTP' => [
        'name' => 'O Lógico',
        'description' => 'Inovador, inventivo e curioso. Busca encontrar soluções lógicas.',
        'strengths' => ['Analítico', 'Original', 'Objetivo', 'Curioso', 'Versátil'],
        'workplace' => 'Se destaca em ambientes que valorizam soluções inovadoras e análise lógica.'
    ],
    'ESTP' => [
        'name' => 'O Empreendedor',
        'description' => 'Energético, ativo e perspicaz. Gosta de resolver problemas práticos.',
        'strengths' => ['Energético', 'Prático', 'Adaptável', 'Persuasivo', 'Observador'],
        'workplace' => 'Prefere ambientes dinâmicos com desafios práticos.'
    ],
    'ESFP' => [
        'name' => 'O Animador',
        'description' => 'Espontâneo, energético e entusiasta. Gosta de trabalhar com outros.',
        'strengths' => ['Entusiasta', 'Amigável', 'Adaptável', 'Prático', 'Observador'],
        'workplace' => 'Se sente bem em ambientes sociais e colaborativos.'
    ],
    'ENFP' => [
        'name' => 'O Inspirador',
        'description' => 'Caloroso, entusiasta e imaginativo. Vê possibilidades em tudo.',
        'strengths' => ['Criativo', 'Entusiasta', 'Flexível', 'Amigável', 'Comunicativo'],
        'workplace' => 'Prospera em ambientes que permitem inovação e interação social.'
    ],
    'ENTP' => [
        'name' => 'O Inovador',
        'description' => 'Rápido, engenhoso e estimulante. Gosta de novos desafios.',
        'strengths' => ['Inovador', 'Criativo', 'Adaptável', 'Dinâmico', 'Analítico'],
        'workplace' => 'Se destaca em ambientes que valorizam novas ideias e soluções criativas.'
    ],
    'ESTJ' => [
        'name' => 'O Executivo',
        'description' => 'Prático, realista e decidido. Focado em conseguir resultados.',
        'strengths' => ['Organizado', 'Leal', 'Dedicado', 'Prático', 'Sistemático'],
        'workplace' => 'Prefere ambientes estruturados com objetivos claros.'
    ],
    'ESFJ' => [
        'name' => 'O Provedor',
        'description' => 'Caloroso, consciente e cooperativo. Gosta de trabalhar com outros.',
        'strengths' => ['Cooperativo', 'Leal', 'Tradicional', 'Responsável', 'Prestativo'],
        'workplace' => 'Se sente bem em ambientes harmoniosos e colaborativos.'
    ],
    'ENFJ' => [
        'name' => 'O Professor',
        'description' => 'Caloroso, empático e responsável. Altamente sintonizado com outros.',
        'strengths' => ['Carismático', 'Empático', 'Organizado', 'Responsável', 'Altruísta'],
        'workplace' => 'Prospera em ambientes que valorizam desenvolvimento pessoal e trabalho em equipe.'
    ],
    'ENTJ' => [
        'name' => 'O Comandante',
        'description' => 'Franco, decisivo e assumindo liderança rapidamente.',
        'strengths' => ['Confiante', 'Decisivo', 'Eficiente', 'Estratégico', 'Determinado'],
        'workplace' => 'Se destaca em ambientes que valorizam liderança e realização de objetivos.'
    ]
];

$type_info = $mbti_descriptions[$mbti_type];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado MBTI - <?php echo htmlspecialchars($nome_candidato); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
            padding: 20px;
        }
        .result-card {
            max-width: 800px;
            margin: 0 auto;
        }
        .card {
            border: none;
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
        .strength-item {
            background-color: #e9ecef;
            padding: 10px 15px;
            border-radius: 20px;
            margin: 5px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="result-card">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0">Resultado MBTI</h4>
                        <small>
                            <?php echo htmlspecialchars($nome_candidato); ?> 
                            <?php if (!empty($cargo_candidato)): ?>
                                - <?php echo htmlspecialchars($cargo_candidato); ?>
                            <?php endif; ?>
                        </small>
                    </div>
                    <div>
                        <a href="../download_profile_mbti.php?email=<?php echo urlencode($email); ?>" class="btn btn-light btn-sm">
                            <i class="bi bi-file-pdf"></i> Baixar PDF
                        </a>
                        <a href="../view_candidates_mbti.php" class="btn btn-light btn-sm ms-2">
                            <i class="bi bi-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="type-badge mb-2"><?php echo $mbti_type; ?></div>
                        <div class="type-name mb-3"><?php echo $type_info['name']; ?></div>
                        <p class="lead"><?php echo $type_info['description']; ?></p>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="bi bi-star"></i> Pontos Fortes
                                    </h5>
                                    <div class="mt-3">
                                        <?php foreach ($type_info['strengths'] as $strength): ?>
                                            <span class="strength-item"><?php echo $strength; ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="bi bi-building"></i> No Ambiente de Trabalho
                                    </h5>
                                    <p class="mt-3"><?php echo $type_info['workplace']; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <p class="text-muted">
                            <i class="bi bi-calendar"></i> 
                            Teste realizado em: <?php echo date('d/m/Y H:i', strtotime($data_teste)); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

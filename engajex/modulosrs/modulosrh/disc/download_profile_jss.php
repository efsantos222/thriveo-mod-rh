<?php
session_start();

// Verificar se é superadmin
if (!isset($_SESSION['superadmin_authenticated']) || !$_SESSION['superadmin_authenticated']) {
    header('Location: superadmin_login.php');
    exit;
}

// Verificar se o email foi fornecido
if (!isset($_GET['email'])) {
    die('Email não fornecido');
}

$email = $_GET['email'];

// Log para debug
$log_file = 'debug_jss.log';
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Buscando resultados para email: $email\n", FILE_APPEND);

// Carregar resultados do JSS
$email_formatado = str_replace(['@', '.'], '_', $email);
$resultados_file = "resultados/{$email_formatado}_avaliacao_jss.csv";

if (!file_exists($resultados_file)) {
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Arquivo de resultados não existe\n", FILE_APPEND);
    die('Resultados não encontrados para este candidato');
}

file_put_contents($log_file, date('Y-m-d H:i:s') . " - Arquivo de resultados existe\n", FILE_APPEND);
$conteudo = file_get_contents($resultados_file);
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Conteúdo do arquivo:\n$conteudo\n", FILE_APPEND);

$fp = fopen($resultados_file, 'r');
if ($fp === false) {
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Erro ao abrir arquivo de resultados\n", FILE_APPEND);
    die('Erro ao ler resultados');
}

$header = fgetcsv($fp); // Pular cabeçalho (ID, Frequência, Gravidade)
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Cabeçalho: " . implode(',', $header) . "\n", FILE_APPEND);

$frequencia_total = 0;
$severidade_total = 0;
$dados = [];

// Ler até encontrar a linha TOTAL
while (($data = fgetcsv($fp)) !== FALSE) {
    if ($data[0] === 'TOTAL') {
        $frequencia_total = $data[1];
        $severidade_total = $data[2];
        break;
    }
    $dados[] = $data;
}

fclose($fp);

// Carregar informações do candidato
$candidatos_file = "resultados/candidatos_jss.csv";

if (!file_exists($candidatos_file)) {
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Arquivo de candidatos não existe\n", FILE_APPEND);
    die('Arquivo de candidatos não encontrado');
}

file_put_contents($log_file, date('Y-m-d H:i:s') . " - Arquivo de candidatos existe\n", FILE_APPEND);
$conteudo = file_get_contents($candidatos_file);
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Conteúdo do arquivo de candidatos:\n$conteudo\n", FILE_APPEND);

$fp = fopen($candidatos_file, 'r');
$header_candidatos = fgetcsv($fp);
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Cabeçalho candidatos: " . implode(',', $header_candidatos) . "\n", FILE_APPEND);

$nome_candidato = '';
$empresa = '';
$cargo = '';
$data_avaliacao = '';
$selecionador_nome = '';

while (($data = fgetcsv($fp)) !== FALSE) {
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Verificando candidato: " . implode(',', $data) . "\n", FILE_APPEND);
    if ($data[4] === $email) { // Email está na coluna 4
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Encontrou candidato!\n", FILE_APPEND);
        $nome_candidato = $data[3]; // Nome está na coluna 3
        $empresa = $data[6]; // Empresa está na coluna 6
        $cargo = $data[7]; // Cargo está na coluna 7
        $data_avaliacao = $data[0]; // Data está na coluna 0
        $selecionador_nome = $data[1]; // Selecionador está na coluna 1
        break;
    }
}
fclose($fp);

// Função para interpretar pontuações
function interpret_score($score) {
    if ($score >= 0 && $score <= 39) return "Baixo";
    if ($score >= 40 && $score <= 59) return "Médio";
    if ($score >= 60) return "Alto";
    return "Indefinido";
}

// Função para obter a classe Bootstrap baseada no nível
function get_level_class($level) {
    switch ($level) {
        case 'Baixo':
            return 'success';
        case 'Médio':
            return 'warning';
        case 'Alto':
            return 'danger';
        default:
            return 'secondary';
    }
}

$pontuacao_composta = $frequencia_total * $severidade_total;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório JSS - <?php echo htmlspecialchars($nome_candidato); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-before: always;
            }
            body {
                padding: 0 !important;
            }
            .container {
                max-width: 100% !important;
                width: 100% !important;
            }
        }
        .table th {
            background-color: #f8f9fa !important;
        }
    </style>
</head>
<body class="container py-4">
    <div class="no-print mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <a href="superadmin_panel.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> Imprimir
            </button>
        </div>
    </div>

    <div class="text-center mb-4">
        <h1 class="mb-3">Relatório de Estresse no Trabalho (JSS)</h1>
        <p class="text-muted">Job Stress Survey</p>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h2 class="h5 mb-0">Informações do Candidato</h2>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Nome:</strong> <?php echo htmlspecialchars($nome_candidato); ?></p>
                    <p><strong>Empresa:</strong> <?php echo htmlspecialchars($empresa); ?></p>
                    <p><strong>Cargo:</strong> <?php echo htmlspecialchars($cargo); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Data da Avaliação:</strong> <?php echo htmlspecialchars($data_avaliacao); ?></p>
                    <p><strong>Avaliador:</strong> <?php echo htmlspecialchars($selecionador_nome); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h2 class="h5 mb-0">Resultados da Avaliação</h2>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Métrica</th>
                        <th class="text-center">Pontuação</th>
                        <th class="text-center">Nível</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $metricas = [
                        ['Frequência', $frequencia_total],
                        ['Gravidade', $severidade_total],
                        ['Pontuação Composta', $pontuacao_composta]
                    ];
                    
                    foreach ($metricas as $metrica) {
                        $nivel = interpret_score($metrica[1]);
                        $classe = get_level_class($nivel);
                        echo "<tr>";
                        echo "<td>{$metrica[0]}</td>";
                        echo "<td class='text-center'>{$metrica[1]}</td>";
                        echo "<td class='text-center'><span class='badge bg-{$classe}'>{$nivel}</span></td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="h5 mb-0">Detalhamento por Item</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th class="text-center">Item</th>
                            <th>Frequência</th>
                            <th>Gravidade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dados as $item): ?>
                        <tr>
                            <td class="text-center"><?php echo htmlspecialchars($item[0]); ?></td>
                            <td><?php echo htmlspecialchars($item[1]); ?></td>
                            <td><?php echo htmlspecialchars($item[2]); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

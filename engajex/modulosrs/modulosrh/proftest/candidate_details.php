<?php
session_start();

// Verificar se está logado como selecionador
if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
    header('Location: admin_login.php');
    exit;
}

// Verificar se o email do candidato foi fornecido
if (!isset($_GET['email'])) {
    header('Location: view_results.php');
    exit;
}

$candidato_email = $_GET['email'];

// Função para ler o CSV de avaliação
function readAvaliacaoCSV($filename) {
    $resultado = [];
    if (file_exists($filename)) {
        $fp = fopen($filename, 'r');
        // Pular cabeçalho
        fgetcsv($fp);
        while (($data = fgetcsv($fp)) !== FALSE) {
            $resultado[$data[0]] = $data[1];
        }
        fclose($fp);
    }
    return $resultado;
}

// Buscar dados do candidato
$candidato = null;
$candidatos_file = 'resultados/candidatos.csv';

if (file_exists($candidatos_file)) {
    $fp = fopen($candidatos_file, 'r');
    fgetcsv($fp); // Pular cabeçalho
    
    while (($data = fgetcsv($fp)) !== FALSE) {
        if ($data[4] === $candidato_email && $data[2] === $_SESSION['admin_email']) {
            $avaliacao_file = 'resultados/' . str_replace(['@', '.'], '_', $candidato_email) . '_avaliacao.csv';
            $grafico_file = 'resultados/' . str_replace(['@', '.'], '_', $candidato_email) . '_grafico.png';
            
            if (file_exists($avaliacao_file)) {
                $resultado = readAvaliacaoCSV($avaliacao_file);
                $candidato = [
                    'data' => $data[0],
                    'nome' => $data[3],
                    'email' => $candidato_email,
                    'resultado' => $resultado,
                    'grafico' => file_exists($grafico_file) ? $grafico_file : null,
                    'avaliacao_file' => $avaliacao_file
                ];
            }
            break;
        }
    }
    fclose($fp);
}

if (!$candidato) {
    header('Location: view_results.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Candidato - <?php echo htmlspecialchars($candidato['nome']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { padding: 20px; }
        .profile-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .profile-graph {
            max-width: 100%;
            height: auto;
        }
        .disc-scores {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
        }
        .disc-score {
            text-align: center;
            padding: 10px;
            border-radius: 5px;
            flex: 1;
            margin: 0 5px;
        }
        .score-D { background-color: rgba(255, 99, 71, 0.2); }
        .score-I { background-color: rgba(255, 215, 0, 0.2); }
        .score-S { background-color: rgba(34, 139, 34, 0.2); }
        .score-C { background-color: rgba(0, 0, 255, 0.2); }
        .download-section {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Detalhes do Candidato</h2>
            <div>
                <a href="view_results.php" class="btn btn-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
                <a href="logout.php" class="btn btn-outline-secondary">Sair</a>
            </div>
        </div>

        <div class="profile-card">
            <div class="row">
                <div class="col-md-6">
                    <h3><?php echo htmlspecialchars($candidato['nome']); ?></h3>
                    <p class="text-muted">
                        <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($candidato['email']); ?><br>
                        <i class="bi bi-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($candidato['data'])); ?>
                    </p>
                    
                    <div class="disc-scores">
                        <div class="disc-score score-D">
                            <h4>D</h4>
                            <strong><?php echo $candidato['resultado']['Perfil D']; ?>%</strong>
                        </div>
                        <div class="disc-score score-I">
                            <h4>I</h4>
                            <strong><?php echo $candidato['resultado']['Perfil I']; ?>%</strong>
                        </div>
                        <div class="disc-score score-S">
                            <h4>S</h4>
                            <strong><?php echo $candidato['resultado']['Perfil S']; ?>%</strong>
                        </div>
                        <div class="disc-score score-C">
                            <h4>C</h4>
                            <strong><?php echo $candidato['resultado']['Perfil C']; ?>%</strong>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h5>Perfil Predominante: <?php echo $candidato['resultado']['Perfil Predominante']; ?></h5>
                        <p class="mb-4"><?php echo $candidato['resultado']['Recomendação']; ?></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <?php if ($candidato['grafico']): ?>
                        <img src="<?php echo htmlspecialchars($candidato['grafico']); ?>" 
                             alt="Gráfico DISC" 
                             class="profile-graph">
                    <?php endif; ?>
                </div>
            </div>

            <div class="download-section">
                <h4 class="mb-3">Downloads Disponíveis</h4>
                <div class="row">
                    <div class="col-md-4">
                        <a href="<?php echo htmlspecialchars($candidato['avaliacao_file']); ?>" 
                           class="btn btn-primary w-100 mb-2" 
                           download>
                            <i class="bi bi-file-earmark-text"></i> Baixar Avaliação Completa
                        </a>
                    </div>
                    <?php if ($candidato['grafico']): ?>
                    <div class="col-md-4">
                        <a href="<?php echo htmlspecialchars($candidato['grafico']); ?>" 
                           class="btn btn-success w-100 mb-2" 
                           download>
                            <i class="bi bi-graph-up"></i> Baixar Gráfico
                        </a>
                    </div>
                    <?php endif; ?>
                    <div class="col-md-4">
                        <a href="download_profile.php?email=<?php echo urlencode($candidato['email']); ?>" 
                           class="btn btn-info w-100 mb-2">
                            <i class="bi bi-file-pdf"></i> Baixar Perfil Detalhado
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

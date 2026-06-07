<?php
session_start();

// Verificar se está logado como selecionador
if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
    header('Location: admin_login.php');
    exit;
}

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

// Buscar candidatos do selecionador
$candidatos = [];
$candidatos_file = 'resultados/candidatos.csv';

if (file_exists($candidatos_file)) {
    $fp = fopen($candidatos_file, 'r');
    fgetcsv($fp); // Pular cabeçalho
    
    while (($data = fgetcsv($fp)) !== FALSE) {
        if ($data[2] === $_SESSION['admin_email']) {
            $candidato_email = $data[4];
            $avaliacao_file = 'resultados/' . str_replace(['@', '.'], '_', $candidato_email) . '_avaliacao.csv';
            $grafico_file = 'resultados/' . str_replace(['@', '.'], '_', $candidato_email) . '_grafico.png';
            
            if (file_exists($avaliacao_file)) {
                $resultado = readAvaliacaoCSV($avaliacao_file);
                $candidatos[] = [
                    'data' => $data[0],
                    'nome' => $data[3],
                    'email' => $candidato_email,
                    'resultado' => $resultado,
                    'grafico' => file_exists($grafico_file) ? $grafico_file : null,
                    'avaliacao_file' => $avaliacao_file
                ];
            }
        }
    }
    fclose($fp);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados das Avaliações - Sistema DISC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { padding: 20px; }
        .profile-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
        .download-links {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Resultados das Avaliações DISC</h2>
            <div>
                <span class="me-3">Olá, <?php echo htmlspecialchars($_SESSION['admin_nome']); ?></span>
                <a href="view_candidates.php" class="btn btn-primary me-2">Gerenciar Candidatos</a>
                <a href="logout.php" class="btn btn-secondary">Sair</a>
            </div>
        </div>

        <?php if (empty($candidatos)): ?>
            <div class="alert alert-info">
                Nenhum candidato completou a avaliação ainda.
            </div>
        <?php else: ?>
            <div class="row">
            <?php foreach ($candidatos as $candidato): ?>
                <div class="col-md-6 mb-4">
                    <div class="profile-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h3><?php echo htmlspecialchars($candidato['nome']); ?></h3>
                                <p class="text-muted">
                                    <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($candidato['email']); ?><br>
                                    <i class="bi bi-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($candidato['data'])); ?>
                                </p>
                            </div>
                            <a href="candidate_details.php?email=<?php echo urlencode($candidato['email']); ?>" 
                               class="btn btn-outline-primary">
                                <i class="bi bi-eye"></i> Ver Detalhes
                            </a>
                        </div>
                        
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
                        
                        <h5>Perfil Predominante: <?php echo $candidato['resultado']['Perfil Predominante']; ?></h5>
                        
                        <div class="download-links">
                            <div class="btn-group w-100">
                                <a href="view_csv.php?file=<?php echo urlencode(basename($candidato['avaliacao_file'])); ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-file-earmark-text"></i> Ver Avaliação
                                </a>
                                <?php if ($candidato['grafico']): ?>
                                <a href="<?php echo htmlspecialchars($candidato['grafico']); ?>" 
                                   class="btn btn-sm btn-outline-success" 
                                   download>
                                    <i class="bi bi-graph-up"></i> Gráfico
                                </a>
                                <?php endif; ?>
                                <a href="download_profile.php?email=<?php echo urlencode($candidato['email']); ?>" 
                                   class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-file-pdf"></i> Perfil
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

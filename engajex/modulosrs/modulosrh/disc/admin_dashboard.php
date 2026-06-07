<?php
session_start();

// Verificar se está logado como admin
if (!isset($_SESSION['admin_authenticated']) || !$_SESSION['admin_authenticated']) {
    header('Location: admin_login.php');
    exit;
}

// Carregar dados dos candidatos DISC
$candidatos = [];
$total_candidatos = 0;
$pendentes = 0;
$concluidos = 0;

$candidatos_file = 'resultados/candidatos.csv';
if (file_exists($candidatos_file)) {
    $fp = fopen($candidatos_file, 'r');
    fgetcsv($fp); // Pular cabeçalho
    
    while (($data = fgetcsv($fp)) !== FALSE) {
        if ($data[2] === $_SESSION['admin_email']) {
            $total_candidatos++;
            $avaliacao_file = 'resultados/' . str_replace(['@', '.'], '_', $data[4]) . '_avaliacao.csv';
            
            if (file_exists($avaliacao_file)) {
                $concluidos++;
            } else {
                $pendentes++;
                // Guardar apenas os 5 candidatos pendentes mais recentes
                if (count($candidatos) < 5) {
                    $candidatos[] = [
                        'nome' => $data[3],
                        'email' => $data[4],
                        'cargo' => $data[6],
                        'data' => $data[0],
                        'tipo' => 'DISC'
                    ];
                }
            }
        }
    }
    fclose($fp);
}

// Carregar dados dos candidatos MBTI
$candidatos_mbti = [];
$total_candidatos_mbti = 0;
$pendentes_mbti = 0;
$concluidos_mbti = 0;

$candidatos_mbti_file = 'resultados/candidatos_mbti.csv';
if (file_exists($candidatos_mbti_file)) {
    $fp = fopen($candidatos_mbti_file, 'r');
    fgetcsv($fp); // Pular cabeçalho
    
    while (($data = fgetcsv($fp)) !== FALSE) {
        if ($data[2] === $_SESSION['admin_email']) {
            $total_candidatos_mbti++;
            $avaliacao_file = 'resultados/' . str_replace(['@', '.'], '_', $data[4]) . '_avaliacao_mbti.csv';
            
            if (file_exists($avaliacao_file)) {
                $concluidos_mbti++;
            } else {
                $pendentes_mbti++;
                // Guardar apenas os 5 candidatos pendentes mais recentes
                if (count($candidatos) < 5) {
                    $candidatos[] = [
                        'nome' => $data[3],
                        'email' => $data[4],
                        'cargo' => $data[6],
                        'data' => $data[0],
                        'tipo' => 'MBTI'
                    ];
                }
            }
        }
    }
    fclose($fp);
}

// Carregar dados dos candidatos Big Five
$candidatos_bigfive = [];
$total_candidatos_bigfive = 0;
$pendentes_bigfive = 0;
$concluidos_bigfive = 0;

$candidatos_bigfive_file = 'resultados/candidatos_bigfive.csv';
if (file_exists($candidatos_bigfive_file)) {
    $fp = fopen($candidatos_bigfive_file, 'r');
    fgetcsv($fp); // Pular cabeçalho
    
    while (($data = fgetcsv($fp)) !== FALSE) {
        if ($data[2] === $_SESSION['admin_email']) {
            $total_candidatos_bigfive++;
            $avaliacao_file = 'resultados/' . str_replace(['@', '.'], '_', $data[4]) . '_avaliacao_bigfive.csv';
            
            if (file_exists($avaliacao_file)) {
                $concluidos_bigfive++;
            } else {
                $pendentes_bigfive++;
                // Guardar apenas os 5 candidatos pendentes mais recentes
                if (count($candidatos) < 5) {
                    $candidatos[] = [
                        'nome' => $data[3],
                        'email' => $data[4],
                        'cargo' => $data[6],
                        'data' => $data[0],
                        'tipo' => 'Big Five'
                    ];
                }
            }
        }
    }
    fclose($fp);
}

// Ordenar candidatos por data (mais recentes primeiro)
usort($candidatos, function($a, $b) {
    return strtotime($b['data']) - strtotime($a['data']);
});
$candidatos = array_slice($candidatos, 0, 5);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema DISC/MBTI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
            padding: 20px;
        }
        .content-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .stats-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }
        .stats-card h3 {
            color: #2c3e50;
            font-size: 1.2rem;
            margin-bottom: 15px;
        }
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: #3498db;
        }
        .pending-list {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        .action-buttons .btn {
            margin-bottom: 10px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'header.php'; ?>
        
        <div class="content-container">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">Bem-vindo(a), <?php echo htmlspecialchars($_SESSION['admin_nome']); ?>!</h2>
                    <p class="text-muted">Gerencie seus candidatos e visualize relatórios.</p>
                </div>
                <div>
                    <a href="comparison.php" class="btn btn-info">
                        <i class="bi bi-info-circle"></i> Entenda as diferenças entre DISC e MBTI
                    </a>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="action-buttons p-3 bg-light rounded">
                        <h4 class="mb-3 text-primary">DISC</h4>
                        <a href="register_candidate.php" class="btn btn-primary mb-2 w-100">
                            <i class="bi bi-person-plus"></i> Novo Candidato DISC
                        </a>
                        <a href="view_candidates.php" class="btn btn-outline-primary w-100">
                            <i class="bi bi-people"></i> Ver Candidatos DISC
                        </a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="action-buttons p-3 bg-light rounded">
                        <h4 class="mb-3 text-success">MBTI</h4>
                        <a href="register_candidate_mbti.php" class="btn btn-success mb-2 w-100">
                            <i class="bi bi-person-plus"></i> Novo Candidato MBTI
                        </a>
                        <a href="view_candidates_mbti.php" class="btn btn-outline-success w-100">
                            <i class="bi bi-people"></i> Ver Candidatos MBTI
                        </a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="action-buttons p-3 bg-light rounded">
                        <h4 class="mb-3 text-info">Big Five</h4>
                        <a href="register_candidate_bigfive.php" class="btn btn-info mb-2 w-100">
                            <i class="bi bi-person-plus"></i> Novo Candidato Big Five
                        </a>
                        <a href="view_candidates_bigfive.php" class="btn btn-outline-info w-100">
                            <i class="bi bi-people"></i> Ver Candidatos Big Five
                        </a>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="stats-card">
                        <h3>Avaliações DISC</h3>
                        <div class="row">
                            <div class="col-4 text-center">
                                <div class="stats-number"><?php echo $total_candidatos; ?></div>
                                <div class="stats-label">Total</div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="stats-number"><?php echo $pendentes; ?></div>
                                <div class="stats-label">Pendentes</div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="stats-number"><?php echo $concluidos; ?></div>
                                <div class="stats-label">Concluídos</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <h3>Avaliações MBTI</h3>
                        <div class="row">
                            <div class="col-4 text-center">
                                <div class="stats-number"><?php echo $total_candidatos_mbti; ?></div>
                                <div class="stats-label">Total</div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="stats-number"><?php echo $pendentes_mbti; ?></div>
                                <div class="stats-label">Pendentes</div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="stats-number"><?php echo $concluidos_mbti; ?></div>
                                <div class="stats-label">Concluídos</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <h3>Avaliações Big Five</h3>
                        <div class="row">
                            <div class="col-4 text-center">
                                <div class="stats-number"><?php echo $total_candidatos_bigfive; ?></div>
                                <div class="stats-label">Total</div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="stats-number"><?php echo $pendentes_bigfive; ?></div>
                                <div class="stats-label">Pendentes</div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="stats-number"><?php echo $concluidos_bigfive; ?></div>
                                <div class="stats-label">Concluídos</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending List -->
            <div class="pending-list">
                <h4 class="mb-3">
                    <i class="bi bi-clock-history"></i> 
                    Avaliações Pendentes Recentes
                </h4>
                <?php if (empty($candidatos)): ?>
                    <p class="text-muted">Não há avaliações pendentes.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>E-mail</th>
                                    <th>Cargo</th>
                                    <th>Data</th>
                                    <th>Tipo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($candidatos as $candidato): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($candidato['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($candidato['email']); ?></td>
                                        <td><?php echo htmlspecialchars($candidato['cargo']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($candidato['data'])); ?></td>
                                        <td><span class="badge bg-<?php echo $candidato['tipo'] === 'DISC' ? 'primary' : ($candidato['tipo'] === 'MBTI' ? 'success' : 'info'); ?>"><?php echo $candidato['tipo']; ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['superadmin_authenticated'])) {
    header('Location: superadmin_login.php');
    exit;
}

$error = '';
$candidato = null;
$resultados = null;

if (isset($_GET['email'])) {
    $email = $_GET['email'];
    
    // Buscar dados do candidato
    $candidatos_file = 'resultados/candidatos_bigfive.csv';
    if (file_exists($candidatos_file)) {
        if (($handle = fopen($candidatos_file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle)) !== FALSE) {
                if ($data[4] === $email) { // Email está na quinta coluna
                    $candidato = [
                        'data_criacao' => $data[0],
                        'selecionador_nome' => $data[1],
                        'selecionador_email' => $data[2],
                        'nome' => $data[3],
                        'email' => $data[4],
                        'empresa' => $data[5],
                        'cargo' => $data[6],
                        'status' => isset($data[7]) ? $data[7] : 'pendente'
                    ];
                    break;
                }
            }
            fclose($handle);
        }
    }
    
    // Se encontrou o candidato e o teste está concluído, buscar resultados
    if ($candidato && $candidato['status'] === 'completed') {
        $resultados_file = 'resultados/' . str_replace(['@', '.'], '_', $email) . '_avaliacao_bigfive.csv';
        if (file_exists($resultados_file)) {
            if (($handle = fopen($resultados_file, "r")) !== FALSE) {
                $header = fgetcsv($handle);
                $resultados = fgetcsv($handle);
                fclose($handle);
                
                if ($resultados) {
                    $resultados = [
                        'abertura' => $resultados[0],
                        'conscienciosidade' => $resultados[1],
                        'extroversao' => $resultados[2],
                        'amabilidade' => $resultados[3],
                        'neuroticismo' => $resultados[4]
                    ];
                }
            }
        }
    }
    
    if (!$candidato) {
        $error = "Candidato não encontrado.";
    } elseif ($candidato['status'] !== 'completed') {
        $error = "O candidato ainda não concluiu o teste.";
    } elseif (!$resultados) {
        $error = "Resultados não encontrados.";
    }
} else {
    header('Location: superadmin_panel.php#candidatos-bigfive');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados Big Five</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Resultados Big Five</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                            <div class="text-center mt-3">
                                <a href="superadmin_panel.php#candidatos-bigfive" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Voltar
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="mb-4">
                                <h5>Dados do Candidato:</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Nome:</strong> <?php echo htmlspecialchars($candidato['nome']); ?></p>
                                        <p><strong>Email:</strong> <?php echo htmlspecialchars($candidato['email']); ?></p>
                                        <p><strong>Empresa:</strong> <?php echo htmlspecialchars($candidato['empresa']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Cargo:</strong> <?php echo htmlspecialchars($candidato['cargo']); ?></p>
                                        <p><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($candidato['data_criacao'])); ?></p>
                                        <p><strong>Status:</strong> <?php echo ucfirst($candidato['status']); ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <canvas id="bigFiveChart"></canvas>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="mb-3">Pontuações:</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Dimensão</th>
                                                    <th>Pontuação</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Abertura à Experiência</td>
                                                    <td><?php echo $resultados['abertura']; ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Conscienciosidade</td>
                                                    <td><?php echo $resultados['conscienciosidade']; ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Extroversão</td>
                                                    <td><?php echo $resultados['extroversao']; ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Amabilidade</td>
                                                    <td><?php echo $resultados['amabilidade']; ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Neuroticismo</td>
                                                    <td><?php echo $resultados['neuroticismo']; ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-center">
                                <a href="superadmin_panel.php#candidatos-bigfive" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Voltar
                                </a>
                            </div>
                            
                            <script>
                                const ctx = document.getElementById('bigFiveChart').getContext('2d');
                                new Chart(ctx, {
                                    type: 'radar',
                                    data: {
                                        labels: [
                                            'Abertura à Experiência',
                                            'Conscienciosidade',
                                            'Extroversão',
                                            'Amabilidade',
                                            'Neuroticismo'
                                        ],
                                        datasets: [{
                                            label: 'Pontuações Big Five',
                                            data: [
                                                <?php echo $resultados['abertura']; ?>,
                                                <?php echo $resultados['conscienciosidade']; ?>,
                                                <?php echo $resultados['extroversao']; ?>,
                                                <?php echo $resultados['amabilidade']; ?>,
                                                <?php echo $resultados['neuroticismo']; ?>
                                            ],
                                            fill: true,
                                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                            borderColor: 'rgb(54, 162, 235)',
                                            pointBackgroundColor: 'rgb(54, 162, 235)',
                                            pointBorderColor: '#fff',
                                            pointHoverBackgroundColor: '#fff',
                                            pointHoverBorderColor: 'rgb(54, 162, 235)'
                                        }]
                                    },
                                    options: {
                                        elements: {
                                            line: {
                                                borderWidth: 3
                                            }
                                        },
                                        scales: {
                                            r: {
                                                angleLines: {
                                                    display: true
                                                },
                                                suggestedMin: 0,
                                                suggestedMax: 40
                                            }
                                        }
                                    }
                                });
                            </script>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

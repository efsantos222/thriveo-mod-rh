<?php
session_start();
require_once '../header.php';
require_once 'questions.php';

if (!isset($_SESSION['user_email'])) {
    header('Location: ../login.php');
    exit;
}

$user_email = $_SESSION['user_email'];
$result_file = 'resultados/' . str_replace(['@', '.'], '_', $user_email) . '_mbti.csv';

if (!file_exists($result_file)) {
    header('Location: test.php');
    exit;
}

// Carregar resultados
$fp = fopen($result_file, 'r');
$headers = fgetcsv($fp);
$data = fgetcsv($fp);
fclose($fp);

$mbti_type = $data[2]; // Tipo MBTI está na terceira coluna
$type_info = $mbti_types[$mbti_type];

// Calcular percentuais
$total_questions = 8; // 2 questões por dimensão
$percentages = [
    'E' => ($data[3] / ($total_questions/4)) * 100,
    'I' => ($data[4] / ($total_questions/4)) * 100,
    'S' => ($data[5] / ($total_questions/4)) * 100,
    'N' => ($data[6] / ($total_questions/4)) * 100,
    'T' => ($data[7] / ($total_questions/4)) * 100,
    'F' => ($data[8] / ($total_questions/4)) * 100,
    'J' => ($data[9] / ($total_questions/4)) * 100,
    'P' => ($data[10] / ($total_questions/4)) * 100
];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados MBTI - Sistema de RH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .result-card {
            max-width: 800px;
            margin: 2rem auto;
        }
        .dimension-bar {
            height: 30px;
            position: relative;
        }
        .dimension-label {
            position: absolute;
            width: 100%;
            text-align: center;
            color: white;
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
            line-height: 30px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="result-card">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Seu Resultado MBTI</h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h2 class="display-4"><?php echo $mbti_type; ?></h2>
                        <h3 class="text-muted"><?php echo $type_info['name']; ?></h3>
                    </div>

                    <div class="mb-4">
                        <h5>Descrição</h5>
                        <p class="lead"><?php echo $type_info['description']; ?></p>
                    </div>

                    <div class="mb-4">
                        <h5>Seus Percentuais</h5>
                        
                        <!-- E/I -->
                        <div class="mb-3">
                            <div class="row g-0">
                                <div class="col-6">
                                    <div class="dimension-bar bg-primary" style="width: <?php echo $percentages['E']; ?>%">
                                        <span class="dimension-label">E (<?php echo round($percentages['E']); ?>%)</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="dimension-bar bg-success" style="width: <?php echo $percentages['I']; ?>%">
                                        <span class="dimension-label">I (<?php echo round($percentages['I']); ?>%)</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- S/N -->
                        <div class="mb-3">
                            <div class="row g-0">
                                <div class="col-6">
                                    <div class="dimension-bar bg-info" style="width: <?php echo $percentages['S']; ?>%">
                                        <span class="dimension-label">S (<?php echo round($percentages['S']); ?>%)</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="dimension-bar bg-warning" style="width: <?php echo $percentages['N']; ?>%">
                                        <span class="dimension-label">N (<?php echo round($percentages['N']); ?>%)</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- T/F -->
                        <div class="mb-3">
                            <div class="row g-0">
                                <div class="col-6">
                                    <div class="dimension-bar bg-danger" style="width: <?php echo $percentages['T']; ?>%">
                                        <span class="dimension-label">T (<?php echo round($percentages['T']); ?>%)</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="dimension-bar bg-secondary" style="width: <?php echo $percentages['F']; ?>%">
                                        <span class="dimension-label">F (<?php echo round($percentages['F']); ?>%)</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- J/P -->
                        <div class="mb-3">
                            <div class="row g-0">
                                <div class="col-6">
                                    <div class="dimension-bar bg-dark" style="width: <?php echo $percentages['J']; ?>%">
                                        <span class="dimension-label">J (<?php echo round($percentages['J']); ?>%)</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="dimension-bar bg-primary" style="width: <?php echo $percentages['P']; ?>%">
                                        <span class="dimension-label">P (<?php echo round($percentages['P']); ?>%)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5 class="mb-0">Pontos Fortes</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <?php foreach ($type_info['strengths'] as $strength): ?>
                                            <li class="list-group-item">
                                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                <?php echo $strength; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5 class="mb-0">Carreiras Compatíveis</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <?php foreach ($type_info['career_matches'] as $career): ?>
                                            <li class="list-group-item">
                                                <i class="bi bi-briefcase-fill text-primary me-2"></i>
                                                <?php echo $career; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

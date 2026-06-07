<?php
session_start();

// Verificar se está logado como superadmin ou admin
if ((!isset($_SESSION['admin_authenticated']) || !$_SESSION['admin_authenticated']) && 
    (!isset($_SESSION['superadmin_authenticated']) || !$_SESSION['superadmin_authenticated'])) {
    header('Location: admin_login.php');
    exit;
}

// Verificar se o arquivo foi especificado
if (!isset($_GET['file']) || empty($_GET['file'])) {
    header('Location: view_candidates.php');
    exit;
}

// Sanitizar o nome do arquivo e verificar se está na pasta resultados
$file = basename($_GET['file']);
$file_path = 'resultados/' . $file;

if (!file_exists($file_path) || !is_file($file_path) || pathinfo($file_path, PATHINFO_EXTENSION) !== 'csv') {
    header('Location: view_candidates.php');
    exit;
}

// Verificar se o arquivo pertence ao selecionador atual
$is_authorized = false;
$candidatos_file = 'resultados/candidatos.csv';

if (file_exists($candidatos_file)) {
    $fp = fopen($candidatos_file, 'r');
    fgetcsv($fp); // Pular cabeçalho
    
    while (($data = fgetcsv($fp)) !== FALSE) {
        if ($data[2] === $_SESSION['admin_email']) {
            $candidato_file = 'resultados/' . str_replace(['@', '.'], '_', $data[4]) . '_avaliacao.csv';
            if ($candidato_file === $file_path) {
                $is_authorized = true;
                $candidato_nome = $data[3];
                break;
            }
        }
    }
    fclose($fp);
}

if (!$is_authorized) {
    header('Location: view_candidates.php');
    exit;
}

// Carregar os dados do CSV
$rows = [];
$header = [];

if (($handle = fopen($file_path, "r")) !== FALSE) {
    // Ler o cabeçalho
    if (($data = fgetcsv($handle)) !== FALSE) {
        $header = $data;
    }
    
    // Ler os dados
    while (($data = fgetcsv($handle)) !== FALSE) {
        $rows[] = $data;
    }
    fclose($handle);
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Resultados - Sistema DISC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .table th {
            background-color: #f8f9fa;
        }
        .card-header {
            background-color: #0d6efd;
        }
        .back-button {
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="back-button">
            <a href="view_candidates.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
        
        <div class="card">
            <div class="card-header text-white">
                <h5 class="mb-0">
                    <i class="bi bi-file-earmark-text"></i>
                    Resultados da Avaliação - <?php echo htmlspecialchars($candidato_nome); ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <?php foreach ($header as $col): ?>
                                    <th><?php echo htmlspecialchars($col); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <?php foreach ($row as $cell): ?>
                                        <td><?php echo htmlspecialchars($cell); ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

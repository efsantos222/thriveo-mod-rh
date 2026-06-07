<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$page = 'reports';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Relatórios - Thriveo ZBB</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body style="background-color: var(--background-color);">
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        <main class="main-content">
            <h1 class="mb-4">Relatórios e Exportações</h1>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <div class="card">
                    <h3>Relatório Executivo Mensal</h3>
                    <p class="text-secondary mb-4">Consolidado de todas as despesas, aprovações e economias do mês.</p>
                    <button class="btn btn-primary" onclick="exportReport('executive', 'pdf')">Gerar PDF</button>
                    <button class="btn btn-secondary" onclick="exportReport('executive', 'csv')">Excel</button>
                </div>

                <div class="card">
                    <h3>Análise Vertical de Custos</h3>
                    <p class="text-secondary mb-4">Detalhamento da estrutura de custos por centro de custo e categoria.
                    </p>
                    <button class="btn btn-primary" onclick="exportReport('vertical', 'pdf')">Gerar PDF</button>
                    <button class="btn btn-secondary" onclick="exportReport('vertical', 'csv')">Excel</button>
                </div>

                <div class="card">
                    <h3>Performance ZBB</h3>
                    <p class="text-secondary mb-4">KPIs de aderência, tempo de aprovação e justificativas rejeitadas.
                    </p>
                    <button class="btn btn-primary" onclick="exportReport('performance', 'pdf')">Gerar PDF</button>
                    <button class="btn btn-secondary" onclick="exportReport('performance', 'csv')">Excel</button>
                </div>
            </div>
        </main>
    </div>
    <script>
        function exportReport(type, format) {
            const url = `api/export_report.php?type=${type}&format=${format}`;
            if(format === 'pdf') {
                window.open(url, '_blank');
            } else {
                window.location.href = url;
            }
        }
    </script>
</body>

</html>
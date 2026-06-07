<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    die("Acesso negado");
}

$type = $_GET['type'] ?? 'executive';
$format = $_GET['format'] ?? 'csv';
$companyId = $_SESSION['company_id'] ?? 1;

// Prepare Data
$data = [];
$filename = "relatorio_" . $type . "_" . date('Ymd');
$title = "Relatório";
$headers = [];

if ($type === 'executive') {
    $title = "Relatório Executivo Mensal";
    // Group by Cost Center for executive view
    $sql = "SELECT cc.name as context, 
            SUM(b.amount_budgeted) as total_budgeted, 
            SUM(b.amount_realized) as total_realized, 
            (SUM(b.amount_budgeted) - SUM(b.amount_realized)) as economy
            FROM budgets b
            JOIN cost_centers cc ON b.cost_center_id = cc.id
            WHERE b.company_id = ?
            GROUP BY cc.name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$companyId]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $headers = ['Centro de Custo', 'Orçado (Ref)', 'Realizado', 'Economia'];

} elseif ($type === 'vertical') {
    $title = "Análise Vertical de Custos";
    // Detailed list
    $sql = "SELECT cc.name as cc_name, c.name as cat_name, b.amount_realized 
            FROM budgets b
            JOIN cost_centers cc ON b.cost_center_id = cc.id
            JOIN categories c ON b.category_id = c.id
            WHERE b.company_id = ?
            ORDER BY cc.name, b.amount_realized DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$companyId]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $headers = ['Centro de Custo', 'Categoria', 'Valor Realizado'];

} elseif ($type === 'performance') {
    $title = "Performance ZBB";
    // Status counts
    $sql = "SELECT b.status, COUNT(*) as count, SUM(b.amount_realized) as value
            FROM budgets b
            WHERE b.company_id = ?
            GROUP BY b.status";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$companyId]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $headers = ['Status', 'Quantidade de Itens', 'Valor Total'];
}

// OUTPUT
if ($format === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, $headers, ';'); // Excel friendly delimiter

    foreach ($data as $row) {
        // Format numbers for CSV if needed, but raw is better. 
        // Just replacing dots with commas for BR Excel if strictly needed, but let's keep standard.
        // Actually, BR Excel expects semicolon delimiter and numbers with comma decimal.
        $rowFormatted = array_map(function ($val) {
            return is_numeric($val) ? number_format($val, 2, ',', '') : $val;
        }, $row);
        fputcsv($output, $rowFormatted, ';');
    }
    fclose($output);
    exit;

} elseif ($format === 'pdf') { // Fake PDF (Print View)
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">

    <head>
        <meta charset="UTF-8">
        <title>
            <?php echo $title; ?>
        </title>
        <style>
            body {
                font-family: sans-serif;
                padding: 2rem;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 2rem;
            }

            th,
            td {
                border: 1px solid #ccc;
                padding: 0.5rem;
                text-align: left;
            }

            th {
                background: #eee;
            }

            .header {
                text-align: center;
                margin-bottom: 3rem;
            }

            .print-btn {
                display: block;
                margin-bottom: 1rem;
            }

            @media print {
                .print-btn {
                    display: none;
                }
            }
        </style>
    </head>

    <body onload="window.print()">
        <button class="print-btn" onclick="window.print()">🖨️ Imprimir / Salvar PDF</button>

        <div class="header">
            <h1>Thriveo ZBB</h1>
            <h2>
                <?php echo $title; ?>
            </h2>
            <p>Gerado em:
                <?php echo date('d/m/Y H:i'); ?>
            </p>
        </div>

        <table>
            <thead>
                <tr>
                    <?php foreach ($headers as $h): ?>
                        <th>
                            <?php echo $h; ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row): ?>
                    <tr>
                        <?php foreach ($row as $cell): ?>
                            <td>
                                <?php echo is_numeric($cell) ? number_format($cell, 2, ',', '.') : htmlspecialchars($cell); ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </body>

    </html>
    <?php
    exit;
}
?>
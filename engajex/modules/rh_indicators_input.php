<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../config.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$user = $_SESSION['user_id'];
$companyId = $_SESSION['company_id'] ?? 1;
$role = $_SESSION['role'];

// Check Permission (Simple: Admin, Responsible, or Permission Grant)
$canEdit = in_array($role, ['admin', 'responsible', 'company_admin']);
if (!$canEdit) {
    // Check specific permission
    $stmt = $pdo->prepare("SELECT can_edit FROM rh_permissions WHERE user_id = ? AND company_id = ?");
    $stmt->execute([$user, $companyId]);
    $canEdit = $stmt->fetchColumn();
}

if (!$canEdit) {
    die("Acesso negado. Solicite permissão ao responsável.");
}

// Table Definitions
$tables = [
    'rh_contratacoes' => '1. Contratações',
    'rh_demissoes' => '2. Demissões',
    'rh_efetivo' => '3. Efetivo',
    'rh_horasext' => '4. Horas Extras',
    'rh_folhapag' => '5. Folha de Pagamento',
    'rh_humor' => '6. Humor',
    'rh_feedback' => '6.1. Feedback', // Correction for duplicate numbering
    'rh_rstempo' => '7. Tempo R&S',
    'rh_rsvagas' => '7.1. Vagas R&S',
    'rh_rsdesc' => '8. Economia R&S',
    'rh_realoc' => '9. Realocação',
    'rh_capacita' => '10. Capacitação',
    // GPTW Tables
    'geral_gptw' => 'GPTW 1. Geral',
    'respeito_gptw' => 'GPTW 2. Respeito',
    'demais_gptw' => 'GPTW 3. Demais',
    'credibilidade_gptw' => 'GPTW 4. Credibilidade',
    'imparcialidade_gptw' => 'GPTW 5. Imparcialidade',
    'orgulho_gptw' => 'GPTW 6. Orgulho',
    'camaradagem_gptw' => 'GPTW 7. Camaradagem',
    'adicionais_gptw' => 'GPTW 8. Adicionais',
    'bench_gptw' => 'GPTW 9. Benchmark',
    'enps_gptw' => 'GPTW 10. e-NPS',
    'lidera_gptw' => 'GPTW 11.A Liderança',
    'ivr_gptw' => 'GPTW 11.B Inovação (IVR)',
    'particip_gptw' => 'GPTW 11.C Participação',
    'reunioes_gptw' => 'GPTW 12.A Reuniões',
    'melhorar_gptw' => 'GPTW 12.B Melhorar',
    'manter_gptw' => 'GPTW 13. Manter'
];

$selectedTable = $_GET['table'] ?? 'rh_contratacoes';
if (!array_key_exists($selectedTable, $tables)) {
    $selectedTable = 'rh_contratacoes';
}

$message = '';

// --- HANDLE POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_record') {
        // Build INSERT dynamically based on table columns
        // Common fields: mes, ano, mes_n, data_ref
        $mes = $_POST['mes'];
        $ano = $_POST['ano'];
        $mes_n = $_POST['mes_n'];
        $data_ref = $_POST['data_ref'];

        // Remove standard fields from POST to find specific fields
        $data = $_POST;
        unset($data['action'], $data['mes'], $data['ano'], $data['mes_n'], $data['data_ref']);

        $cols = "company_id, mes, ano, mes_n, data_ref";
        $vals = "?, ?, ?, ?, ?";
        $params = [$companyId, $mes, $ano, $mes_n, $data_ref];

        foreach ($data as $k => $v) {
            $cols .= ", $k";
            $vals .= ", ?";
            $params[] = $v;
        }

        try {
            $sql = "INSERT INTO $selectedTable ($cols) VALUES ($vals) 
                    ON DUPLICATE KEY UPDATE ";

            // Build ON DUPLICATE UPDATE clause
            $updates = [];
            foreach (array_keys($data) as $k) {
                $updates[] = "$k = VALUES($k)";
            }
            $sql .= implode(", ", $updates);

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $message = "Registro salvo com sucesso!";

        } catch (PDOException $e) {
            $message = "Erro ao salvar: " . $e->getMessage();
        }
    }

    // --- CSV IMPORT LOGIC ---
    if (isset($_POST['action']) && $_POST['action'] === 'import_csv') {
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
            $file = $_FILES['csv_file']['tmp_name'];

            // Detect Delimiter & Read First Line for Header
            $handle = fopen($file, "r");
            if ($handle) {
                $firstLine = fgets($handle);
                $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';

                // BOM Removal from First Line
                $bom = pack('H*', 'EFBBBF');
                if (substr($firstLine, 0, 3) === $bom) {
                    $firstLine = substr($firstLine, 3);
                }

                $header = str_getcsv($firstLine, $delimiter);

                if ($header && count($header) > 0) {
                    // Fetch valid columns for this table
                    $validCols = [];
                    $q = $pdo->query("DESCRIBE $selectedTable");
                    foreach ($q->fetchAll() as $c) {
                        $colName = $c['Field'];
                        if (!in_array($colName, ['id', 'created_at'])) {
                            $validCols[] = $colName;
                        }
                    }

                    // Map Header Index to Column Name
                    $colMap = [];
                    foreach ($header as $idx => $h) {
                        $cleanH = trim(strtolower($h));
                        // Remove potential quotes or hidden chars
                        $cleanH = str_replace(['"', "'", "\n", "\r", "\t"], '', $cleanH);

                        if (in_array($cleanH, $validCols)) {
                            $colMap[$idx] = $cleanH;
                        }
                    }

                    if (empty($colMap)) {
                        $csvCols = implode(" | ", $header);
                        $message = "Erro: Nenhuma coluna correspondente. <br>Colunas no CSV: [$csvCols]. <br>Colunas esperadas: " . implode(", ", $validCols);
                    } else {
                        // Rewind not enough because we read first line manually
                        // But fgetcsv continues? No, we closed?
                        // Actually easier to close and reopen or just continue parsing line by line
                        // But str_getcsv acts on string.
                        // Let's re-open specific for the loop or keep going?
                        // We consumed one line. Let's keep reading.

                        $rowsImported = 0;
                        $debugLog = [];
                        while (($line = fgets($handle)) !== FALSE) {
                            $row = str_getcsv($line, $delimiter);
                            // Cleanup empty rows
                            if (count($row) < 1 || (count($row) == 1 && empty(trim($row[0]))))
                                continue;

                            $insertData = [];
                            $insertData['company_id'] = $companyId;

                            foreach ($colMap as $idx => $colName) {
                                if (isset($row[$idx])) {
                                    $val = trim($row[$idx]);
                                    $val = str_replace(['R$', ' '], '', $val);
                                    if (is_numeric(str_replace(',', '.', $val))) {
                                        $val = str_replace(',', '.', $val);
                                    }
                                    if ($val !== '')
                                        $insertData[$colName] = $val;
                                }
                            }
                            if (isset($insertData['data_ref'])) {
                                $d = $insertData['data_ref'];
                                if (strpos($d, '/') !== false) {
                                    $parts = explode('/', $d);
                                    if (count($parts) == 3) {
                                        // Assume DD/MM/YYYY
                                        $insertData['data_ref'] = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
                                    }
                                }
                            }

                            // Debug: Check if key fields exist
                            if (!isset($insertData['mes']) || !isset($insertData['ano'])) {
                                if (count($debugLog) < 5)
                                    $debugLog[] = "Linha " . ($rowsImported + 2) . " ignorada (sem mes/ano). Dados: " . json_encode($insertData);
                                continue;
                            }

                            // If data_ref is missing, try to build it or defaulting
                            if (!isset($insertData['data_ref'])) {
                                // Default to 1st of month if mes_n exists, else today
                                $m = $insertData['mes_n'] ?? date('m');
                                $y = $insertData['ano'] ?? date('Y');
                                if (strlen($m) == 1)
                                    $m = "0$m";
                                $insertData['data_ref'] = "$y-$m-01";
                            }

                            $cols = implode(", ", array_keys($insertData));
                            $places = implode(", ", array_fill(0, count($insertData), "?"));
                            $vals = array_values($insertData);

                            $updates = [];
                            foreach (array_keys($insertData) as $k) {
                                if ($k != 'company_id')
                                    $updates[] = "$k = VALUES($k)";
                            }
                            $updateStr = implode(", ", $updates);

                            try {
                                $sql = "INSERT INTO $selectedTable ($cols) VALUES ($places) ON DUPLICATE KEY UPDATE $updateStr";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute($vals);
                                $rowsImported++;
                            } catch (Exception $e) {
                                if (count($debugLog) < 5)
                                    $debugLog[] = "Erro SQL na linha " . ($rowsImported + 2) . ": " . $e->getMessage();
                            }
                        }
                        $message = "Importação concluída: $rowsImported registros processados.";
                        if (!empty($debugLog)) {
                            $message .= "<br><div style='margin-top:0.5rem; font-size:0.9em; background:rgba(0,0,0,0.2); padding:0.5rem;'><strong>Debug:</strong><br>" . implode("<br>", $debugLog) . "</div>";
                        }
                    }
                } else {
                    $message = "Erro: Cabeçalho CSV inválido ou vazio.";
                }
                fclose($handle);
            }
        } else {
            $message = "Erro no upload do arquivo.";
        }
    }
}

// Fetch Data for List
$list = $pdo->prepare("SELECT * FROM $selectedTable WHERE company_id = ? ORDER BY data_ref DESC LIMIT 50");
$list->execute([$companyId]);
$records = $list->fetchAll();

// Get Columns for Form (Simple Logic: Describe Table)
// In production, hardcode fields for better UX. Here, auto-discovery for brevity.
$columns = [];
$colTypes = [];
try {
    $q = $pdo->query("DESCRIBE $selectedTable");
    $rawCols = $q->fetchAll();
    foreach ($rawCols as $c) {
        if (!in_array($c['Field'], ['id', 'company_id', 'created_at'])) {
            $columns[] = $c['Field'];
            // Detect Type
            $type = strtolower($c['Type']);
            if (strpos($type, 'int') !== false || strpos($type, 'decimal') !== false || strpos($type, 'float') !== false || strpos($type, 'double') !== false) {
                $colTypes[$c['Field']] = 'number';
            } else {
                $colTypes[$c['Field']] = 'text';
            }
        }
    }
} catch (PDOException $e) {
    // Table might not exist yet if script didn't run
    $message = "Erro: Tabela não encontrada. Execute update_db_rh_indicators.php";
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Input Indicadores RH</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .input-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }

        .form-section {
            background: rgba(255, 255, 255, 0.05);
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content">
            <h1>📥 Input de Indicadores RH</h1>

            <?php if ($message): ?>
                <div
                    style="background:rgba(59,130,246,0.1); color:#60a5fa; padding:1rem; border-radius:0.5rem; margin-bottom:1rem;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Selector -->
            <div style="margin-bottom: 2rem;">
                <label>Selecione a Tabela:</label>
                <select onchange="window.location.href='?table='+this.value"
                    style="padding:0.5rem; border-radius:0.3rem; background:#1e293b; color:white; border:1px solid #475569;">
                    <?php foreach ($tables as $t => $label): ?>
                        <option value="<?php echo $t; ?>" <?php echo $t === $selectedTable ? 'selected' : ''; ?>>
                            <?php echo $label; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <style>
                .tabs {
                    display: flex;
                    gap: 1rem;
                    margin-bottom: 1rem;
                    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                }

                .tab {
                    padding: 0.5rem 1rem;
                    cursor: pointer;
                    opacity: 0.7;
                    border-bottom: 2px solid transparent;
                }

                .tab.active {
                    opacity: 1;
                    border-bottom: 2px solid var(--primary-color);
                }
            </style>
            <script>
                function showTab(id) {
                    document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
                    document.getElementById(id).style.display = 'block';
                    document.querySelectorAll('.tab').forEach(el => el.classList.remove('active'));
                    document.querySelector(`[onclick="showTab('${id}')"]`).classList.add('active');
                }
            </script>

            <div class="tabs">
                <div class="tab active" onclick="showTab('manual')">Input Manual</div>
                <div class="tab" onclick="showTab('csv')">Importar CSV</div>
            </div>

            <!-- Form Manual -->
            <div id="manual" class="tab-content form-section">
                <h3>Adicionar / Atualizar Registro</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="add_record">
                    <div class="input-grid">
                        <div>
                            <label>Data Ref (YYYY-MM-DD)</label>
                            <input type="date" name="data_ref" required style="width:100%; padding:0.5rem;">
                        </div>
                        <div>
                            <label>Mês (Jan, Fev...)</label>
                            <input type="text" name="mes" required style="width:100%; padding:0.5rem;"
                                placeholder="Ex: Jan">
                        </div>
                        <div>
                            <label>Ano</label>
                            <input type="text" name="ano" required style="width:100%; padding:0.5rem;"
                                value="<?php echo date('Y'); ?>">
                        </div>
                        <div>
                            <label>Mês N (1-12)</label>
                            <input type="number" name="mes_n" required style="width:100%; padding:0.5rem;" min="1"
                                max="12">
                        </div>

                        <?php foreach ($columns as $col): ?>
                            <?php if (!in_array($col, ['mes', 'ano', 'mes_n', 'data_ref'])): ?>
                                <div>
                                    <label>
                                        <?php echo ucfirst(str_replace('_', ' ', $col)); ?>
                                    </label>
                                            <?php if (isset($colTypes[$col]) && $colTypes[$col] == 'number'): ?>
                                        <input type="number" step="0.01" name="<?php echo $col; ?>"
                                            style="width:100%; padding:0.5rem;">
                                <?php else: ?>
                                                    <input type="text" name="<?php echo $col; ?>"
                                                    style="width:100%; padding:0.5rem;" placeholder="Texto...">
                                            <?php endif; ?>
                                        </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <button class="btn btn-primary" style="margin-top:1rem;">💾 Salvar Registro</button>
                </form>
            </div>

            <!-- Form CSV -->
            <div id="csv" class="tab-content form-section" style="display:none;">
                <h3>Importar Arquivo CSV</h3>
                <p style="margin-bottom:1rem; color: #94a3b8;">
                    O arquivo deve conter cabeçalho com os nomes exatos das colunas (ex:
                    <code>mes;ano;contratacao</code>).<br>
                    Separador: <strong>ponto e vírgula (;)</strong>.<br>
                    Colunas disponíveis: <strong><?php echo implode(", ", $columns); ?></strong>
                </p>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="import_csv">
                    <div class="form-group">
                        <input type="file" name="csv_file" accept=".csv" required
                            style="color:white; margin-bottom: 1rem;">
                    </div>
                    <button class="btn btn-primary">📂 Importar CSV</button>
                </form>
            </div>

            <!-- List -->
            <h3>Registros Recentes</h3>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; color:#fff;">
                    <thead>
                        <tr style="border-bottom:1px solid #475569;">
                            <?php foreach ($columns as $c): ?>
                                <th style="padding:0.5rem; text-align:left;">
                                    <?php echo $c; ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $r): ?>
                            <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
                                <?php foreach ($columns as $c): ?>
                                    <td style="padding:0.5rem;">
                                        <?php echo $r[$c]; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>
</body>

</html>
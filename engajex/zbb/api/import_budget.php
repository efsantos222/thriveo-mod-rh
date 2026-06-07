require_once '../../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
echo json_encode(['success' => false, 'message' => 'Não autorizado']);
exit;
}

if (!in_array($_SESSION['role'], ['responsible', 'company_admin', 'manager', 'admin'])) {
echo json_encode(['success' => false, 'message' => 'Acesso Negado (Role)']);
exit;
}

$companyId = $_SESSION['company_id'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
echo json_encode(['success' => false, 'message' => 'Método inválido']);
exit;
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
echo json_encode(['success' => false, 'message' => 'Erro no upload do arquivo']);
exit;
}

$fileTmpPath = $_FILES['file']['tmp_name'];
$fileName = $_FILES['file']['name'];
$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

if ($fileExtension !== 'csv') {
echo json_encode(['success' => false, 'message' => 'Apenas arquivos .csv são suportados neste momento.']);
exit;
}

// Process CSV
$handle = fopen($fileTmpPath, 'r');
if (!$handle) {
echo json_encode(['success' => false, 'message' => 'Erro ao abrir o arquivo']);
exit;
}

// Headers expected: Centro de Custo, Categoria, Natureza, Valor (Ref. Ant.), Valor (Atual), Justificativa
// We will assume order or basic mapping. Simple approach: Index based or simple check.
// Let's assume columns 0 to 5 in order.

// Detect delimiter
$firstLine = fgets($handle);
rewind($handle);
$delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';

$row = 0;
$imported = 0;
$errors = 0;
$errorDetails = [];

while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
$row++;
if ($row === 1)
continue; // Skip header

// Map columns
// CSV structure: 0: CC, 1: Category, 2: Nature, 3: Val Ref, 4: Val Cur, 5: Justification

// Check if line is empty/invalid
if (count($data) < 2) { continue; // Skip empty lines } $ccName=trim($data[0] ?? '' ); $catName=trim($data[1] ?? '' );
    $nature=strtolower(trim($data[2] ?? 'variable' )); $valRef=floatval(str_replace(',', '.' , str_replace(['R$', ' '
    ], '' , $data[3] ?? '0' ))); // Basic cleaning $valCur=floatval(str_replace(',', '.' , str_replace(['R$', ' ' ], ''
    , $data[4] ?? '0' ))); $justification=trim($data[5] ?? '' ); if (!$ccName || !$catName) { $errors++;
    $errorDetails[]="Linha $row: Centro de Custo ou Categoria vazios." ; continue; } // Resolve IDs (Find or Create
    simple logic) // Cost Center $stmtCC=$pdo->prepare("SELECT id FROM cost_centers WHERE company_id = ? AND name = ?");
    $stmtCC->execute([$companyId, $ccName]);
    $ccId = $stmtCC->fetchColumn();

    if (!$ccId) {
    $stmtInsCC = $pdo->prepare("INSERT INTO cost_centers (company_id, name) VALUES (?, ?)");
    $stmtInsCC->execute([$companyId, $ccName]);
    $ccId = $pdo->lastInsertId();
    }

    // Category
    $stmtCat = $pdo->prepare("SELECT id FROM categories WHERE company_id = ? AND name = ?");
    $stmtCat->execute([$companyId, $catName]);
    $catId = $stmtCat->fetchColumn();

    if (!$catId) {
    // Map nature to ENUM
    $validNatures = ['fixed', 'variable', 'discretionary'];
    if (!in_array($nature, $validNatures)) {
    // map pt-br
    if ($nature == 'fixa')
    $nature = 'fixed';
    elseif ($nature == 'variável' || $nature == 'variavel')
    $nature = 'variable';
    elseif ($nature == 'discricionária' || $nature == 'discricionaria')
    $nature = 'discretionary';
    else
    $nature = 'variable';
    }

    $stmtInsCat = $pdo->prepare("INSERT INTO categories (company_id, name, type) VALUES (?, ?, ?)");
    $stmtInsCat->execute([$companyId, $catName, $nature]);
    $catId = $pdo->lastInsertId();
    }

    // Insert Budget (Current Month/Year context assumed from user selection or current date)
    // For import, we might need Year/Month in CSV or use defaults. Let's use defaults for now (Jan 2026 as per screen).
    $month = isset($_POST['month']) ? intval($_POST['month']) : 1;
    $year = isset($_POST['year']) ? intval($_POST['year']) : 2026;

    try {
    // Insert or Update logic
    $stmtBudget = $pdo->prepare("INSERT INTO budgets (company_id, cost_center_id, category_id, fiscal_year, month,
    amount_budgeted, amount_realized, justification, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'draft')
    ON DUPLICATE KEY UPDATE amount_realized = VALUES(amount_realized), justification = VALUES(justification)");

    // Using Ref. Ant. as 'budgeted' (generic mapping for now) and Actual as 'realized'
    $stmtBudget->execute([$companyId, $ccId, $catId, $year, $month, $valRef, $valCur, $justification]);
    $imported++;
    } catch (Exception $e) {
    $errors++;
    $errorDetails[] = "Linha $row: Erro ao salvar no banco - " . $e->getMessage();
    }
    }

    fclose($handle);

    echo json_encode([
    'success' => true,
    'message' => "Processamento concluído. $imported linhas importadas, $errors erros.",
    'details' => ['imported' => $imported, 'errors' => $errors, 'error_messages' => $errorDetails ?? []]
    ]);
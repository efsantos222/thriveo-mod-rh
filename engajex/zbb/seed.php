<?php
// seed.php
require_once __DIR__ . '/config/db.php';

echo "Iniciando população de dados (seeding)...<br>";

try {
    // 1. Get the main company (created in install.php)
    $stmt = $pdo->query("SELECT id FROM companies LIMIT 1");
    $companyId = $stmt->fetchColumn();

    if (!$companyId) {
        die("Erro: Nenhuma empresa encontrada. Execute install.php primeiro.");
    }

    echo "Usando Empresa ID: $companyId<br>";

    // 2. Seed Cost Centers
    $costCenters = [
        ['name' => 'Administrativo', 'code' => 'CC01'],
        ['name' => 'Marketing e Vendas', 'code' => 'CC02'],
        ['name' => 'TI & Infraestrutura', 'code' => 'CC03'],
        ['name' => 'Operações', 'code' => 'CC04'],
        ['name' => 'Recursos Humanos', 'code' => 'CC05']
    ];

    foreach ($costCenters as $cc) {
        // Check if exists to avoid duplicates
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM cost_centers WHERE company_id = ? AND name = ?");
        $stmtCheck->execute([$companyId, $cc['name']]);
        if ($stmtCheck->fetchColumn() == 0) {
            $stmtInsert = $pdo->prepare("INSERT INTO cost_centers (company_id, name, code) VALUES (?, ?, ?)");
            $stmtInsert->execute([$companyId, $cc['name'], $cc['code']]);
            echo "Centro de Custo criado: {$cc['name']}<br>";
        }
    }

    // 3. Seed Categories
    $categories = [
        ['name' => 'Salários e Encargos', 'type' => 'fixed'],
        ['name' => 'Licenças de Software', 'type' => 'fixed'],
        ['name' => 'Campanhas de Marketing', 'type' => 'variable'],
        ['name' => 'Viagens Corporativas', 'type' => 'discretionary'],
        ['name' => 'Treinamentos', 'type' => 'discretionary'],
        ['name' => 'Material de Escritório', 'type' => 'variable']
    ];

    foreach ($categories as $cat) {
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE company_id = ? AND name = ?");
        $stmtCheck->execute([$companyId, $cat['name']]);
        if ($stmtCheck->fetchColumn() == 0) {
            $stmtInsert = $pdo->prepare("INSERT INTO categories (company_id, name, type) VALUES (?, ?, ?)");
            $stmtInsert->execute([$companyId, $cat['name'], $cat['type']]);
            echo "Categoria criada: {$cat['name']}<br>";
        }
    }

    // 4. Seed Budgets (Sample Data)
    // Fetch IDs for references
    $ccAdmin = $pdo->query("SELECT id FROM cost_centers WHERE code='CC01' LIMIT 1")->fetchColumn();
    $ccMkt = $pdo->query("SELECT id FROM cost_centers WHERE code='CC02' LIMIT 1")->fetchColumn();
    $ccTI = $pdo->query("SELECT id FROM cost_centers WHERE code='CC03' LIMIT 1")->fetchColumn();

    $catSalary = $pdo->query("SELECT id FROM categories WHERE name='Salários e Encargos' LIMIT 1")->fetchColumn();
    $catSoft = $pdo->query("SELECT id FROM categories WHERE name='Licenças de Software' LIMIT 1")->fetchColumn();
    $catMkt = $pdo->query("SELECT id FROM categories WHERE name='Campanhas de Marketing' LIMIT 1")->fetchColumn();

    $budgets = [
        [
            'cc_id' => $ccAdmin,
            'cat_id' => $catSalary,
            'month' => 1,
            'year' => 2026,
            'budgeted' => 50000.00,
            'realized' => 50000.00,
            'status' => 'approved'
        ],
        [
            'cc_id' => $ccTI,
            'cat_id' => $catSoft,
            'month' => 1,
            'year' => 2026,
            'budgeted' => 12000.00,
            'realized' => 12000.00,
            'status' => 'approved'
        ],
        [
            'cc_id' => $ccMkt,
            'cat_id' => $catMkt,
            'month' => 1,
            'year' => 2026,
            'budgeted' => 20000.00,
            'realized' => 25000.00,
            'status' => 'pending'
        ],
        [
            'cc_id' => $ccMkt,
            'cat_id' => $catSalary,
            'month' => 1,
            'year' => 2026,
            'budgeted' => 30000.00,
            'realized' => 30000.00,
            'status' => 'approved'
        ]
    ];

    foreach ($budgets as $b) {
        if ($b['cc_id'] && $b['cat_id']) {
            // Check existence
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM budgets WHERE company_id = ? AND cost_center_id = ? AND category_id = ? AND month = ? AND fiscal_year = ?");
            $stmtCheck->execute([$companyId, $b['cc_id'], $b['cat_id'], $b['month'], $b['year']]);

            if ($stmtCheck->fetchColumn() == 0) {
                $stmtInsert = $pdo->prepare("INSERT INTO budgets (company_id, cost_center_id, category_id, fiscal_year, month, amount_budgeted, amount_realized, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmtInsert->execute([$companyId, $b['cc_id'], $b['cat_id'], $b['year'], $b['month'], $b['budgeted'], $b['realized'], $b['status']]);
                echo "Orçamento inserido: CC {$b['cc_id']} - R$ {$b['budgeted']}<br>";
            }
        }
    }

} catch (PDOException $e) {
    die("Erro ao popular dados: " . $e->getMessage());
}

echo "Dados populados com sucesso!";
?>
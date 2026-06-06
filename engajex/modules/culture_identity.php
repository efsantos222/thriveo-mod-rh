<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../config.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$companyId = $_SESSION['company_id'] ?? 1;
$role = $_SESSION['role'];
$isManager = in_array($role, ['admin', 'manager', 'responsible']);

if (!$isManager) {
    die("Acesso restrito a gestores.");
}

$message = '';

// --- SAVE ACTION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'save_identity') {
        $purpose = $_POST['purpose'];
        $mission = $_POST['mission'];
        $vision = $_POST['vision'];
        $values = $_POST['values'];
        $principles = $_POST['principles'];

        // Check if exists
        $check = $pdo->prepare("SELECT id FROM culture_identity WHERE company_id = ?");
        $check->execute([$companyId]);

        if ($check->fetch()) {
            $stmt = $pdo->prepare("UPDATE culture_identity SET purpose=?, mission=?, vision=?, values_text=?, principles=? WHERE company_id=?");
            $stmt->execute([$purpose, $mission, $vision, $values, $principles, $companyId]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO culture_identity (purpose, mission, vision, values_text, principles, company_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$purpose, $mission, $vision, $values, $principles, $companyId]);
        }
        $message = "Identidade salva com sucesso!";
    }

    if ($_POST['action'] === 'import_v2mom') {
        // Fetch V2MOM Data
        // Get latest V2MOM ID
        $vmStmt = $pdo->prepare("SELECT id FROM v2mom WHERE company_id = ? ORDER BY id DESC LIMIT 1");
        $vmStmt->execute([$companyId]);
        $v2momId = $vmStmt->fetchColumn();

        if ($v2momId) {
            $vStmt = $pdo->prepare("SELECT description FROM v2mom_vision WHERE v2mom_id = ?");
            $vStmt->execute([$v2momId]);
            $vVision = $vStmt->fetchColumn();

            $valStmt = $pdo->prepare("SELECT description FROM v2mom_values WHERE v2mom_id = ?");
            $valStmt->execute([$v2momId]);
            $vValues = $valStmt->fetchAll();

            $valuesText = "";
            foreach ($vValues as $v) {
                $valuesText .= "- " . $v['description'] . "\n";
            }
        } else {
            $vVision = "";
            $valuesText = "";
            $message = "V2MOM não encontrado.";
        }

        // Update Identity
        // We only overwrite Vision and Values, keep others keys
        $check = $pdo->prepare("SELECT id FROM culture_identity WHERE company_id = ?");
        $check->execute([$companyId]);
        if ($check->fetch()) {
            $pdo->prepare("UPDATE culture_identity SET vision=?, values_text=? WHERE company_id=?")->execute([$vVision, $valuesText, $companyId]);
        } else {
            $pdo->prepare("INSERT INTO culture_identity (vision, values_text, company_id) VALUES (?, ?, ?)")->execute([$vVision, $valuesText, $companyId]);
        }
        $message = "Dados importados do V2MOM!";
    }
}

// --- FETCH ---
$stmt = $pdo->prepare("SELECT * FROM culture_identity WHERE company_id = ?");
$stmt->execute([$companyId]);
$identity = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Identidade Organizacional - Cultura</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="app-layout">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h1>Identidade Organizacional</h1>
                <a href="board_culture.php" class="btn btn-outline">
                    < Voltar</a>
            </div>

            <?php if ($message): ?>
                <div style="margin:1rem 0; color:#34d399; background:rgba(16,185,129,0.1);
            padding:1rem; border-radius:0.5rem;"><?php echo $message; ?></div><?php endif; ?>

            <div
                style="background:rgba(255,255,255,0.05); padding:2rem; border-radius:1rem; border:1px solid var(--glass-border);">
                <div style="display:flex; justify-content:flex-end; margin-bottom:1rem;">
                    <form method="POST">
                        <input type="hidden" name="action" value="import_v2mom">
                        <button class="btn btn-outline" style="font-size:0.8rem;">📥 Importar do V2MOM</button>
                    </form>
                </div>

                <form method="POST">
                    <input type="hidden" name="action" value="save_identity">

                    <label style="display:block; margin-top:1rem;">Propósito (Why)</label>
                    <textarea name="purpose" rows="2"
                        style="width:100%; padding:0.8rem; background:#1e293b; border:1px solid var(--glass-border); color:white; border-radius:0.5rem;"><?php echo htmlspecialchars($identity['purpose'] ?? ''); ?></textarea>

                    <label style="display:block; margin-top:1rem;">Missão (What)</label>
                    <textarea name="mission" rows="2"
                        style="width:100%; padding:0.8rem; background:#1e293b; border:1px solid var(--glass-border); color:white; border-radius:0.5rem;"><?php echo htmlspecialchars($identity['mission'] ?? ''); ?></textarea>

                    <label style="display:block; margin-top:1rem;">Visão (Where)</label>
                    <textarea name="vision" rows="2"
                        style="width:100%; padding:0.8rem; background:#1e293b; border:1px solid var(--glass-border); color:white; border-radius:0.5rem;"><?php echo htmlspecialchars($identity['vision'] ?? ''); ?></textarea>

                    <label style="display:block; margin-top:1rem;">Valores (How)</label>
                    <textarea name="values" rows="4"
                        style="width:100%; padding:0.8rem; background:#1e293b; border:1px solid var(--glass-border); color:white; border-radius:0.5rem;"><?php echo htmlspecialchars($identity['values_text'] ?? ''); ?></textarea>

                    <label style="display:block; margin-top:1rem;">Princípios (Non-negotiables)</label>
                    <textarea name="principles" rows="4"
                        style="width:100%; padding:0.8rem; background:#1e293b; border:1px solid var(--glass-border); color:white; border-radius:0.5rem;"><?php echo htmlspecialchars($identity['principles'] ?? ''); ?></textarea>

                    <button class="btn btn-primary" style="margin-top:2rem; width:100%;">Salvar Identidade</button>
                </form>
            </div>
        </main>
    </div>
</body>

</html>
<?php
require_once '../config/config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('../login.php');
}

$empresa_id = $_SESSION['empresa_id'];
$message = '';

// Handle Post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $proposito = $_POST['proposito'];
    $missao = $_POST['missao'];
    $visao = $_POST['visao'];
    $valores = $_POST['valores'];

    // Simple AI generation of "Culture" text based on these inputs could happen here, 
    // but for now we just save them.
    $cultura = "Baseado em: $missao, $visao, $valores."; // Placeholder for generated culture

    // Upsert
    $check = $pdo->prepare("SELECT id FROM company_intelelct WHERE empresa_id = ?");
    $check->execute([$empresa_id]);

    if ($check->fetch()) {
        $stmt = $pdo->prepare("UPDATE company_intelelct SET proposito=?, missao=?, visao=?, valores=?, cultura_organizacional=? WHERE empresa_id=?");
        $stmt->execute([$proposito, $missao, $visao, $valores, $cultura, $empresa_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO company_intelelct (empresa_id, proposito, missao, visao, valores, cultura_organizacional) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$empresa_id, $proposito, $missao, $visao, $valores, $cultura]);
    }
    $message = "Identidade Organizacional salva com sucesso!";
}

// Fetch
$stmt = $pdo->prepare("SELECT * FROM company_intelelct WHERE empresa_id = ?");
$stmt->execute([$empresa_id]);
$data = $stmt->fetch() ?: ['proposito' => '', 'missao' => '', 'visao' => '', 'valores' => ''];
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Cultura & Identidade</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content">
            <header class="topbar">
                <h2>Identidade Organizacional</h2>
            </header>
            <div class="page-content">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Definição da Cultura</h3>
                    </div>
                    <?php if ($message): ?>
                        <div
                            style="padding:1rem; background:#dcfce7; color:#166534; border-radius:0.5rem; margin-bottom:1rem;">
                            <?php echo $message; ?></div><?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label>Propósito</label>
                            <textarea name="proposito" class="form-control"
                                rows="3"><?php echo htmlspecialchars($data['proposito']); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Missão</label>
                            <textarea name="missao" class="form-control"
                                rows="3"><?php echo htmlspecialchars($data['missao']); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Visão</label>
                            <textarea name="visao" class="form-control"
                                rows="3"><?php echo htmlspecialchars($data['visao']); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Princípios & Valores</label>
                            <textarea name="valores" class="form-control"
                                rows="4"><?php echo htmlspecialchars($data['valores']); ?></textarea>
                        </div>

                        <div style="background:#f8fafc; padding:1rem; border-radius:0.5rem; margin-top:1rem;">
                            <p style="font-size:0.9rem; color:#666;"><i class="fa-solid fa-lightbulb"
                                    style="color:#eab308;"></i> Dica: O "AI Onboarding Buddy" usará estas informações
                                para treinar e responder dúvidas dos novos colaboradores sobre a empresa.</p>
                        </div>

                        <div style="margin-top:2rem;">
                            <button type="submit" class="btn btn-primary">Salvar Identidade</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>

</html>
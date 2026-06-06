<?php
require_once '../config.php';
if (!isAdmin()) {
    header("Location: ../login.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Company Data
    $compName = $_POST['comp_name'];
    $cnpj = $_POST['cnpj'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];

    // Responsible Data
    $respName = $_POST['resp_name'];
    $respEmail = $_POST['resp_email'];
    $respPhone = $_POST['resp_phone'];
    $respCargo = $_POST['resp_cargo'];
    $respArea = $_POST['resp_area'];
    $respPass = $_POST['resp_password'];

    try {
        $pdo->beginTransaction();

        // 1. Insert Company
        $stmt = $pdo->prepare("INSERT INTO companies (name, cnpj, address, phone, responsible_name, responsible_email, responsible_phone, responsible_role, responsible_area) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$compName, $cnpj, $address, $phone, $respName, $respEmail, $respPhone, $respCargo, $respArea]);
        $companyId = $pdo->lastInsertId();

        // 2. Insert User (Responsible)
        $hash = password_hash($respPass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (company_id, name, email, password, role, cargo, area) VALUES (?, ?, ?, ?, 'responsible', ?, ?)");
        $stmt->execute([$companyId, $respName, $respEmail, $hash, $respCargo, $respArea]);

        $pdo->commit();
        header("Location: companies.php");
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Erro ao cadastrar: " . $e->getMessage();
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            $error = "E-mail já cadastrado.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Nova Empresa - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        /* Styles moved to assets/css/style.css */
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <h1>Cadastrar Nova Empresa</h1>
            <?php if ($error): ?>
                <div style="color: #fca5a5; margin-bottom: 1rem;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="card" style="max-width: 800px;">
                <h3 style="margin-bottom: 1rem; color: var(--primary-color);">Dados da Empresa</h3>
                <div class="form-group">
                    <label class="form-label">Nome da Empresa</label>
                    <input type="text" name="comp_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">CNPJ</label>
                    <input type="text" name="cnpj" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Endereço Completo</label>
                    <input type="text" name="address" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Telefone</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>

                <h3 style="margin-bottom: 1rem; margin-top: 2rem; color: var(--secondary-color);">Dados do Responsável
                </h3>
                <div class="form-group">
                    <label class="form-label">Nome Completo</label>
                    <input type="text" name="resp_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="resp_email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Senha de Acesso</label>
                    <input type="password" name="resp_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Telefone</label>
                    <input type="text" name="resp_phone" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Cargo</label>
                    <input type="text" name="resp_cargo" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Área</label>
                    <input type="text" name="resp_area" class="form-control" required>
                </div>

                <div style="margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">Cadastrar Empresa & Responsável</button>
                    <a href="companies.php" class="btn btn-outline">Cancelar</a>
                </div>
            </form>
        </main>
    </div>
</body>

</html>
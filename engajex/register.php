<?php
require_once 'config.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name = $_POST['company_name'] ?? '';
    $cnpj = $_POST['cnpj'] ?? '';
    $address = $_POST['address'] ?? '';
    $area_atuacao = $_POST['area_atuacao'] ?? '';
    $employee_count = intval($_POST['employee_count'] ?? 0);
    $responsible_name = $_POST['responsible_name'] ?? '';
    $responsible_email = $_POST['responsible_email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($company_name) && !empty($cnpj) && !empty($responsible_email) && !empty($password)) {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$responsible_email]);
            if ($stmt->fetch()) {
                $error = "E-mail já cadastrado.";
            } else {
                $pdo->beginTransaction();

                // 1. Insert Company
                $trial_ends_at = date('Y-m-d H:i:s', strtotime('+15 days'));
                $stmt = $pdo->prepare("INSERT INTO companies (name, cnpj, address, area_atuacao, employee_count, responsible_name, responsible_email, phone, responsible_phone, responsible_role, responsible_area, trial_ends_at, subscription_status) VALUES (?, ?, ?, ?, ?, ?, ?, '', '', '', '', ?, 'trial')");
                $stmt->execute([$company_name, $cnpj, $address, $area_atuacao, $employee_count, $responsible_name, $responsible_email, $trial_ends_at]);
                $company_id = $pdo->lastInsertId();

                // 2. Insert User (Responsible)
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (company_id, name, email, password, role) VALUES (?, ?, ?, ?, 'responsible')");
                $stmt->execute([$company_id, $responsible_name, $responsible_email, $hashed_password]);

                $pdo->commit();
                $message = "Cadastro realizado com sucesso! Você tem 15 dias de acesso gratuito.";
                header("Location: login.php?registered=1");
                exit;
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = "Erro ao realizar cadastro: " . $e->getMessage();
        }
    } else {
        $error = "Preencha todos os campos obrigatórios.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Empresa - TestProf Engaja</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 2rem 0;">

    <div class="container" style="max-width: 600px; width: 100%;">
        <div class="card">
            <h2 style="margin-bottom: 2rem; text-align: center;">Cadastro de Empresa</h2>
            
            <?php if ($error): ?>
                <div style="background: rgba(239, 68, 68, 0.2); color: #fca5a5; padding: 0.75rem; border-radius: 0.5rem; margin-bottom: 1rem; border: 1px solid rgba(239, 68, 68, 0.3);">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Nome da Empresa</label>
                        <input type="text" name="company_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">CNPJ</label>
                        <input type="text" name="cnpj" class="form-control" placeholder="00.000.000/0000-00" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Endereço Completo</label>
                    <input type="text" name="address" class="form-control" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Área de Atuação</label>
                        <input type="text" name="area_atuacao" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Qtd. de Empregados</label>
                        <input type="number" name="employee_count" class="form-control" required>
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--glass-border); margin: 1rem 0 2rem;">
                <h3 style="margin-bottom: 1.5rem; font-size: 1.1rem; color: var(--secondary-color);">Dados do Responsável</h3>

                <div class="form-group">
                    <label class="form-label">Nome Completo</label>
                    <input type="text" name="responsible_name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">E-mail Corporativo</label>
                    <input type="email" name="responsible_email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Senha de Acesso</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Cadastrar e Começar Teste de 15 Dias</button>
            </form>

            <p style="margin-top: 1.5rem; font-size: 0.9rem; text-align: center; color: var(--text-muted);">
                Já tem cadastro? <a href="login.php" style="color: var(--secondary-color); text-decoration: none;">Faça login</a>
            </p>
        </div>
    </div>

</body>
</html>

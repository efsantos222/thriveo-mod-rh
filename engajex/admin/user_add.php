<?php
require_once '../config.php';
if (!isAdmin()) {
    header("Location: ../login.php");
    exit;
}

$companies = $pdo->query("SELECT id, name FROM companies ORDER BY name")->fetchAll();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $company_id = $_POST['company_id']; // Can be null if admin? usually admin has no company or company 1.
    $role = $_POST['role'];
    $cargo = $_POST['cargo'];
    $area = $_POST['area'];

    if ($role != 'admin' && empty($company_id)) {
        $error = "Selecione uma empresa para usuários não-admin.";
    } else {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, company_id, role, cargo, area) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hash, $company_id ?: null, $role, $cargo, $area]);

            header("Location: users.php");
            exit;
        } catch (PDOException $e) {
            $error = "Erro ao cadastrar: " . $e->getMessage();
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $error = "E-mail já cadastrado.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Novo Usuário - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
</head>

<body>
    <div class="app-layout">
        <?php include 'includes/sidebar.php'; ?>
        <main class="main-content">
            <h1>Cadastrar Novo Usuário</h1>
            <?php if ($error): ?>
                <div
                    style="color: #fca5a5; margin-bottom: 1rem; background:rgba(239,68,68,0.1); padding:1rem; border-radius:0.5rem;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="card" style="max-width: 600px;">
                <div class="form-group">
                    <label class="form-label">Nome Completo</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Senha</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Empresa</label>
                    <select name="company_id" class="form-control">
                        <option value="">Selecione...</option>
                        <?php foreach ($companies as $c): ?>
                            <option value="<?php echo $c['id']; ?>">
                                <?php echo htmlspecialchars($c['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Perfil de Acesso (Role)</label>
                    <select name="role" class="form-control" required>
                        <option value="responsible">Responsável (Gestor)</option>
                        <option value="company_admin">Admin da Empresa</option>
                        <option value="manager">Gerente</option>
                        <option value="employee">Colaborador</option>
                        <option value="admin">Administrador Master</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Cargo</label>
                    <input type="text" name="cargo" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Área</label>
                    <input type="text" name="area" class="form-control">
                </div>

                <div style="margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">Salvar Usuário</button>
                    <a href="users.php" class="btn btn-outline">Cancelar</a>
                </div>
            </form>
        </main>
    </div>
</body>

</html>
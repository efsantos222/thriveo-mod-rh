<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkAuth('admin');

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $companyName = $_POST['company_name'];
    $cnpj = $_POST['cnpj'];
    $sector = $_POST['sector']; // New Field
    $respName = $_POST['resp_name'];
    $respEmail = $_POST['resp_email'];
    $respPass = $_POST['resp_pass'];

    if ($companyName && $respEmail && $respPass) {
        try {
            $pdo->beginTransaction();

            // Create Company
            $stmt = $pdo->prepare("INSERT INTO companies (name, cnpj, sector) VALUES (?, ?, ?)");
            $stmt->execute([$companyName, $cnpj, $sector]);
            $companyId = $pdo->lastInsertId();

            // Create Responsible User
            $hashedPass = password_hash($respPass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (company_id, name, email, password, role) VALUES (?, ?, ?, ?, 'responsible')");
            $stmt->execute([$companyId, $respName, $respEmail, $hashedPass]);

            $pdo->commit();
            $success = "Empresa e Responsável cadastrados com sucesso!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Erro ao cadastrar: " . $e->getMessage();
        }
    } else {
        $error = "Preencha todos os campos obrigatórios.";
    }
}

// List Companies
$companies = $pdo->query("
    SELECT c.*, u.name as resp_name, u.email as resp_email 
    FROM companies c 
    LEFT JOIN users u ON u.company_id = c.id AND u.role = 'responsible'
    ORDER BY c.created_at DESC
")->fetchAll();

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Gerenciar Empresas - MLPT</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: #1e293b;
            padding: 20px;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }

        .content {
            flex: 1;
            padding: 40px;
        }

        .sidebar .logo {
            margin-bottom: 40px;
            text-align: center;
        }

        .menu-item {
            display: block;
            padding: 12px 16px;
            color: var(--text-dim);
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 5px;
        }

        .menu-item:hover,
        .menu-item.active {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
        }

        .menu-item i {
            margin-right: 10px;
            width: 20px;
        }

        .form-card {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 40px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-dim);
        }

        .form-control {
            width: 100%;
            padding: 10px;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        th {
            color: var(--text-dim);
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="admin-layout">
        <div class="sidebar">
            <div class="logo">MLPT Admin</div>
            <a href="index.php" class="menu-item"><i class="fa-solid fa-house"></i> Home</a>
            <a href="companies.php" class="menu-item active"><i class="fa-solid fa-building"></i> Empresas</a>
            <a href="settings.php" class="menu-item"><i class="fa-solid fa-gear"></i> Configurações</a>
            <a href="../logout.php" class="menu-item"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
        </div>
        <div class="content">
            <h2>Gerenciar Empresas</h2>

            <?php if ($success): ?>
                <div style="color: #10b981; margin-bottom: 20px;"><?php echo $success; ?></div> <?php endif; ?>
            <?php if ($error): ?>
                <div style="color: #ef4444; margin-bottom: 20px;"><?php echo $error; ?></div> <?php endif; ?>

            <div class="form-card">
                <h3 style="margin-bottom: 20px;">Nova Empresa</h3>
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Nome da Empresa</label>
                            <input type="text" name="company_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Setor</label>
                            <select name="sector" class="form-control">
                                <option value="Tecnologia">Tecnologia</option>
                                <option value="Financeiro">Financeiro</option>
                                <option value="Saúde">Saúde</option>
                                <option value="Varejo">Varejo</option>
                                <option value="Indústria">Indústria</option>
                                <option value="Serviços">Serviços</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>CNPJ</label>
                            <input type="text" name="cnpj" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Nome do Responsável</label>
                            <input type="text" name="resp_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>E-mail do Responsável</label>
                            <input type="email" name="resp_email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Senha Provisória</label>
                            <input type="text" name="resp_pass" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-top: 20px;">Cadastrar</button>
                </form>
            </div>

            <div class="list-card">
                <h3>Empresas Cadastradas</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Empresa</th>
                            <th>Setor</th>
                            <th>CNPJ</th>
                            <th>Responsável</th>
                            <th>E-mail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($companies as $comp): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($comp['name']); ?></td>
                                <td><?php echo htmlspecialchars($comp['sector'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($comp['cnpj']); ?></td>
                                <td><?php echo htmlspecialchars($comp['resp_name']); ?></td>
                                <td><?php echo htmlspecialchars($comp['resp_email']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>
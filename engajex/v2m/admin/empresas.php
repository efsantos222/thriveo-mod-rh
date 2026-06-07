<?php
session_start();
require_once dirname(__DIR__) . '/config.php';

if (!isAdmin()) {
    redirect('login.php');
}

$message = '';
$action = $_GET['action'] ?? 'list';

// Create/Edit/Delete Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_company'])) {
        $razao = $_POST['razao_social'];
        $cnpj = $_POST['cnpj'];
        $segmento = $_POST['segmento'];
        $porte = $_POST['porte'];

        $stmt = $pdo->prepare("INSERT INTO empresas (razao_social, cnpj, segmento, porte) VALUES (?, ?, ?, ?)");
        try {
            $stmt->execute([$razao, $cnpj, $segmento, $porte]);
            $message = "Empresa cadastrada com sucesso!";
        } catch (PDOException $e) {
            $message = "Erro ao cadastrar: " . $e->getMessage();
        }
    }

    // Add logic for update/delete as needed
}

// Fetch Companies
$stmt = $pdo->query("SELECT * FROM empresas ORDER BY data_cadastro DESC");
$empresas = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Empresas - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            padding-top: 80px;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 80px;
            bottom: 0;
            width: 250px;
            background: rgba(30, 41, 59, 0.9);
            border-right: 1px solid var(--glass-border);
            padding: 20px;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid var(--glass-border);
            color: #cbd5e1;
        }

        th {
            background: rgba(255, 255, 255, 0.05);
            color: white;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 80px;
            bottom: 0;
            width: 250px;
            background: rgba(30, 41, 59, 0.9);
            border-right: 1px solid var(--glass-border);
            padding: 20px;
        }

        .admin-nav li {
            margin-bottom: 10px;
        }

        .admin-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 6px;
            color: #cbd5e1;
        }

        .admin-nav a:hover,
        .admin-nav a.active {
            background: var(--primary-color);
            color: white;
        }
    </style>
</head>

<body>

    <header class="glass"
        style="position: fixed; top: 0; width: 100%; height: 80px; z-index: 50; display: flex; align-items: center; padding: 0 20px; justify-content: space-between;">
        <div class="logo"><i class="ph ph-strategy"></i> V2MOM ADMIN</div>
        <a href="logout.php" class="btn btn-outline" style="padding: 5px 15px;">Sair</a>
    </header>

    <aside class="sidebar">
        <ul class="admin-nav">
            <li><a href="dashboard.php"><i class="ph ph-squares-four"></i> Dashboard</a></li>
            <li><a href="empresas.php" class="active"><i class="ph ph-buildings"></i> Empresas</a></li>
            <li><a href="responsaveis.php"><i class="ph ph-users"></i> Responsáveis</a></li>
            <li><a href="config_ia.php"><i class="ph ph-robot"></i> Configuração IA</a></li>
            <li><a href="logs.php"><i class="ph ph-scroll"></i> Logs & Auditoria</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h1>Gestão de Empresas</h1>

        <?php if ($message): ?>
            <div
                style="padding: 10px; background: rgba(16, 185, 129, 0.2); color: #6ee7b7; border-radius: 6px; margin: 20px 0;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="glass" style="padding: 20px; margin-top: 20px;">
            <h3>Nova Empresa</h3>
            <form method="POST"
                style="margin-top: 15px; display: grid; gap: 15px; grid-template-columns: repeat(2, 1fr);">
                <input type="hidden" name="create_company" value="1">
                <input type="text" name="razao_social" placeholder="Razão Social" required
                    style="padding: 10px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px;">
                <input type="text" name="cnpj" placeholder="CNPJ" class="cnpj-mask" required
                    style="padding: 10px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px;">
                <select name="segmento"
                    style="padding: 10px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px;">
                    <option value="">Selecione o Segmento</option>
                    <option value="Tecnologia">Tecnologia</option>
                    <option value="Varejo">Varejo</option>
                    <option value="Saúde">Saúde</option>
                    <option value="Serviços Financeiros">Serviços Financeiros</option>
                    <option value="Indústria">Indústria</option>
                    <option value="Educação">Educação</option>
                    <option value="Outro">Outro</option>
                </select>
                <select name="porte"
                    style="padding: 10px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px;">
                    <option value="">Selecione o Porte</option>
                    <option value="Pequeno">Pequeno (até 50 func.)</option>
                    <option value="Médio">Médio (50-500 func.)</option>
                    <option value="Grande">Grande (+500 func.)</option>
                </select>
                <div style="grid-column: span 2;">
                    <button type="submit" class="btn btn-primary">Cadastrar Empresa</button>
                </div>
            </form>
        </div>

        <div class="glass" style="padding: 20px; margin-top: 30px;">
            <h3>Empresas Cadastradas</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Razão Social</th>
                        <th>CNPJ</th>
                        <th>Segmento</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($empresas as $e): ?>
                        <tr>
                            <td><?= $e['id'] ?></td>
                            <td><?= htmlspecialchars($e['razao_social']) ?></td>
                            <td><?= htmlspecialchars($e['cnpj']) ?></td>
                            <td><?= htmlspecialchars($e['segmento']) ?></td>
                            <td>
                                <span
                                    style="padding: 2px 8px; border-radius: 10px; background: <?= $e['status'] == 'ativo' ? 'rgba(16,185,129,0.2)' : 'rgba(239,68,68,0.2)' ?>; color: <?= $e['status'] == 'ativo' ? '#6ee7b7' : '#fca5a5' ?>">
                                    <?= ucfirst($e['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="#" style="color: var(--primary-color);">Editar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>

</html>
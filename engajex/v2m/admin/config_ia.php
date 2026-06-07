<?php
session_start();
require_once dirname(__DIR__) . '/config.php';

if (!isAdmin()) {
    redirect('login.php');
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $empresa_id = $_POST['empresa_id'];
    $api_key = $_POST['api_key'];
    $model = $_POST['modelo'];
    $temp = $_POST['temperatura'];

    try {
        // Check if config exists
        $stmt_check = $pdo->prepare("SELECT id FROM configuracoes_ia WHERE id_empresa = ?");
        $stmt_check->execute([$empresa_id]);

        if ($stmt_check->fetch()) {
            $stmt = $pdo->prepare("UPDATE configuracoes_ia SET api_key_openai = ?, modelo_preferido = ?, temperatura = ? WHERE id_empresa = ?");
            $stmt->execute([$api_key, $model, $temp, $empresa_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO configuracoes_ia (id_empresa, api_key_openai, modelo_preferido, temperatura) VALUES (?, ?, ?, ?)");
            $stmt->execute([$empresa_id, $api_key, $model, $temp]);
        }
        $message = "Configurações salvas!";
    } catch (PDOException $e) {
        $message = "Erro: " . $e->getMessage();
    }
}

// Fetch all companies and their configs
$stmt = $pdo->query("
    SELECT e.id, e.razao_social, c.api_key_openai, c.modelo_preferido, c.temperatura 
    FROM empresas e 
    LEFT JOIN configuracoes_ia c ON e.id = c.id_empresa 
    WHERE e.status = 'ativo'
    ORDER BY e.razao_social
");
$configs = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações IA - Admin</title>
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

        .config-card {
            background: rgba(30, 41, 59, 0.5);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid var(--glass-border);
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
            <li><a href="empresas.php"><i class="ph ph-buildings"></i> Empresas</a></li>
            <li><a href="responsaveis.php"><i class="ph ph-users"></i> Responsáveis</a></li>
            <li><a href="config_ia.php" class="active"><i class="ph ph-robot"></i> Configuração IA</a></li>
            <li><a href="logs.php"><i class="ph ph-scroll"></i> Logs & Auditoria</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h1>Configuração de IA por Empresa</h1>

        <?php if ($message): ?>
            <div
                style="padding: 15px; background: rgba(16, 185, 129, 0.2); color: #6ee7b7; border-radius: 6px; margin: 20px 0;">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <div style="margin-top: 20px;">
            <?php foreach ($configs as $c): ?>
                <div class="config-card">
                    <h3><?= htmlspecialchars($c['razao_social']) ?></h3>
                    <form method="POST"
                        style="margin-top: 15px; display: grid; gap: 15px; grid-template-columns: repeat(3, 1fr) auto;">
                        <input type="hidden" name="empresa_id" value="<?= $c['id'] ?>">

                        <div>
                            <label style="display: block; font-size: 0.8rem; margin-bottom: 5px; color: #94a3b8;">API Key
                                OpenAI</label>
                            <input type="password" name="api_key"
                                value="<?= htmlspecialchars($c['api_key_openai'] ?? '') ?>" placeholder="sk-..."
                                style="width: 100%; padding: 8px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px;">
                        </div>

                        <div>
                            <label
                                style="display: block; font-size: 0.8rem; margin-bottom: 5px; color: #94a3b8;">Modelo</label>
                            <select name="modelo"
                                style="width: 100%; padding: 8px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px;">
                                <option value="gpt-4o" <?= ($c['modelo_preferido'] ?? '') == 'gpt-4o' ? 'selected' : '' ?>>GPT-4o
                                </option>
                                <option value="gpt-4o-mini" <?= ($c['modelo_preferido'] ?? '') == 'gpt-4o-mini' ? 'selected' : '' ?>>GPT-4o Mini</option>
                                <option value="gpt-3.5-turbo" <?= ($c['modelo_preferido'] ?? '') == 'gpt-3.5-turbo' ? 'selected' : '' ?>>GPT-3.5 Turbo</option>
                            </select>
                        </div>

                        <div>
                            <label
                                style="display: block; font-size: 0.8rem; margin-bottom: 5px; color: #94a3b8;">Temperatura
                                (0-1)</label>
                            <input type="number" step="0.1" min="0" max="1" name="temperatura"
                                value="<?= htmlspecialchars($c['temperatura'] ?? '0.7') ?>"
                                style="width: 100%; padding: 8px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px;">
                        </div>

                        <div style="display: flex; align-items: flex-end;">
                            <button type="submit" class="btn btn-primary" style="padding: 9px 20px;">Salvar</button>
                        </div>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

</body>

</html>
<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkAuth('admin');

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $apiKey = $_POST['api_key'];

    // Check if exists
    $stmt = $pdo->prepare("SELECT id FROM settings WHERE setting_key = 'openai_api_key'");
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $sql = "UPDATE settings SET setting_value = ? WHERE setting_key = 'openai_api_key'";
    } else {
        $sql = "INSERT INTO settings (setting_value, setting_key) VALUES (?, 'openai_api_key')";
    }

    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$apiKey])) {
        $message = "Chave API atualizada com sucesso.";
    } else {
        $message = "Erro ao atualizar chave.";
    }
}

// Get current key
$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'openai_api_key'");
$stmt->execute();
$currentKey = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Configurações - MLPT</title>
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
            max-width: 600px;
            border: 1px solid rgba(255, 255, 255, 0.05);
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
    </style>
</head>

<body>
    <div class="admin-layout">
        <div class="sidebar">
            <div class="logo">MLPT Admin</div>
            <a href="index.php" class="menu-item"><i class="fa-solid fa-house"></i> Home</a>
            <a href="companies.php" class="menu-item"><i class="fa-solid fa-building"></i> Empresas</a>
            <a href="settings.php" class="menu-item active"><i class="fa-solid fa-gear"></i> Configurações</a>
            <a href="../logout.php" class="menu-item"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
        </div>
        <div class="content">
            <h2>Configurações do Sistema</h2>

            <?php if ($message): ?>
                <div style="color: #10b981; margin-bottom: 20px;"><?php echo $message; ?></div> <?php endif; ?>

            <div class="form-card">
                <form method="POST">
                    <div class="form-group">
                        <label>OpenAI API Key</label>
                        <input type="password" name="api_key" class="form-control"
                            value="<?php echo htmlspecialchars($currentKey ?: ''); ?>" required placeholder="sk-...">
                        <small style="color: var(--text-dim); display: block; margin-top: 5px;">Necessária para análise
                            de documentos com IA.</small>
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-top: 20px;">Salvar Chave</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
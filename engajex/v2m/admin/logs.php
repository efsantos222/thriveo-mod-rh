<?php
session_start();
require_once dirname(__DIR__) . '/config.php';

if (!isAdmin()) {
    redirect('login.php');
}

// Fetch logs logic
// Note: In production this should have pagination
try {
    $stmt = $pdo->query("SELECT l.*, r.nome as nome_responsavel, a.nome as nome_admin 
                         FROM logs_acesso l 
                         LEFT JOIN responsaveis r ON l.id_usuario = r.id AND l.tipo_usuario = 'responsavel'
                         LEFT JOIN administradores a ON l.id_usuario = a.id AND l.tipo_usuario = 'admin'
                         ORDER BY l.data_hora DESC LIMIT 100");
    $logs = $stmt->fetchAll();
} catch (PDOException $e) {
    $logs = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { padding-top: 80px; }
        .sidebar { position: fixed; left: 0; top: 80px; bottom: 0; width: 250px; background: rgba(30, 41, 59, 0.9); border-right: 1px solid var(--glass-border); padding: 20px; }
        .main-content { margin-left: 250px; padding: 20px; }
        .admin-nav li { margin-bottom: 10px; }
        .admin-nav a { display: flex; align-items: center; gap: 10px; padding: 10px; border-radius: 6px; color: #cbd5e1; }
        .admin-nav a:hover, .admin-nav a.active { background: var(--primary-color); color: white; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.9rem; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid var(--glass-border); color: #cbd5e1; }
    </style>
</head>
<body>

    <header class="glass" style="position: fixed; top: 0; width: 100%; height: 80px; z-index: 50; display: flex; align-items: center; padding: 0 20px; justify-content: space-between;">
        <div class="logo"><i class="ph ph-strategy"></i> V2MOM ADMIN</div>
        <a href="logout.php" class="btn btn-outline" style="padding: 5px 15px;">Sair</a>
    </header>

    <aside class="sidebar">
        <ul class="admin-nav">
            <li><a href="dashboard.php"><i class="ph ph-squares-four"></i> Dashboard</a></li>
            <li><a href="empresas.php"><i class="ph ph-buildings"></i> Empresas</a></li>
            <li><a href="responsaveis.php"><i class="ph ph-users"></i> Responsáveis</a></li>
            <li><a href="config_ia.php"><i class="ph ph-robot"></i> Configuração IA</a></li>
            <li><a href="logs.php" class="active"><i class="ph ph-scroll"></i> Logs & Auditoria</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h1>Logs de Acesso</h1>
        
        <div class="glass" style="padding: 20px; margin-top: 20px;">
            <table>
                <thead>
                    <tr>
                        <th>Data/Hora</th>
                        <th>Usuário</th>
                        <th>Tipo</th>
                        <th>Ação</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="5" style="text-align: center;">Nenhum log encontrado.</td></tr>
                    <?php else: ?>
                        <?php foreach($logs as $l): ?>
                        <tr>
                            <td><?= $l['data_hora'] ?></td>
                            <td><?= htmlspecialchars($l['nome_admin'] ?? $l['nome_responsavel'] ?? 'Desconhecido') ?></td>
                            <td><?= ucfirst($l['tipo_usuario']) ?></td>
                            <td><?= htmlspecialchars($l['acao']) ?></td>
                            <td><?= htmlspecialchars($l['ip']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>

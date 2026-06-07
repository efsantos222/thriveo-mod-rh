<?php
session_start();
require_once '../config.php';

// Verificar se está logado e é administrador
if (!checkSession() || getUserRole() !== 'administrador') {
    header('Location: ../login.php');
    exit();
}

$user = $_SESSION[SESSION_NAME];

try {
    $pdo = getConnection();
    
    // Estatísticas
    $stats = [];
    
    // Total de usuários
    $stmt = $pdo->query("SELECT COUNT(*) as total, role FROM usuarios GROUP BY role");
    $usersByRole = $stmt->fetchAll();
    $stats['usuarios'] = ['total' => 0, 'administradores' => 0, 'aplicadores' => 0, 'candidatos' => 0];
    foreach ($usersByRole as $row) {
        $stats['usuarios']['total'] += $row['total'];
        $stats['usuarios'][$row['role'] . 's'] = $row['total'];
    }
    
    // Total de testes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM testes WHERE ativo = 1");
    $stats['testes'] = $stmt->fetch()['total'];
    
    // Total de aplicações
    $stmt = $pdo->query("SELECT COUNT(*) as total, status FROM aplicacoes GROUP BY status");
    $aplicacoesByStatus = $stmt->fetchAll();
    $stats['aplicacoes'] = ['total' => 0, 'agendado' => 0, 'em_andamento' => 0, 'finalizado' => 0];
    foreach ($aplicacoesByStatus as $row) {
        $stats['aplicacoes']['total'] += $row['total'];
        $stats['aplicacoes'][$row['status']] = $row['total'];
    }
    
    // Taxa de aprovação média
    $stmt = $pdo->query("SELECT AVG(percentual_acerto) as media FROM aplicacoes WHERE status = 'finalizado'");
    $stats['taxa_aprovacao'] = round($stmt->fetch()['media'] ?? 0, 2);
    
    // Últimas atividades
    $stmt = $pdo->query("
        SELECT l.*, u.nome as usuario_nome 
        FROM logs_atividades l
        LEFT JOIN usuarios u ON l.usuario_id = u.id
        ORDER BY l.created_at DESC
        LIMIT 10
    ");
    $atividades = $stmt->fetchAll();
    
    // Testes recentes
    $stmt = $pdo->query("
        SELECT t.*, u.nome as criador_nome, c.nome as categoria_nome
        FROM testes t
        LEFT JOIN usuarios u ON t.criado_por = u.id
        LEFT JOIN categorias c ON t.categoria_id = c.id
        ORDER BY t.created_at DESC
        LIMIT 5
    ");
    $testes_recentes = $stmt->fetchAll();
    
} catch(PDOException $e) {
    error_log($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - <?php echo SYSTEM_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="sidebar-logo">
                    <span>ProfTest Admin</span>
                </a>
            </div>
            
            <nav class="sidebar-menu">
                <div class="menu-title">Principal</div>
                <a href="dashboard.php" class="menu-item active">
                    <span>Dashboard</span>
                </a>
                
                <div class="menu-title">Gestão</div>
                <a href="usuarios.php" class="menu-item">
                    <span>Usuários</span>
                </a>
                <a href="testes.php" class="menu-item">
                    <span>Testes</span>
                </a>
                <a href="categorias.php" class="menu-item">
                    <span>Categorias</span>
                </a>
                <a href="aplicacoes.php" class="menu-item">
                    <span>Aplicações</span>
                </a>
                
                <div class="menu-title">Relatórios</div>
                <a href="relatorios.php" class="menu-item">
                    <span>Relatórios</span>
                </a>
                <a href="logs.php" class="menu-item">
                    <span>Logs do Sistema</span>
                </a>
                
                <div class="menu-title">Sistema</div>
                <a href="configuracoes.php" class="menu-item">
                    <span>Configurações</span>
                </a>
                <a href="../logout.php" class="menu-item">
                    <span>Sair</span>
                </a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <h1 class="header-title">Dashboard</h1>
                <div class="header-actions">
                    <div class="user-menu">
                        <div class="user-avatar">
                            <?php echo substr($user['nome'], 0, 1); ?>
                        </div>
                        <div>
                            <div style="font-size: 14px; font-weight: 500;"><?php echo $user['nome']; ?></div>
                            <div style="font-size: 12px; color: var(--text-muted);">Administrador</div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Content -->
            <div class="content">
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div>
                                <div class="stat-title">Total de Usuários</div>
                                <div class="stat-value"><?php echo $stats['usuarios']['total']; ?></div>
                                <div class="stat-change">
                                    <span><?php echo $stats['usuarios']['candidatos']; ?> candidatos</span>
                                </div>
                            </div>
                            <div class="stat-icon primary">👥</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-header">
                            <div>
                                <div class="stat-title">Testes Ativos</div>
                                <div class="stat-value"><?php echo $stats['testes']; ?></div>
                                <div class="stat-change">Disponíveis para aplicação</div>
                            </div>
                            <div class="stat-icon success">📝</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-header">
                            <div>
                                <div class="stat-title">Aplicações</div>
                                <div class="stat-value"><?php echo $stats['aplicacoes']['total']; ?></div>
                                <div class="stat-change">
                                    <?php echo $stats['aplicacoes']['finalizado']; ?> finalizadas
                                </div>
                            </div>
                            <div class="stat-icon warning">📊</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-header">
                            <div>
                                <div class="stat-title">Taxa de Aprovação</div>
                                <div class="stat-value"><?php echo $stats['taxa_aprovacao']; ?>%</div>
                                <div class="stat-change positive">Média geral</div>
                            </div>
                            <div class="stat-icon danger">📈</div>
                        </div>
                    </div>
                </div>
                
                <!-- Testes Recentes -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Testes Recentes</h3>
                        <a href="testes.php" class="btn btn-primary btn-sm">Ver Todos</a>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Título</th>
                                        <th>Categoria</th>
                                        <th>Nível</th>
                                        <th>Criado por</th>
                                        <th>Data</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($testes_recentes as $teste): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($teste['titulo']); ?></td>
                                        <td><?php echo htmlspecialchars($teste['categoria_nome']); ?></td>
                                        <td>
                                            <span class="badge badge-primary">
                                                <?php echo ucfirst($teste['nivel_dificuldade']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($teste['criador_nome']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($teste['created_at'])); ?></td>
                                        <td>
                                            <span class="badge <?php echo $teste['ativo'] ? 'badge-success' : 'badge-danger'; ?>">
                                                <?php echo $teste['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Atividades Recentes -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Atividades Recentes</h3>
                        <a href="logs.php" class="btn btn-outline btn-sm">Ver Logs Completos</a>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Usuário</th>
                                        <th>Ação</th>
                                        <th>Data/Hora</th>
                                        <th>IP</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($atividades as $atividade): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($atividade['usuario_nome'] ?? 'Sistema'); ?></td>
                                        <td><?php echo htmlspecialchars($atividade['acao']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($atividade['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($atividade['ip_address']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/admin.js"></script>
</body>
</html>

<?php
session_start();
require_once '../config.php';

// Verificar se está logado e é aplicador
if (!checkSession() || getUserRole() !== 'aplicador') {
    header('Location: ../login.php');
    exit();
}

$user = $_SESSION[SESSION_NAME];

try {
    $pdo = getConnection();
    
    // Estatísticas do aplicador
    $aplicador_id = $user['id'];
    
    // Total de candidatos vinculados
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT candidato_id) as total 
        FROM aplicacoes 
        WHERE aplicador_id = ?
    ");
    $stmt->execute([$aplicador_id]);
    $total_candidatos = $stmt->fetch()['total'];
    
    // Aplicações por status
    $stmt = $pdo->prepare("
        SELECT status, COUNT(*) as total 
        FROM aplicacoes 
        WHERE aplicador_id = ? 
        GROUP BY status
    ");
    $stmt->execute([$aplicador_id]);
    $aplicacoes_status = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Aplicações agendadas para hoje
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM aplicacoes 
        WHERE aplicador_id = ? 
        AND DATE(data_agendada) = CURDATE()
        AND status = 'agendado'
    ");
    $stmt->execute([$aplicador_id]);
    $agendadas_hoje = $stmt->fetch()['total'];
    
    // Taxa de conclusão
    $total_aplicacoes = array_sum($aplicacoes_status);
    $finalizadas = $aplicacoes_status['finalizado'] ?? 0;
    $taxa_conclusao = $total_aplicacoes > 0 ? round(($finalizadas / $total_aplicacoes) * 100, 1) : 0;
    
    // Próximas aplicações
    $stmt = $pdo->prepare("
        SELECT a.*, 
               u.nome as candidato_nome, 
               u.email as candidato_email,
               t.titulo as teste_titulo,
               t.tempo_limite
        FROM aplicacoes a
        JOIN usuarios u ON a.candidato_id = u.id
        JOIN testes t ON a.teste_id = t.id
        WHERE a.aplicador_id = ?
        AND a.status = 'agendado'
        AND a.data_agendada >= NOW()
        ORDER BY a.data_agendada ASC
        LIMIT 10
    ");
    $stmt->execute([$aplicador_id]);
    $proximas_aplicacoes = $stmt->fetchAll();
    
    // Resultados recentes
    $stmt = $pdo->prepare("
        SELECT a.*, 
               u.nome as candidato_nome,
               t.titulo as teste_titulo,
               a.pontuacao_obtida,
               a.percentual_acerto
        FROM aplicacoes a
        JOIN usuarios u ON a.candidato_id = u.id
        JOIN testes t ON a.teste_id = t.id
        WHERE a.aplicador_id = ?
        AND a.status = 'finalizado'
        ORDER BY a.data_fim DESC
        LIMIT 10
    ");
    $stmt->execute([$aplicador_id]);
    $resultados_recentes = $stmt->fetchAll();
    
} catch(PDOException $e) {
    error_log($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Aplicador - <?php echo SYSTEM_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="sidebar-logo">
                    <span>ProfTest Aplicador</span>
                </a>
            </div>
            
            <nav class="sidebar-menu">
                <div class="menu-title">Principal</div>
                <a href="dashboard.php" class="menu-item active">
                    <span>Dashboard</span>
                </a>
                
                <div class="menu-title">Gestão</div>
                <a href="candidatos.php" class="menu-item">
                    <span>Candidatos</span>
                </a>
                <a href="aplicar-testes.php" class="menu-item">
                    <span>Aplicar Testes</span>
                </a>
                <a href="aplicacoes.php" class="menu-item">
                    <span>Aplicações</span>
                </a>
                <a href="resultados.php" class="menu-item">
                    <span>Resultados</span>
                </a>
                
                <div class="menu-title">Relatórios</div>
                <a href="relatorios.php" class="menu-item">
                    <span>Relatórios</span>
                </a>
                
                <div class="menu-title">Sistema</div>
                <a href="perfil.php" class="menu-item">
                    <span>Meu Perfil</span>
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
                    <a href="aplicar-testes.php" class="btn btn-primary">
                        + Nova Aplicação
                    </a>
                    <div class="user-menu">
                        <div class="user-avatar">
                            <?php echo substr($user['nome'], 0, 1); ?>
                        </div>
                        <div>
                            <div style="font-size: 14px; font-weight: 500;"><?php echo $user['nome']; ?></div>
                            <div style="font-size: 12px; color: var(--text-muted);">Aplicador</div>
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
                                <div class="stat-title">Candidatos</div>
                                <div class="stat-value"><?php echo $total_candidatos; ?></div>
                                <div class="stat-change">Total vinculados</div>
                            </div>
                            <div class="stat-icon primary">👥</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-header">
                            <div>
                                <div class="stat-title">Aplicações Hoje</div>
                                <div class="stat-value"><?php echo $agendadas_hoje; ?></div>
                                <div class="stat-change">Agendadas</div>
                            </div>
                            <div class="stat-icon warning">📅</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-header">
                            <div>
                                <div class="stat-title">Taxa de Conclusão</div>
                                <div class="stat-value"><?php echo $taxa_conclusao; ?>%</div>
                                <div class="stat-change positive"><?php echo $finalizadas; ?> finalizadas</div>
                            </div>
                            <div class="stat-icon success">✅</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-header">
                            <div>
                                <div class="stat-title">Em Andamento</div>
                                <div class="stat-value"><?php echo $aplicacoes_status['em_andamento'] ?? 0; ?></div>
                                <div class="stat-change">Aplicações ativas</div>
                            </div>
                            <div class="stat-icon info">⏳</div>
                        </div>
                    </div>
                </div>
                
                <!-- Próximas Aplicações -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Próximas Aplicações</h3>
                        <a href="aplicacoes.php" class="btn btn-outline btn-sm">Ver Todas</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($proximas_aplicacoes)): ?>
                            <p class="text-muted text-center">Nenhuma aplicação agendada</p>
                        <?php else: ?>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Candidato</th>
                                        <th>Teste</th>
                                        <th>Data/Hora</th>
                                        <th>Tempo Limite</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($proximas_aplicacoes as $aplicacao): ?>
                                    <tr>
                                        <td>
                                            <div>
                                                <div><?php echo htmlspecialchars($aplicacao['candidato_nome']); ?></div>
                                                <div class="text-muted" style="font-size: 12px;">
                                                    <?php echo htmlspecialchars($aplicacao['candidato_email']); ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($aplicacao['teste_titulo']); ?></td>
                                        <td>
                                            <?php 
                                            $data = new DateTime($aplicacao['data_agendada']);
                                            echo $data->format('d/m/Y H:i'); 
                                            ?>
                                        </td>
                                        <td><?php echo $aplicacao['tempo_limite']; ?> min</td>
                                        <td>
                                            <a href="aplicacao-detalhes.php?id=<?php echo $aplicacao['id']; ?>" 
                                               class="btn btn-sm btn-primary">Gerenciar</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Resultados Recentes -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Resultados Recentes</h3>
                        <a href="resultados.php" class="btn btn-outline btn-sm">Ver Todos</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($resultados_recentes)): ?>
                            <p class="text-muted text-center">Nenhum resultado disponível</p>
                        <?php else: ?>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Candidato</th>
                                        <th>Teste</th>
                                        <th>Data Finalização</th>
                                        <th>Pontuação</th>
                                        <th>Aproveitamento</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($resultados_recentes as $resultado): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($resultado['candidato_nome']); ?></td>
                                        <td><?php echo htmlspecialchars($resultado['teste_titulo']); ?></td>
                                        <td>
                                            <?php echo date('d/m/Y H:i', strtotime($resultado['data_fim'])); ?>
                                        </td>
                                        <td>
                                            <?php echo number_format($resultado['pontuacao_obtida'], 1); ?>
                                        </td>
                                        <td>
                                            <span class="badge <?php 
                                                echo $resultado['percentual_acerto'] >= 70 ? 'badge-success' : 
                                                     ($resultado['percentual_acerto'] >= 50 ? 'badge-warning' : 'badge-danger'); 
                                            ?>">
                                                <?php echo number_format($resultado['percentual_acerto'], 1); ?>%
                                            </span>
                                        </td>
                                        <td>
                                            <a href="resultado-detalhes.php?id=<?php echo $resultado['id']; ?>" 
                                               class="btn btn-sm btn-outline">Ver Detalhes</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

<?php
session_start();
require_once '../config.php';

// Verificar se está logado e é candidato
if (!checkSession() || getUserRole() !== 'candidato') {
    header('Location: ../login.php');
    exit();
}

$user = $_SESSION[SESSION_NAME];

try {
    $pdo = getConnection();
    $candidato_id = $user['id'];
    
    // Buscar aplicações do candidato
    $stmt = $pdo->prepare("
        SELECT a.*, 
               t.titulo as teste_titulo,
               t.descricao as teste_descricao,
               t.tempo_limite,
               t.pontuacao_maxima,
               t.nivel_dificuldade,
               u.nome as aplicador_nome
        FROM aplicacoes a
        JOIN testes t ON a.teste_id = t.id
        JOIN usuarios u ON a.aplicador_id = u.id
        WHERE a.candidato_id = ?
        ORDER BY 
            CASE 
                WHEN a.status = 'agendado' THEN 1
                WHEN a.status = 'em_andamento' THEN 2
                ELSE 3
            END,
            a.data_agendada DESC,
            a.created_at DESC
    ");
    $stmt->execute([$candidato_id]);
    $aplicacoes = $stmt->fetchAll();
    
    // Separar aplicações por status
    $testes_disponiveis = [];
    $testes_em_andamento = [];
    $testes_finalizados = [];
    
    foreach ($aplicacoes as $aplicacao) {
        switch ($aplicacao['status']) {
            case 'agendado':
                $testes_disponiveis[] = $aplicacao;
                break;
            case 'em_andamento':
                $testes_em_andamento[] = $aplicacao;
                break;
            case 'finalizado':
                $testes_finalizados[] = $aplicacao;
                break;
        }
    }
    
    // Estatísticas do candidato
    $total_testes = count($aplicacoes);
    $testes_concluidos = count($testes_finalizados);
    $testes_pendentes = count($testes_disponiveis) + count($testes_em_andamento);
    
    // Média de aproveitamento
    $stmt = $pdo->prepare("
        SELECT AVG(percentual_acerto) as media
        FROM aplicacoes
        WHERE candidato_id = ? AND status = 'finalizado'
    ");
    $stmt->execute([$candidato_id]);
    $media_aproveitamento = round($stmt->fetch()['media'] ?? 0, 1);
    
} catch(PDOException $e) {
    error_log($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Testes - <?php echo SYSTEM_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .test-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        
        .test-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .test-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        
        .test-title {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 5px;
        }
        
        .test-meta {
            display: flex;
            gap: 15px;
            color: #64748b;
            font-size: 14px;
            margin-top: 10px;
        }
        
        .test-meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .difficulty-badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .difficulty-facil {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .difficulty-medio {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .difficulty-dificil {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .no-tests {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
        }
        
        .no-tests-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="sidebar-logo">
                    <span>ProfTest</span>
                </a>
            </div>
            
            <nav class="sidebar-menu">
                <div class="menu-title">Principal</div>
                <a href="dashboard.php" class="menu-item active">
                    <span>Meus Testes</span>
                </a>
                
                <div class="menu-title">Histórico</div>
                <a href="resultados.php" class="menu-item">
                    <span>Meus Resultados</span>
                </a>
                
                <div class="menu-title">Conta</div>
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
                <h1 class="header-title">Meus Testes</h1>
                <div class="header-actions">
                    <div class="user-menu">
                        <div class="user-avatar">
                            <?php echo substr($user['nome'], 0, 1); ?>
                        </div>
                        <div>
                            <div style="font-size: 14px; font-weight: 500;"><?php echo $user['nome']; ?></div>
                            <div style="font-size: 12px; color: var(--text-muted);">Candidato</div>
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
                                <div class="stat-title">Total de Testes</div>
                                <div class="stat-value"><?php echo $total_testes; ?></div>
                            </div>
                            <div class="stat-icon primary">📝</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-header">
                            <div>
                                <div class="stat-title">Pendentes</div>
                                <div class="stat-value"><?php echo $testes_pendentes; ?></div>
                            </div>
                            <div class="stat-icon warning">⏳</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-header">
                            <div>
                                <div class="stat-title">Concluídos</div>
                                <div class="stat-value"><?php echo $testes_concluidos; ?></div>
                            </div>
                            <div class="stat-icon success">✅</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-header">
                            <div>
                                <div class="stat-title">Aproveitamento Médio</div>
                                <div class="stat-value"><?php echo $media_aproveitamento; ?>%</div>
                            </div>
                            <div class="stat-icon info">📊</div>
                        </div>
                    </div>
                </div>
                
                <!-- Testes em Andamento -->
                <?php if (!empty($testes_em_andamento)): ?>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">🔴 Testes em Andamento</h3>
                    </div>
                    <div class="card-body">
                        <?php foreach ($testes_em_andamento as $teste): ?>
                        <div class="test-card">
                            <div class="test-header">
                                <div>
                                    <h4 class="test-title"><?php echo htmlspecialchars($teste['teste_titulo']); ?></h4>
                                    <p style="color: #64748b; margin: 5px 0;">
                                        <?php echo htmlspecialchars($teste['teste_descricao']); ?>
                                    </p>
                                    <div class="test-meta">
                                        <div class="test-meta-item">
                                            ⏱️ Iniciado em: <?php echo date('d/m/Y H:i', strtotime($teste['data_inicio'])); ?>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <a href="realizar-teste.php?id=<?php echo $teste['id']; ?>" 
                                       class="btn btn-danger">Continuar Teste</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Testes Disponíveis -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">📋 Testes Disponíveis</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($testes_disponiveis)): ?>
                        <div class="no-tests">
                            <div class="no-tests-icon">📭</div>
                            <h4>Nenhum teste disponível no momento</h4>
                            <p>Quando novos testes forem atribuídos a você, eles aparecerão aqui.</p>
                        </div>
                        <?php else: ?>
                            <?php foreach ($testes_disponiveis as $teste): ?>
                            <div class="test-card">
                                <div class="test-header">
                                    <div>
                                        <h4 class="test-title"><?php echo htmlspecialchars($teste['teste_titulo']); ?></h4>
                                        <p style="color: #64748b; margin: 5px 0;">
                                            <?php echo htmlspecialchars($teste['teste_descricao']); ?>
                                        </p>
                                        <div class="test-meta">
                                            <div class="test-meta-item">
                                                ⏱️ <?php echo $teste['tempo_limite']; ?> minutos
                                            </div>
                                            <div class="test-meta-item">
                                                🎯 <?php echo $teste['pontuacao_maxima']; ?> pontos
                                            </div>
                                            <div class="test-meta-item">
                                                <span class="difficulty-badge difficulty-<?php echo $teste['nivel_dificuldade']; ?>">
                                                    <?php echo ucfirst($teste['nivel_dificuldade']); ?>
                                                </span>
                                            </div>
                                        </div>
                                        <?php if ($teste['data_agendada']): ?>
                                        <div style="margin-top: 10px; color: #64748b; font-size: 13px;">
                                            📅 Agendado para: <?php echo date('d/m/Y H:i', strtotime($teste['data_agendada'])); ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <a href="iniciar-teste.php?id=<?php echo $teste['id']; ?>" 
                                           class="btn btn-primary">Iniciar Teste</a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Testes Finalizados -->
                <?php if (!empty($testes_finalizados)): ?>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">✅ Testes Concluídos</h3>
                        <a href="resultados.php" class="btn btn-outline btn-sm">Ver Todos os Resultados</a>
                    </div>
                    <div class="card-body">
                        <?php foreach (array_slice($testes_finalizados, 0, 5) as $teste): ?>
                        <div class="test-card">
                            <div class="test-header">
                                <div>
                                    <h4 class="test-title"><?php echo htmlspecialchars($teste['teste_titulo']); ?></h4>
                                    <div class="test-meta">
                                        <div class="test-meta-item">
                                            📅 Finalizado: <?php echo date('d/m/Y', strtotime($teste['data_fim'])); ?>
                                        </div>
                                        <div class="test-meta-item">
                                            🎯 Pontuação: <?php echo number_format($teste['pontuacao_obtida'], 1); ?>/<?php echo $teste['pontuacao_maxima']; ?>
                                        </div>
                                        <div class="test-meta-item">
                                            <span class="badge <?php 
                                                echo $teste['percentual_acerto'] >= 70 ? 'badge-success' : 
                                                     ($teste['percentual_acerto'] >= 50 ? 'badge-warning' : 'badge-danger'); 
                                            ?>">
                                                <?php echo number_format($teste['percentual_acerto'], 1); ?>% de aproveitamento
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <a href="resultado-detalhes.php?id=<?php echo $teste['id']; ?>" 
                                       class="btn btn-outline btn-sm">Ver Resultado</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>

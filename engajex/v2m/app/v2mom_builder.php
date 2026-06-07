<?php
session_start();
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/AIHelper.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'responsavel') {
    redirect('login.php');
}

$update_msg = '';
$curr_tab = $_GET['tab'] ?? 'vision';
$v2mom_id = $_GET['id'] ?? null;

// Ensure we have a V2MOM ID or create a draft
if (!$v2mom_id) {
    // Check if there is a draft
    $stmt = $pdo->prepare("SELECT id FROM v2mom WHERE id_empresa = ? AND status = 'rascunho'");
    $stmt->execute([$_SESSION['empresa_id']]);
    $existing = $stmt->fetch();

    if ($existing) {
        $v2mom_id = $existing['id'];
    } else {
        // Create new
        $stmt = $pdo->prepare("INSERT INTO v2mom (id_empresa, id_responsavel, versao, status) VALUES (?, ?, 'Versão Inicial', 'rascunho')");
        $stmt->execute([$_SESSION['empresa_id'], $_SESSION['user_id']]);
        $v2mom_id = $pdo->lastInsertId();
    }
}

$aiHelper = new AIHelper($pdo, $_SESSION['empresa_id']);

// Logic to handle AI Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ai_action'])) {
    $action = $_POST['ai_action'];
    if (in_array($action, ['suggest_methods', 'suggest_obstacles', 'suggest_measures'])) {
        // Prepare complex context
        $stmt = $pdo->prepare("SELECT * FROM visoes WHERE id_v2mom = ? LIMIT 1");
        $stmt->execute([$v2mom_id]);
        $v = $stmt->fetch();

        $stmt = $pdo->prepare("SELECT * FROM valores WHERE id_v2mom = ?");
        $stmt->execute([$v2mom_id]);
        $vals = $stmt->fetchAll();

        $stmt = $pdo->prepare("SELECT * FROM metodos WHERE id_v2mom = ?");
        $stmt->execute([$v2mom_id]);
        $mets = $stmt->fetchAll();

        $context_data = [
            'vision' => $v['descricao'] ?? '',
            'values' => array_reduce($vals, function ($carry, $item) {
                return $carry . $item['descricao'] . ', ';
            }, ''),
            'methods' => array_reduce($mets, function ($carry, $item) {
                return $carry . $item['descricao'] . ', ';
            }, '')
        ];
    } else {
        $context_data = $_POST['context_data'] ?? '';
    }

    // Simulate Fetching Company Context
    $stmt_comp = $pdo->prepare("SELECT * FROM empresas WHERE id = ?");
    $stmt_comp->execute([$_SESSION['empresa_id']]);
    $company = $stmt_comp->fetch();

    $prompt_base = "Você é um consultor especialista em método V2MOM. Empresa: {$company['razao_social']}, Segmento: {$company['segmento']}, Porte: {$company['porte']}.";

    $response = [];

    switch ($action) {
        case 'vision_analysis':
            $prompt = "$prompt_base Analise esta Visão: '$context_data'. É inspiradora? Sugira 3 melhorias.";
            $res = $aiHelper->generateResponse("Seja um consultor estratégico.", $prompt, $v2mom_id, 'vision_analysis');
            $response = $res;
            break;

        case 'suggest_values':
            $prompt = "$prompt_base A Visão é: '$context_data'. Sugira 5 valores corporativos alinhados.";
            $res = $aiHelper->generateResponse("Seja um consultor estratégico.", $prompt, $v2mom_id, 'suggest_values');
            $response = $res;
            break;

        case 'suggest_methods':
            $prompt = "$prompt_base A Visão é: '{$context_data['vision']}'. Os Valores são: '{$context_data['values']}'. Sugira 3 métodos estratégicos para alcançar essa visão.";
            $res = $aiHelper->generateResponse("Seja um consultor estratégico.", $prompt, $v2mom_id, 'suggest_methods');
            $response = $res;
            break;

        case 'suggest_obstacles':
            $prompt = "$prompt_base A Visão é: '{$context_data['vision']}'. Os Métodos são: '{$context_data['methods']}'. Quais os principais obstáculos e riscos para executar isso?";
            $res = $aiHelper->generateResponse("Seja um consultor de riscos.", $prompt, $v2mom_id, 'suggest_obstacles');
            $response = $res;
            break;

        case 'suggest_measures':
            $prompt = "$prompt_base A Visão é: '{$context_data['vision']}'. Sugira 5 KPIs (Métricas) claros e quantificáveis.";
            $res = $aiHelper->generateResponse("Seja um especialista em dados.", $prompt, $v2mom_id, 'suggest_measures');
            $response = $res;
            break;
    }

    // For now we just echo json for AJAX handling, but we are doing full page reload style for simplicity first
    // Actually, let's just store result in session or variable to show in modal/alert
    if (isset($response['content'])) {
        $update_msg = "<strong>IA Sugere:</strong><br>" . nl2br(htmlspecialchars($response['content']));
    } else {
        $update_msg = "Erro IA: " . ($response['error'] ?? 'Desconhecido');
    }
}

// Fetch current V2MOM data
// Fetch current V2MOM data
$stmt = $pdo->prepare("SELECT * FROM visoes WHERE id_v2mom = ? LIMIT 1");
$stmt->execute([$v2mom_id]);
$current_vision = $stmt->fetch();

// Handle Save Vision
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_vision'])) {
    $descricao = $_POST['context_data'] ?? '';

    if ($current_vision) {
        $stmt = $pdo->prepare("UPDATE visoes SET descricao = ? WHERE id = ?");
        $stmt->execute([$descricao, $current_vision['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO visoes (id_v2mom, descricao) VALUES (?, ?)");
        $stmt->execute([$v2mom_id, $descricao]);
    }

    $update_msg = "<span style='color: #6ee7b7'>Visão salva com sucesso!</span>";

    // Refresh data
    $stmt = $pdo->prepare("SELECT * FROM visoes WHERE id_v2mom = ? LIMIT 1");
    $stmt->execute([$v2mom_id]);
    $current_vision = $stmt->fetch();
}

// Fetch Values
$stmt = $pdo->prepare("SELECT * FROM valores WHERE id_v2mom = ? ORDER BY prioridade ASC");
$stmt->execute([$v2mom_id]);
$current_values = $stmt->fetchAll();

// Handle Add/Edit Value
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_value'])) {
    $descricao = $_POST['val_descricao'];
    $prioridade = $_POST['val_prioridade'];
    $val_id = $_POST['val_id'] ?? '';

    if ($val_id) {
        $stmt = $pdo->prepare("UPDATE valores SET descricao = ?, prioridade = ? WHERE id = ?");
        $stmt->execute([$descricao, $prioridade, $val_id]);
        $update_msg = "<span style='color: #6ee7b7'>Valor atualizado!</span>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO valores (id_v2mom, descricao, prioridade) VALUES (?, ?, ?)");
        $stmt->execute([$v2mom_id, $descricao, $prioridade]);
        $update_msg = "<span style='color: #6ee7b7'>Novo valor adicionado!</span>";
    }

    // Refresh
    $stmt = $pdo->prepare("SELECT * FROM valores WHERE id_v2mom = ? ORDER BY prioridade ASC");
    $stmt->execute([$v2mom_id]);
    $current_values = $stmt->fetchAll();
}

// Handle Delete Value
if (isset($_GET['delete_val'])) {
    $stmt = $pdo->prepare("DELETE FROM valores WHERE id = ?");
    $stmt->execute([$_GET['delete_val']]);
    redirect('v2mom_builder.php?tab=values');
}

// Fetch Methods
$stmt = $pdo->prepare("SELECT * FROM metodos WHERE id_v2mom = ?");
$stmt->execute([$v2mom_id]);
$current_methods = $stmt->fetchAll();

// Handle Save Method
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_method'])) {
    $descricao = $_POST['met_descricao'];
    $responsavel = $_POST['met_responsavel'];
    $prazo = $_POST['met_prazo'];
    $status = $_POST['met_status'];
    $met_id = $_POST['met_id'] ?? '';

    if ($met_id) {
        $stmt = $pdo->prepare("UPDATE metodos SET descricao = ?, responsavel = ?, prazo = ?, status = ? WHERE id = ?");
        $stmt->execute([$descricao, $responsavel, $prazo, $status, $met_id]);
        $update_msg = "<span style='color: #6ee7b7'>Método atualizado!</span>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO metodos (id_v2mom, descricao, responsavel, prazo, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$v2mom_id, $descricao, $responsavel, $prazo, $status]);
        $update_msg = "<span style='color: #6ee7b7'>Novo método adicionado!</span>";
    }

    // Refresh
    $stmt = $pdo->prepare("SELECT * FROM metodos WHERE id_v2mom = ?");
    $stmt->execute([$v2mom_id]);
    $current_methods = $stmt->fetchAll();
}

// Handle Delete Method
if (isset($_GET['delete_met'])) {
    $stmt = $pdo->prepare("DELETE FROM metodos WHERE id = ?");
    $stmt->execute([$_GET['delete_met']]);
    redirect('v2mom_builder.php?tab=methods');
}

// Fetch Obstacles
$stmt = $pdo->prepare("SELECT * FROM obstaculos WHERE id_v2mom = ?");
$stmt->execute([$v2mom_id]);
$current_obstacles = $stmt->fetchAll();

// Handle Save Obstacle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_obstacle'])) {
    $descricao = $_POST['obs_descricao'];
    $impacto = $_POST['obs_impacto'];
    $solucao = $_POST['obs_solucao'];
    $obs_id = $_POST['obs_id'] ?? '';

    if ($obs_id) {
        $stmt = $pdo->prepare("UPDATE obstaculos SET descricao = ?, impacto = ?, solucao_proposta = ? WHERE id = ?");
        $stmt->execute([$descricao, $impacto, $solucao, $obs_id]);
        $update_msg = "<span style='color: #6ee7b7'>Obstáculo atualizado!</span>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO obstaculos (id_v2mom, descricao, impacto, solucao_proposta) VALUES (?, ?, ?, ?)");
        $stmt->execute([$v2mom_id, $descricao, $impacto, $solucao]);
        $update_msg = "<span style='color: #6ee7b7'>Novo obstáculo adicionado!</span>";
    }

    // Refresh
    $stmt = $pdo->prepare("SELECT * FROM obstaculos WHERE id_v2mom = ?");
    $stmt->execute([$v2mom_id]);
    $current_obstacles = $stmt->fetchAll();
}

// Handle Delete Obstacle
if (isset($_GET['delete_obs'])) {
    $stmt = $pdo->prepare("DELETE FROM obstaculos WHERE id = ?");
    $stmt->execute([$_GET['delete_obs']]);
    redirect('v2mom_builder.php?tab=obstacles');
}

// Fetch Metrics
$stmt = $pdo->prepare("SELECT * FROM metricas WHERE id_v2mom = ?");
$stmt->execute([$v2mom_id]);
$current_metrics = $stmt->fetchAll();

// Handle Save Metric
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_metric'])) {
    $descricao = $_POST['metrica_descricao'];
    $meta = $_POST['metrica_meta'];
    $unidade = $_POST['metrica_unidade'];
    $valor_atual = $_POST['metrica_atual'];
    $metrica_id = $_POST['metrica_id'] ?? '';

    if ($metrica_id) {
        $stmt = $pdo->prepare("UPDATE metricas SET descricao = ?, meta = ?, unidade = ?, valor_atual = ? WHERE id = ?");
        $stmt->execute([$descricao, $meta, $unidade, $valor_atual, $metrica_id]);
        $update_msg = "<span style='color: #6ee7b7'>Métrica atualizada!</span>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO metricas (id_v2mom, descricao, meta, unidade, valor_atual) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$v2mom_id, $descricao, $meta, $unidade, $valor_atual]);
        $update_msg = "<span style='color: #6ee7b7'>Nova métrica adicionada!</span>";
    }

    // Refresh
    $stmt = $pdo->prepare("SELECT * FROM metricas WHERE id_v2mom = ?");
    $stmt->execute([$v2mom_id]);
    $current_metrics = $stmt->fetchAll();
}

// Handle Delete Metric
if (isset($_GET['delete_metric'])) {
    $stmt = $pdo->prepare("DELETE FROM metricas WHERE id = ?");
    $stmt->execute([$_GET['delete_metric']]);
    redirect('v2mom_builder.php?tab=measures');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Builder V2MOM</title>
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

        .tab-nav {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--glass-border);
            padding-bottom: 10px;
            overflow-x: auto;
        }

        .tab-item {
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 8px;
            color: #94a3b8;
            white-space: nowrap;
        }

        .tab-item.active {
            background: var(--primary-color);
            color: white;
        }

        .tab-item:hover:not(.active) {
            background: rgba(255, 255, 255, 0.05);
        }

        .app-nav li {
            margin-bottom: 10px;
        }

        .app-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 6px;
            color: #cbd5e1;
        }

        .app-nav a:hover,
        .app-nav a.active {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary-color);
        }
    </style>
</head>

<body>

    <header class="glass"
        style="position: fixed; top: 0; width: 100%; height: 80px; z-index: 50; display: flex; align-items: center; padding: 0 20px; justify-content: space-between;">
        <div class="logo"><i class="ph ph-target"></i> V2MOM BUILDER</div>
        <div style="display: flex; gap: 10px;">
            <button class="btn btn-primary">Salvar Rascunho</button>
            <a href="dashboard.php" class="btn btn-outline">Voltar</a>
        </div>
    </header>

    <aside class="sidebar">
        <ul class="app-nav">
            <li><a href="dashboard.php"><i class="ph ph-squares-four"></i> Visão Geral</a></li>
            <li><a href="v2mom_builder.php" class="active"><i class="ph ph-tree-structure"></i> Construir V2MOM</a></li>
            <li><a href="execution.php"><i class="ph ph-check-square-offset"></i> Execução & Métricas</a></li>
            <li><a href="reports.php"><i class="ph ph-file-pdf"></i> Relatórios</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <?php if ($update_msg): ?>
            <div class="glass" style="padding: 20px; margin-bottom: 20px; border: 1px solid var(--primary-color);">
                <?= $update_msg ?>
            </div>
        <?php endif; ?>

        <div class="tab-nav">
            <a href="?tab=vision" class="tab-item <?= $curr_tab == 'vision' ? 'active' : '' ?>">1. Visão (Vision)</a>
            <a href="?tab=values" class="tab-item <?= $curr_tab == 'values' ? 'active' : '' ?>">2. Valores (Values)</a>
            <a href="?tab=methods" class="tab-item <?= $curr_tab == 'methods' ? 'active' : '' ?>">3. Métodos
                (Methods)</a>
            <a href="?tab=obstacles" class="tab-item <?= $curr_tab == 'obstacles' ? 'active' : '' ?>">4. Obstáculos
                (Obstacles)</a>
            <a href="?tab=measures" class="tab-item <?= $curr_tab == 'measures' ? 'active' : '' ?>">5. Métricas
                (Measures)</a>
        </div>

        <div class="glass" style="padding: 30px; min-height: 500px;">
            <?php if ($curr_tab === 'vision'): ?>
                <h2 style="margin-bottom: 20px;">Defina sua Visão</h2>
                <p style="color: #94a3b8; margin-bottom: 20px;">O que você quer alcançar? Pense grande e seja inspirador.
                </p>

                <form method="POST">
                    <textarea name="context_data" rows="6"
                        style="width: 100%; padding: 15px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 8px; font-size: 1.1rem; margin-bottom: 20px;"
                        placeholder="Ex: Ser a empresa líder em inovação sustentável na América Latina até 2026..."><?= htmlspecialchars($current_vision['descricao'] ?? '') ?></textarea>

                    <div style="display: flex; justify-content: space-between;">
                        <button type="submit" name="ai_action" value="vision_analysis" class="btn btn-outline"><i
                                class="ph ph-robot"></i> IA: Analisar Visão</button>
                        <button type="submit" name="save_vision" value="1" class="btn btn-primary">Salvar Visão</button>
                    </div>
                </form>

            <?php elseif ($curr_tab === 'values'): ?>
                <h2 style="margin-bottom: 20px;">Seus Valores Fundamentais</h2>
                <p style="color: #94a3b8; margin-bottom: 20px;">Quais princípios guiam suas decisões?</p>

                <!-- AI Suggestion Form for Values -->
                <form method="POST"
                    style="margin-bottom: 30px; background: rgba(99,102,241,0.1); padding: 15px; border-radius: 8px; border: 1px dashed var(--primary-color);">
                    <p style="margin-bottom: 10px; font-size: 0.9rem; color: #cbd5e1;">Ainda sem ideias? Peça ajuda para a
                        IA com base na sua Visão.</p>
                    <input type="hidden" name="ai_action" value="suggest_values">
                    <input type="hidden" name="context_data"
                        value="<?= htmlspecialchars($current_vision['descricao'] ?? '') ?>">
                    <button type="submit" class="btn btn-outline" style="width: 100%;"><i class="ph ph-sparkle"></i> IA:
                        Sugerir Valores</button>
                </form>

                <!-- List of Existing Values -->
                <div
                    style="display: grid; gap: 15px; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); margin-bottom: 30px;">
                    <?php foreach ($current_values as $val): ?>
                        <div class="glass" style="padding: 20px; position: relative;">
                            <div
                                style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                                <span
                                    style="background: var(--primary-color); padding: 2px 8px; border-radius: 4px; font-size: 0.8rem;">Prio:
                                    <?= $val['prioridade'] ?></span>
                                <a href="?tab=values&delete_val=<?= $val['id'] ?>"
                                    onclick="return confirm('Excluir este valor?')" style="color: var(--danger-color);"><i
                                        class="ph ph-trash"></i></a>
                            </div>
                            <p><?= htmlspecialchars($val['descricao']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Add New Value Form -->
                <div class="glass" style="padding: 20px; border: 1px solid var(--glass-border);">
                    <h4 style="margin-bottom: 15px;">Adicionar Novo Valor</h4>
                    <form method="POST" style="display: flex; gap: 10px;">
                        <input type="number" name="val_prioridade" placeholder="Prio"
                            style="width: 70px; padding: 10px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px;"
                            value="<?= count($current_values) + 1 ?>">
                        <input type="text" name="val_descricao" placeholder="Descrição do Valor (Ex: Transparência Radical)"
                            required
                            style="flex: 1; padding: 10px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px;">
                        <button type="submit" name="save_value" value="1" class="btn btn-primary"><i class="ph ph-plus"></i>
                            Adicionar</button>
                    </form>
                </div>

            <?php elseif ($curr_tab === 'methods'): ?>
                <h2 style="margin-bottom: 20px;">Métodos (Planos de Ação)</h2>
                <p style="color: #94a3b8; margin-bottom: 20px;">Quais ações específicas você tomará para alcançar sua visão?
                </p>

                <!-- AI Suggestion Form for Methods -->
                <form method="POST"
                    style="margin-bottom: 30px; background: rgba(99,102,241,0.1); padding: 15px; border-radius: 8px; border: 1px dashed var(--primary-color);">
                    <p style="margin-bottom: 10px; font-size: 0.9rem; color: #cbd5e1;">Precisa de ideias de como executar?
                    </p>
                    <input type="hidden" name="ai_action" value="suggest_methods">
                    <button type="submit" class="btn btn-outline" style="width: 100%;"><i class="ph ph-sparkle"></i> IA:
                        Sugerir Métodos (Baseado em Visão/Valores)</button>
                </form>

                <!-- List of Methods -->
                <div style="margin-bottom: 30px;">
                    <?php if (empty($current_methods)): ?>
                        <p style="text-align: center; color: #64748b; padding: 20px;">Nenhum método cadastrado ainda.</p>
                    <?php else: ?>
                        <?php foreach ($current_methods as $met): ?>
                            <div class="glass"
                                style="padding: 20px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
                                <div style="flex: 1;">
                                    <h4 style="color: white; margin-bottom: 5px;"><?= htmlspecialchars($met['descricao']) ?></h4>
                                    <div style="font-size: 0.85rem; color: #94a3b8; display: flex; gap: 15px;">
                                        <span><i class="ph ph-user"></i> <?= htmlspecialchars($met['responsavel']) ?></span>
                                        <span><i class="ph ph-calendar"></i> <?= htmlspecialchars($met['prazo']) ?></span>
                                        <span
                                            style="color: <?= $met['status'] == 'concluido' ? '#6ee7b7' : ($met['status'] == 'atrasado' ? '#fca5a5' : '#fde047') ?>">
                                            <i class="ph ph-circle"></i> <?= ucfirst(str_replace('_', ' ', $met['status'])) ?>
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <a href="?tab=methods&delete_met=<?= $met['id'] ?>"
                                        onclick="return confirm('Excluir este método?')"
                                        style="color: var(--danger-color); padding: 10px;"><i class="ph ph-trash"></i></a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Add Method Form -->
                <div class="glass" style="padding: 20px; border: 1px solid var(--glass-border);">
                    <h4 style="margin-bottom: 15px;">Novo Método</h4>
                    <form method="POST"
                        style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 10px; align-items: end;">
                        <div>
                            <label style="font-size: 0.8rem; color: #cbd5e1;">O que fazer?</label>
                            <input type="text" name="met_descricao" placeholder="Descrição da Ação" required
                                style="width: 100%; padding: 10px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px;">
                        </div>
                        <div>
                            <label style="font-size: 0.8rem; color: #cbd5e1;">Responsável</label>
                            <input type="text" name="met_responsavel" placeholder="Quem?" required
                                style="width: 100%; padding: 10px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px;">
                        </div>
                        <div>
                            <label style="font-size: 0.8rem; color: #cbd5e1;">Prazo</label>
                            <input type="text" name="met_prazo" placeholder="Quando?"
                                style="width: 100%; padding: 10px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px;">
                        </div>
                        <div>
                            <label style="font-size: 0.8rem; color: #cbd5e1;">Status</label>
                            <select name="met_status"
                                style="width: 100%; padding: 10px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px;">
                                <option value="nao_iniciado">Não Iniciado</option>
                                <option value="em_andamento">Em Andamento</option>
                                <option value="concluido">Concluído</option>
                            </select>
                        </div>
                        <button type="submit" name="save_method" value="1" class="btn btn-primary" style="height: 42px;"><i
                                class="ph ph-plus"></i></button>
                    </form>
                </div>

            <?php elseif ($curr_tab === 'obstacles'): ?>
                <h2 style="margin-bottom: 20px;">Obstáculos (Riscos & Bloqueios)</h2>
                <p style="color: #94a3b8; margin-bottom: 20px;">O que pode impedir seu sucesso e como você vai contornar?
                </p>

                <!-- AI Suggestion Form for Obstacles -->
                <form method="POST"
                    style="margin-bottom: 30px; background: rgba(99,102,241,0.1); padding: 15px; border-radius: 8px; border: 1px dashed var(--primary-color);">
                    <p style="margin-bottom: 10px; font-size: 0.9rem; color: #cbd5e1;">Identificar riscos é difícil. Peça
                        ajuda a IA.</p>
                    <input type="hidden" name="ai_action" value="suggest_obstacles">
                    <button type="submit" class="btn btn-outline" style="width: 100%;"><i class="ph ph-shield-warning"></i>
                        IA: Identificar Riscos</button>
                </form>

                <!-- List of Obstacles -->
                <div style="margin-bottom: 30px;">
                    <?php if (empty($current_obstacles)): ?>
                        <p style="text-align: center; color: #64748b; padding: 20px;">Nenhum obstáculo mapeado.</p>
                    <?php else: ?>
                        <?php foreach ($current_obstacles as $obs): ?>
                            <div class="glass" style="padding: 20px; margin-bottom: 15px; position: relative;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                    <span
                                        style="background: <?= $obs['impacto'] == 'alto' ? 'rgba(239,68,68,0.2)' : ($obs['impacto'] == 'medio' ? 'rgba(253,224,71,0.2)' : 'rgba(16,185,129,0.2)') ?>; color: <?= $obs['impacto'] == 'alto' ? '#fca5a5' : ($obs['impacto'] == 'medio' ? '#fde047' : '#6ee7b7') ?>; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem;">
                                        Impacto: <?= ucfirst($obs['impacto']) ?>
                                    </span>
                                    <a href="?tab=obstacles&delete_obs=<?= $obs['id'] ?>"
                                        onclick="return confirm('Remover este obstáculo?')" style="color: var(--danger-color);"><i
                                            class="ph ph-trash"></i></a>
                                </div>
                                <h4 style="color: white; margin-bottom: 10px;"><?= htmlspecialchars($obs['descricao']) ?></h4>
                                <p style="font-size: 0.9rem; color: #94a3b8;"><strong>Solução:</strong>
                                    <?= htmlspecialchars($obs['solucao_proposta']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Add Obstacle Form -->
                <div class="glass" style="padding: 20px; border: 1px solid var(--glass-border);">
                    <h4 style="margin-bottom: 15px;">Mapear Novo Obstáculo</h4>
                    <form method="POST" style="display: grid; gap: 15px;">
                        <div>
                            <label style="font-size: 0.8rem; color: #cbd5e1;">O problema</label>
                            <input type="text" name="obs_descricao" placeholder="Ex: Falta de orçamento para marketing"
                                required
                                style="width: 100%; padding: 10px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px;">
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 10px;">
                            <div>
                                <label style="font-size: 0.8rem; color: #cbd5e1;">Nível de Impacto</label>
                                <select name="obs_impacto"
                                    style="width: 100%; padding: 10px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px;">
                                    <option value="alto">Alto</option>
                                    <option value="medio">Médio</option>
                                    <option value="baixo">Baixo</option>
                                </select>
                            </div>
                            <div>
                                <label style="font-size: 0.8rem; color: #cbd5e1;">Como resolver?</label>
                                <input type="text" name="obs_solucao" placeholder="Ex: Buscar parceiros de mídia"
                                    style="width: 100%; padding: 10px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px;">
                            </div>
                        </div>
                        <button type="submit" name="save_obstacle" value="1" class="btn btn-primary">Adicionar
                            Obstáculo</button>
                    </form>
                </div>

            <?php elseif ($curr_tab === 'measures'): ?>
                <h2 style="margin-bottom: 20px;">Métricas (KPIs & Measures)</h2>
                <p style="color: #94a3b8; margin-bottom: 20px;">Como você saberá que venceu? Quantifique seu sucesso.</p>

                <!-- AI Suggestion Form for Metrics -->
                <form method="POST"
                    style="margin-bottom: 30px; background: rgba(99,102,241,0.1); padding: 15px; border-radius: 8px; border: 1px dashed var(--primary-color);">
                    <p style="margin-bottom: 10px; font-size: 0.9rem; color: #cbd5e1;">Dúvidas em como medir?</p>
                    <input type="hidden" name="ai_action" value="suggest_measures">
                    <button type="submit" class="btn btn-outline" style="width: 100%;"><i class="ph ph-chart-bar"></i> IA:
                        Sugerir Métricas</button>
                </form>

                <!-- List of Metrics -->
                <div
                    style="margin-bottom: 30px; display: grid; gap: 15px; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));">
                    <?php if (empty($current_metrics)): ?>
                        <p style="grid-column: 1/-1; text-align: center; color: #64748b; padding: 20px;">Nenhuma métrica
                            definida.</p>
                    <?php else: ?>
                        <?php foreach ($current_metrics as $met): ?>
                            <div class="glass" style="padding: 20px; position: relative; text-align: center;">
                                <a href="?tab=measures&delete_metric=<?= $met['id'] ?>"
                                    onclick="return confirm('Remover esta métrica?')"
                                    style="position: absolute; top: 10px; right: 10px; color: var(--danger-color);"><i
                                        class="ph ph-trash"></i></a>
                                <h2 style="color: var(--secondary-color); font-size: 2rem; margin: 10px 0;">
                                    <?= htmlspecialchars($met['valor_atual'] ?: '0') ?> <span
                                        style="font-size: 1rem; color: #94a3b8;">/ <?= htmlspecialchars($met['meta']) ?>
                                        <?= htmlspecialchars($met['unidade']) ?></span>
                                </h2>
                                <p style="color: white;"><?= htmlspecialchars($met['descricao']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Add Metric Form -->
                <div class="glass" style="padding: 20px; border: 1px solid var(--glass-border);">
                    <h4 style="margin-bottom: 15px;">Nova Métrica</h4>
                    <form method="POST"
                        style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 10px; align-items: end;">
                        <div>
                            <label style="font-size: 0.8rem; color: #cbd5e1;">Indicador</label>
                            <input type="text" name="metrica_descricao" placeholder="Ex: Receita Recorrente" required
                                style="width: 100%; padding: 10px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px;">
                        </div>
                        <div>
                            <label style="font-size: 0.8rem; color: #cbd5e1;">Meta</label>
                            <input type="text" name="metrica_meta" placeholder="1000" required
                                style="width: 100%; padding: 10px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px;">
                        </div>
                        <div>
                            <label style="font-size: 0.8rem; color: #cbd5e1;">Unidade</label>
                            <input type="text" name="metrica_unidade" placeholder="R$, %, #"
                                style="width: 100%; padding: 10px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px;">
                        </div>
                        <div>
                            <label style="font-size: 0.8rem; color: #cbd5e1;">Atual</label>
                            <input type="text" name="metrica_atual" placeholder="0"
                                style="width: 100%; padding: 10px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px;">
                        </div>
                        <button type="submit" name="save_metric" value="1" class="btn btn-primary" style="height: 42px;"><i
                                class="ph ph-plus"></i></button>
                    </form>
                </div>

            <?php else: ?>
                <h2>Em desenvolvimento...</h2>
            <?php endif; ?>
        </div>
    </main>

</body>

</html>
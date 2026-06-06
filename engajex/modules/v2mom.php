<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config.php';
require_once 'v2mom_ai.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$companyId = $_SESSION['company_id'] ?? 1;
$userId = $_SESSION['user_id'];
$isManager = in_array($_SESSION['role'], ['admin', 'manager', 'responsible']);
$view = $_GET['tab'] ?? 'vision';
$message = '';

// --- 1. Handle V2MOM Existence ---
// Ensure a V2MOM record exists for this company
$stmt = $pdo->prepare("SELECT id, version, status FROM v2mom WHERE company_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$companyId]);
$v2mom = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$v2mom) {
    if ($isManager) {
        $pdo->prepare("INSERT INTO v2mom (company_id, user_id, version) VALUES (?, ?, '1.0')")->execute([$companyId, $userId]);
        $v2momId = $pdo->lastInsertId();
    } else {
        die("Nenhum plano V2MOM definido. Peça ao gestor para criar.");
    }
} else {
    $v2momId = $v2mom['id'];
}

// --- 2. Handle POST Actions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isManager) {
    // A. AI Config
    if ($_POST['action'] === 'save_config') {
        $key = $_POST['api_key'];
        // Check if exists
        $check = $pdo->prepare("SELECT id FROM ai_config WHERE company_id = ?");
        $check->execute([$companyId]);
        if ($check->fetch()) {
            $pdo->prepare("UPDATE ai_config SET api_key = ? WHERE company_id = ?")->execute([$key, $companyId]);
        } else {
            $pdo->prepare("INSERT INTO ai_config (company_id, api_key) VALUES (?, ?)")->execute([$companyId, $key]);
        }
        $message = "Configuração salva!";
    }

    // B. Save Vision
    if ($_POST['action'] === 'save_vision') {
        $desc = $_POST['description'];
        // Check exist
        $check = $pdo->prepare("SELECT id FROM v2mom_vision WHERE v2mom_id = ?");
        $check->execute([$v2momId]);
        if ($check->fetch()) {
            $pdo->prepare("UPDATE v2mom_vision SET description = ? WHERE v2mom_id = ?")->execute([$desc, $v2momId]);
        } else {
            $pdo->prepare("INSERT INTO v2mom_vision (v2mom_id, description) VALUES (?, ?)")->execute([$v2momId, $desc]);
        }
        $message = "Visão atualizada!";
    }

    // C. Add Method
    if ($_POST['action'] === 'add_method') {
        $pdo->prepare("INSERT INTO v2mom_methods (v2mom_id, description, owner_id, deadline) VALUES (?, ?, ?, ?)")
            ->execute([$v2momId, $_POST['desc'], $_POST['owner_id'], $_POST['deadline']]);
        $message = "Método adicionado!";
    }

    if ($_POST['action'] === 'add_value') {
        $pdo->prepare("INSERT INTO v2mom_values (v2mom_id, description) VALUES (?, ?)")
            ->execute([$v2momId, $_POST['desc']]);
        $message = "Valor adicionado!";
    }

    if ($_POST['action'] === 'add_obstacle') {
        $pdo->prepare("INSERT INTO v2mom_obstacles (v2mom_id, description, impact, mitigation_plan) VALUES (?, ?, ?, ?)")
            ->execute([$v2momId, $_POST['desc'], $_POST['impact'], $_POST['mitigation']]);
        $message = "Obstáculo adicionado!";
    }

    if ($_POST['action'] === 'add_measure') {
        $pdo->prepare("INSERT INTO v2mom_measures (v2mom_id, description, target, unit) VALUES (?, ?, ?, ?)")
            ->execute([$v2momId, $_POST['desc'], $_POST['target'], $_POST['unit']]);
        $message = "Medida adicionada!";
    }

    // D. Delete Item (Generic)
    if ($_POST['action'] === 'delete_item') {
        $table = $_POST['table']; // v2mom_methods, etc
        $id = $_POST['item_id'];
        // Security check on table name
        if (in_array($table, ['v2mom_values', 'v2mom_methods', 'v2mom_obstacles', 'v2mom_measures'])) {
            $pdo->prepare("DELETE FROM $table WHERE id = ? AND v2mom_id = ?")->execute([$id, $v2momId]);
            $message = "Item removido.";
        }
    }

    // E. AI Generation
    if ($_POST['action'] === 'generate_ai') {
        $type = $_POST['ai_type'];
        $context = $_POST['ai_context'] ?? '';

        $system = "Você é um consultor expert em V2MOM (Salesforce).";
        $userPrompt = getPrompts($type);
        if ($context)
            $userPrompt .= "\nContexto adicional: " . $context;

        $response = callOpenAI($pdo, $companyId, $system, $userPrompt, $v2momId);

        if (isset($response['error'])) {
            $message = "Erro IA: " . $response['error'];
        } else {
            // Save draft or display? For simplicity let's save to session or just show
            $generatedContent = $response['content'];
        }
    }
}

// --- 3. Fetch Data ---
$vision = $pdo->prepare("SELECT * FROM v2mom_vision WHERE v2mom_id = ?");
$vision->execute([$v2momId]);
$vision = $vision->fetch();
$values = $pdo->prepare("SELECT * FROM v2mom_values WHERE v2mom_id = ?");
$values->execute([$v2momId]);
$values = $values->fetchAll();
$methods = $pdo->prepare("SELECT m.*, u.name as owner_name FROM v2mom_methods m LEFT JOIN users u ON m.owner_id = u.id WHERE m.v2mom_id = ?");
$methods->execute([$v2momId]);
$methods = $methods->fetchAll();
$obstacles = $pdo->prepare("SELECT * FROM v2mom_obstacles WHERE v2mom_id = ?");
$obstacles->execute([$v2momId]);
$obstacles = $obstacles->fetchAll();
$measures = $pdo->prepare("SELECT * FROM v2mom_measures WHERE v2mom_id = ?");
$measures->execute([$v2momId]);
$measures = $measures->fetchAll();

// Users for Dropdown
$users = $pdo->prepare("SELECT id, name FROM users WHERE company_id = ? ORDER BY name");
$users->execute([$companyId]);
$userList = $users->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>V2MOM - Planejamento Estratégico</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .v2mom-nav {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--glass-border);
            padding-bottom: 1rem;
        }

        .nav-item {
            color: var(--text-muted);
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
        }

        .nav-item.active {
            background: var(--primary-color);
            color: white;
        }

        .card-item {
            background: rgba(255, 255, 255, 0.03);
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h1>V2MOM - Planejamento</h1>
                <a href="?tab=config" class="btn btn-outline" style="font-size:0.8rem;">⚙️ Config IA</a>
            </div>

            <?php if ($message): ?>
                <div
                    style="background:rgba(16,185,129,0.2); color:#34d399; padding:1rem; border-radius:0.5rem; margin-bottom:1rem;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            <?php if (isset($generatedContent)): ?>
                <div
                    style="background:rgba(59,130,246,0.1); border:1px solid #3b82f6; padding:1.5rem; border-radius:0.5rem; margin-bottom:2rem;">
                    <h4 style="color:#60a5fa; margin-top:0;">🤖 Sugestão da IA:</h4>
                    <div style="white-space:pre-wrap; color:#cbd5e1;">
                        <?php echo htmlspecialchars($generatedContent); ?>
                    </div>
                    <p style="font-size:0.8rem; color:#94a3b8; margin-top:1rem;">Copie e cole o texto acima nos campos
                        abaixo.</p>
                </div>
            <?php endif; ?>

            <nav class="v2mom-nav">
                <a href="?tab=vision" class="nav-item <?php echo $view == 'vision' ? 'active' : ''; ?>">1. Visão</a>
                <a href="?tab=values" class="nav-item <?php echo $view == 'values' ? 'active' : ''; ?>">2. Valores</a>
                <a href="?tab=methods" class="nav-item <?php echo $view == 'methods' ? 'active' : ''; ?>">3. Métodos</a>
                <a href="?tab=obstacles" class="nav-item <?php echo $view == 'obstacles' ? 'active' : ''; ?>">4.
                    Obstáculos</a>
                <a href="?tab=measures" class="nav-item <?php echo $view == 'measures' ? 'active' : ''; ?>">5.
                    Medidas</a>
            </nav>

            <!-- VISION TAB -->
            <?php if ($view === 'vision'): ?>
                <div class="mlpt-card">
                    <h3>Visão (Vision)</h3>
                    <p style="color:var(--text-muted);">O que queremos alcançar? A imagem do futuro.</p>
                    <form method="POST">
                        <input type="hidden" name="action" value="save_vision">
                        <textarea name="description"
                            style="width:100%; height:150px; background:rgba(0,0,0,0.2); border:1px solid var(--glass-border); color:white; padding:1rem; margin:1rem 0;"><?php echo htmlspecialchars($vision['description'] ?? ''); ?></textarea>

                        <div style="display:flex; gap:1rem;">
                            <button type="submit" class="btn btn-primary">Salvar Visão</button>
                        </div>
                    </form>

                    <hr style="border-color:var(--glass-border); margin:2rem 0;">
                    <form method="POST">
                        <input type="hidden" name="action" value="generate_ai">
                        <input type="hidden" name="ai_type" value="vision">
                        <div style="display:flex; align-items:center; gap:1rem;">
                            <input type="text" name="ai_context"
                                placeholder="Contexto: ex. Empresa de Tecnologia focada em educação..."
                                style="flex:1; padding:0.8rem; background:rgba(0,0,0,0.2); border:1px solid var(--glass-border); color:white;">
                            <button class="btn btn-outline" style="border-color:#818cf8; color:#818cf8;">✨ Gerar com
                                IA</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <!-- METHODS TAB (Using User Base) -->
            <?php if ($view === 'methods'): ?>
                <div class="mlpt-card">
                    <h3>Métodos (Methods)</h3>
                    <p style="color:var(--text-muted);">Como alcançaremos a visão? Ações e responsáveis.</p>

                    <div style="margin-bottom:2rem;">
                        <?php foreach ($methods as $m): ?>
                            <div class="card-item">
                                <div>
                                    <strong>
                                        <?php echo htmlspecialchars($m['description']); ?>
                                    </strong>
                                    <div style="font-size:0.8rem; color:var(--text-muted);">
                                        Resp:
                                        <?php echo htmlspecialchars($m['owner_name'] ?? 'Não atribuído'); ?> |
                                        Prazo:
                                        <?php echo htmlspecialchars($m['deadline']); ?>
                                    </div>
                                </div>
                                <?php if ($isManager): ?>
                                    <form method="POST" onsubmit="return confirm('Excluir?');">
                                        <input type="hidden" name="action" value="delete_item">
                                        <input type="hidden" name="table" value="v2mom_methods">
                                        <input type="hidden" name="item_id" value="<?php echo $m['id']; ?>">
                                        <button style="background:none; border:none; cursor:pointer;">🗑️</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($isManager): ?>
                        <form method="POST" style="background:rgba(0,0,0,0.2); padding:1.5rem; border-radius:0.5rem;">
                            <input type="hidden" name="action" value="add_method">
                            <div style="display:grid; gap:1rem;">
                                <label>Novo Método</label>
                                <input type="text" name="desc" required placeholder="Descreva a ação..."
                                    style="padding:0.8rem; background:#1e293b; border:1px solid var(--glass-border); color:white;">

                                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                                    <div>
                                        <label>Responsável</label>
                                        <select name="owner_id"
                                            style="width:100%; padding:0.8rem; background:#1e293b; border:1px solid var(--glass-border); color:white;">
                                            <option value="">Selecione...</option>
                                            <?php foreach ($userList as $u): ?>
                                                <option value="<?php echo $u['id']; ?>">
                                                    <?php echo htmlspecialchars($u['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label>Prazo</label>
                                        <input type="text" name="deadline" placeholder="ex: Dez/2026"
                                            style="width:100%; padding:0.8rem; background:#1e293b; border:1px solid var(--glass-border); color:white;">
                                    </div>
                                </div>
                                <button class="btn btn-primary">Adicionar Método</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- VALUES TAB -->
            <?php if ($view === 'values'): ?>
                <div class="mlpt-card">
                    <h3>Valores (Values)</h3>
                    <p style="color:var(--text-muted);">Quais princípios guiam nossas decisões?</p>

                    <div style="margin-bottom:2rem;">
                        <?php foreach ($values as $v): ?>
                            <div class="card-item">
                                <div><?php echo htmlspecialchars($v['description']); ?></div>
                                <?php if ($isManager): ?>
                                    <form method="POST" onsubmit="return confirm('Excluir?');">
                                        <input type="hidden" name="action" value="delete_item">
                                        <input type="hidden" name="table" value="v2mom_values">
                                        <input type="hidden" name="item_id" value="<?php echo $v['id']; ?>">
                                        <button style="background:none; border:none; cursor:pointer;">🗑️</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($isManager): ?>
                        <form method="POST" style="background:rgba(0,0,0,0.2); padding:1rem; border-radius:0.5rem;">
                            <input type="hidden" name="action" value="add_value">
                            <div style="display:flex; gap:1rem;">
                                <input type="text" name="desc" required placeholder="Novo Valor (ex: Inovação)"
                                    style="flex:1; padding:0.8rem; background:#1e293b; border:1px solid var(--glass-border); color:white;">
                                <button class="btn btn-primary">Adicionar</button>
                            </div>
                        </form>

                        <hr style="border-color:var(--glass-border); margin:2rem 0;">
                        <form method="POST">
                            <input type="hidden" name="action" value="generate_ai">
                            <input type="hidden" name="ai_type" value="values">
                            <button class="btn btn-outline" style="border-color:#818cf8; color:#818cf8;">✨ Sugerir Valores via
                                IA</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- OBSTACLES TAB -->
            <?php if ($view === 'obstacles'): ?>
                <div class="mlpt-card">
                    <h3>Obstáculos (Obstacles)</h3>
                    <p style="color:var(--text-muted);">O que pode nos impedir e como mitigar?</p>

                    <div style="margin-bottom:2rem;">
                        <?php foreach ($obstacles as $o): ?>
                            <div class="card-item" style="display:block;">
                                <div style="display:flex; justify-content:space-between;">
                                    <strong><?php echo htmlspecialchars($o['description']); ?></strong>
                                    <?php if ($isManager): ?>
                                        <form method="POST" onsubmit="return confirm('Excluir?');" style="display:inline;">
                                            <input type="hidden" name="action" value="delete_item">
                                            <input type="hidden" name="table" value="v2mom_obstacles">
                                            <input type="hidden" name="item_id" value="<?php echo $o['id']; ?>">
                                            <button style="background:none; border:none; cursor:pointer;">🗑️</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                                <div style="font-size:0.9rem; color:#fca5a5; margin-top:0.5rem;">Impacto:
                                    <?php echo ucfirst($o['impact']); ?></div>
                                <div style="font-size:0.9rem; color:#94a3b8; margin-top:0.2rem;">Mitigação:
                                    <?php echo htmlspecialchars($o['mitigation_plan'] ?? '-'); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($isManager): ?>
                        <form method="POST" style="background:rgba(0,0,0,0.2); padding:1rem; border-radius:0.5rem;">
                            <input type="hidden" name="action" value="add_obstacle">
                            <div style="display:grid; gap:1rem;">
                                <input type="text" name="desc" required placeholder="Descreva o obstáculo..."
                                    style="padding:0.8rem; background:#1e293b; border:1px solid var(--glass-border); color:white;">
                                <input type="text" name="mitigation" placeholder="Plano de mitigação..."
                                    style="padding:0.8rem; background:#1e293b; border:1px solid var(--glass-border); color:white;">
                                <select name="impact"
                                    style="padding:0.8rem; background:#1e293b; border:1px solid var(--glass-border); color:white;">
                                    <option value="medium">Impacto Médio</option>
                                    <option value="high">Impacto Alto</option>
                                    <option value="low">Impacto Baixo</option>
                                </select>
                                <button class="btn btn-primary">Adicionar Obstáculo</button>
                            </div>
                        </form>

                        <hr style="border-color:var(--glass-border); margin:2rem 0;">
                        <form method="POST">
                            <input type="hidden" name="action" value="generate_ai">
                            <input type="hidden" name="ai_type" value="obstacles">
                            <button class="btn btn-outline" style="border-color:#818cf8; color:#818cf8;">✨ Analisar Riscos via
                                IA</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- MEASURES TAB -->
            <?php if ($view === 'measures'): ?>
                <div class="mlpt-card">
                    <h3>Medidas (Measures)</h3>
                    <p style="color:var(--text-muted);">Como saberemos que tivemos sucesso?</p>

                    <div style="margin-bottom:2rem;">
                        <?php foreach ($measures as $ms): ?>
                            <div class="card-item">
                                <div>
                                    <strong><?php echo htmlspecialchars($ms['description']); ?></strong>
                                    <div style="font-size:0.8rem; color:var(--text-muted);">Meta:
                                        <?php echo htmlspecialchars($ms['target'] . ' ' . $ms['unit']); ?> | Atual:
                                        <?php echo htmlspecialchars($ms['current_value']); ?></div>
                                </div>
                                <?php if ($isManager): ?>
                                    <form method="POST" onsubmit="return confirm('Excluir?');">
                                        <input type="hidden" name="action" value="delete_item">
                                        <input type="hidden" name="table" value="v2mom_measures">
                                        <input type="hidden" name="item_id" value="<?php echo $ms['id']; ?>">
                                        <button style="background:none; border:none; cursor:pointer;">🗑️</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($isManager): ?>
                        <form method="POST" style="background:rgba(0,0,0,0.2); padding:1rem; border-radius:0.5rem;">
                            <input type="hidden" name="action" value="add_measure">
                            <div style="display:grid; gap:1rem;">
                                <input type="text" name="desc" required placeholder="Indicador (KPI)..."
                                    style="padding:0.8rem; background:#1e293b; border:1px solid var(--glass-border); color:white;">
                                <input type="text" name="target" placeholder="Meta Alvo (ex: 100)"
                                    style="padding:0.8rem; background:#1e293b; border:1px solid var(--glass-border); color:white;">
                                <input type="text" name="unit" placeholder="Unidade (ex: %)"
                                    style="padding:0.8rem; background:#1e293b; border:1px solid var(--glass-border); color:white;">
                                <button class="btn btn-primary">Adicionar Medida</button>
                            </div>
                        </form>

                        <hr style="border-color:var(--glass-border); margin:2rem 0;">
                        <form method="POST">
                            <input type="hidden" name="action" value="generate_ai">
                            <input type="hidden" name="ai_type" value="measures">
                            <button class="btn btn-outline" style="border-color:#818cf8; color:#818cf8;">✨ Sugerir KPIs via
                                IA</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- CONFIG TAB -->
            <?php if ($view === 'config'): ?>
                <div class="mlpt-card">
                    <h3>Configuração IA</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="save_config">
                        <label>OpenAI API Key</label>
                        <input type="password" name="api_key" placeholder="sk-..."
                            style="width:100%; padding:0.8rem; margin:0.5rem 0; background:rgba(0,0,0,0.2); border:1px solid var(--glass-border); color:white;">
                        <button class="btn btn-primary">Salvar Chave</button>
                    </form>
                </div>
            <?php endif; ?>

        </main>
    </div>
</body>

</html>
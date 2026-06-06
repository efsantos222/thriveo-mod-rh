<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$companyId = $_SESSION['company_id'] ?? 1;

$userRole = $_SESSION['role'];
$mode = $_GET['mode'] ?? 'play';
$isAdmin = ($userRole === 'admin' || $userRole === 'responsible' || $userRole === 'manager');

// Auto-switch to admin if authorized and no mode set
if ($isAdmin && !isset($_GET['mode']))
    $mode = 'admin';

$message = '';

// --- ACTIONS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. ADD PHRASE
    if ($_POST['action'] === 'add_phrase') {
        if ($content = trim($_POST['content'])) {
            $pdo->prepare("INSERT INTO bingo_phrases (company_id, content) VALUES (?, ?)")->execute([$companyId, $content]);
            $message = "Frase adicionada!";
        }
    }

    // 2. DELETE PHRASE
    if ($_POST['action'] === 'del_phrase') {
        $pdo->prepare("DELETE FROM bingo_phrases WHERE id = ?")->execute([$_POST['id']]);
    }

    // 3. DRAW NEXT PHRASE
    if ($_POST['action'] === 'draw_phrase') {
        // Get all drawn IDs
        $drawn = $pdo->prepare("SELECT phrase_id FROM bingo_draws WHERE company_id = ?");
        $drawn->execute([$companyId]);
        $drawnIds = $drawn->fetchAll(PDO::FETCH_COLUMN);

        // Find undrawn phrases
        $sql = "SELECT id FROM bingo_phrases WHERE company_id = ?";
        if (!empty($drawnIds)) {
            $placeholders = implode(',', array_fill(0, count($drawnIds), '?'));
            $sql .= " AND id NOT IN ($placeholders)";
            $params = array_merge([$companyId], $drawnIds);
        } else {
            $params = [$companyId];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $available = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($available)) {
            $randomId = $available[array_rand($available)];
            $pdo->prepare("INSERT INTO bingo_draws (company_id, phrase_id) VALUES (?, ?)")->execute([$companyId, $randomId]);
            $message = "Frase sorteada com sucesso!";
        } else {
            $message = "Todas as frases já foram sorteadas!";
        }
    }

    // 4. RESET GAME
    if ($_POST['action'] === 'reset_game') {
        $pdo->prepare("DELETE FROM bingo_draws WHERE company_id = ?")->execute([$companyId]);
        $message = "Jogo reiniciado! Boa sorte.";
    }

    // 5. ADD PLAYER
    if ($_POST['action'] === 'add_player') {
        if ($name = trim($_POST['name'])) {
            // Generate Card (3x3 = 9 items)
            $stmt = $pdo->prepare("SELECT id FROM bingo_phrases WHERE company_id = ? ORDER BY RAND() LIMIT 9");
            $stmt->execute([$companyId]);
            $cardPhrases = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (count($cardPhrases) < 9) {
                $message = "Erro: Cadastre pelo menos 9 frases antes de adicionar jogadores.";
            } else {
                $json = json_encode($cardPhrases);
                $pdo->prepare("INSERT INTO bingo_players (company_id, name, card_json) VALUES (?, ?, ?)")
                    ->execute([$companyId, $name, $json]);
                $message = "Jogador adicionado com cartela!";
            }
        }
    }

    // 6. DELETE PLAYER
    if ($_POST['action'] === 'del_player') {
        $pdo->prepare("DELETE FROM bingo_players WHERE id = ?")->execute([$_POST['id']]);
    }
}

// --- DATA FETCHING ---
// Stats
$totalPhrases = $pdo->prepare("SELECT COUNT(*) FROM bingo_phrases WHERE company_id = ?");
$totalPhrases->execute([$companyId]);
$totalPhrases = $totalPhrases->fetchColumn();

$totalDrawn = $pdo->prepare("SELECT COUNT(*) FROM bingo_draws WHERE company_id = ?");
$totalDrawn->execute([$companyId]);
$totalDrawn = $totalDrawn->fetchColumn();

$totalPlayers = $pdo->prepare("SELECT COUNT(*) FROM bingo_players WHERE company_id = ?");
$totalPlayers->execute([$companyId]);
$totalPlayers = $totalPlayers->fetchColumn();

$remaining = $totalPhrases - $totalDrawn;

// Current Phase
$currentPhrase = "Nenhuma frase sorteada";
$stmt = $pdo->prepare("SELECT p.content FROM bingo_draws d JOIN bingo_phrases p ON d.phrase_id = p.id WHERE d.company_id = ? ORDER BY d.drawn_at DESC LIMIT 1");
$stmt->execute([$companyId]);
if ($row = $stmt->fetch())
    $currentPhrase = $row['content'];

// All Drawn Phrases (History)
$history = $pdo->prepare("SELECT p.content, p.id FROM bingo_draws d JOIN bingo_phrases p ON d.phrase_id = p.id WHERE d.company_id = ? ORDER BY d.drawn_at DESC");
$history->execute([$companyId]);
$drawnHistory = $history->fetchAll();
$drawnIds = array_column($drawnHistory, 'id');

// Phrases List
$phrases = $pdo->prepare("SELECT * FROM bingo_phrases WHERE company_id = ? ORDER BY id DESC");
$phrases->execute([$companyId]);
$allPhrases = $phrases->fetchAll();

// Players
$players = $pdo->prepare("SELECT * FROM bingo_players WHERE company_id = ? ORDER BY id DESC");
$players->execute([$companyId]);
$allPlayers = $players->fetchAll();

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Bingo Corporativo - Thriveo Engajex</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .app-layout {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .main-content {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
            background: #f8fafc;
            color: #1e293b;
        }

        /* Light theme override for this specific module as per image */

        /* OVERRIDES FOR LIGHT THEME REQUESTED IN IMAGE */
        /* If user wants dark mode compliant with system, remove these overrides. Attempting hybrid. */
        body {
            background: #f8fafc;
        }

        .main-content {
            background: #fdf2f8;
            /* Pale Pinkish BG */
        }

        /* CARDS */
        .b-card {
            background: white;
            border: 2px solid #000;
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: 4px 4px 0px rgba(0, 0, 0, 1);
            /* Retro Brutalist shadow */
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .b-title {
            font-weight: 800;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* GRID LAYOUT */
        .admin-grid {
            display: grid;
            grid-template-columns: 350px 1fr;
            grid-template-rows: auto auto;
            gap: 1.5rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* CONTROL PANEL */
        .control-panel .status-box {
            background: linear-gradient(to right, #fca5a5, #fecaca);
            border-radius: 0.5rem;
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 1rem;
            border: 1px solid #f87171;
        }

        .current-label {
            font-size: 0.8rem;
            color: white;
            opacity: 0.9;
        }

        .current-text {
            font-size: 1.2rem;
            font-weight: 800;
            color: #991b1b;
            margin-top: 0.5rem;
        }

        .btn-draw {
            width: 100%;
            padding: 1rem;
            background: #86efac;
            color: #14532d;
            font-weight: 700;
            border: none;
            border-radius: 2rem;
            cursor: pointer;
            transition: transform 0.1s;
            margin-bottom: 1rem;
        }

        .btn-draw:hover {
            transform: scale(1.02);
        }

        .btn-reset {
            width: 100%;
            padding: 0.75rem;
            background: #fda4af;
            color: #9f1239;
            font-weight: 600;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
        }

        .stats-row {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .stat-item {
            flex: 1;
            background: #f1f5f9;
            padding: 0.75rem;
            text-align: center;
            border-radius: 0.5rem;
        }

        .stat-val {
            font-weight: 800;
            font-size: 1.2rem;
        }

        .stat-lbl {
            font-size: 0.7rem;
            color: #64748b;
        }

        /* LISTS */
        .scroll-list {
            max-height: 200px;
            overflow-y: auto;
        }

        .list-item {
            padding: 0.5rem;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.9rem;
            display: flex;
            justify-content: space-between;
        }

        /* BINGO CARD PREVIEW */
        .mini-card-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2px;
            background: #e2e8f0;
            border: 1px solid #cbd5e1;
            width: fit-content;
        }

        .mini-cell {
            width: 12px;
            height: 12px;
            background: white;
        }

        .mini-cell.marked {
            background: #10b981;
        }

        /* PLAYER CARD (Full Size) */
        .player-bingo-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.5rem;
            margin: 2rem auto;
            max-width: 500px;
        }

        .bingo-cell {
            aspect-ratio: 1;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem;
            text-align: center;
            font-weight: 600;
            font-size: 0.9rem;
            color: #475569;
            transition: all 0.3s;
        }

        .bingo-cell.marked {
            background: #10b981;
            color: white;
            border-color: #059669;
            transform: scale(1.05);
            box-shadow: 0 4px 6px rgba(16, 185, 129, 0.4);
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include '../includes/sidebar.php'; ?>

        <main class="main-content">

            <!-- ADMIN VIEW -->
            <?php if ($mode === 'admin'): ?>
                <div class="admin-grid">

                    <!-- 1. Game Control -->
                    <div class="b-card control-panel" style="grid-row: span 1;">
                        <div class="b-title">🎛 Controle do Jogo</div>

                        <div class="status-box">
                            <div class="current-label">Frase Atual:</div>
                            <div class="current-text">"<?php echo htmlspecialchars($currentPhrase); ?>"</div>
                        </div>

                        <form method="POST">
                            <input type="hidden" name="action" value="draw_phrase">
                            <button class="btn-draw">Sortear Próxima Frase</button>
                        </form>

                        <div class="stats-row">
                            <div class="stat-item">
                                <div class="stat-val"><?php echo $totalDrawn; ?></div>
                                <div class="stat-lbl">Sorteadas</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-val"><?php echo $remaining; ?></div>
                                <div class="stat-lbl">Restantes</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-val"><?php echo $totalPlayers; ?></div>
                                <div class="stat-lbl">Jogadores</div>
                            </div>
                        </div>

                        <form method="POST"
                            onsubmit="return confirm('Reiniciar o jogo apagará todo o histórico de sorteio. Continuar?');">
                            <input type="hidden" name="action" value="reset_game">
                            <button class="btn-reset">Reiniciar Jogo Completo</button>
                        </form>
                    </div>

                    <!-- 2. Manage Phrases -->
                    <div class="b-card">
                        <div class="b-title">
                            <span>📝 Gerenciar Frases (<?php echo $totalPhrases; ?>)</span>
                        </div>

                        <form method="POST" style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                            <input type="hidden" name="action" value="add_phrase">
                            <input type="text" name="content" class="form-control" placeholder="Digite uma nova frase..."
                                style="background: #f1f5f9; border: 1px solid #cbd5e1; color:black;">
                            <button
                                style="background: none; border: 1px solid #cbd5e1; border-radius: 4px; cursor: pointer;">➕</button>
                        </form>

                        <div class="scroll-list">
                            <?php foreach ($allPhrases as $p): ?>
                                <div class="list-item">
                                    <span><?php echo htmlspecialchars($p['content']); ?></span>
                                    <form method="POST" style="margin:0"><input type="hidden" name="action"
                                            value="del_phrase"><input type="hidden" name="id"
                                            value="<?php echo $p['id']; ?>"><button
                                            style="color:red; background:none; border:none; cursor:pointer;">&times;</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- 3. Drawn History -->
                    <div class="b-card">
                        <div class="b-title">📊 Histórico (<?php echo $totalDrawn; ?>)</div>
                        <div class="scroll-list">
                            <?php if (empty($drawnHistory)): ?>
                                <p style="color:#94a3b8; font-size:0.9rem; text-align:center; padding:1rem;">Nenhuma frase
                                    sorteada ainda</p><?php endif; ?>
                            <?php foreach ($drawnHistory as $h): ?>
                                <div class="list-item" style="color: #64748b;">
                                    <span>✔ <?php echo htmlspecialchars($h['content']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- 4. Players Monitoring -->
                    <div class="b-card" style="grid-row: span 2;">
                        <div class="b-title">👀 Acompanhamento (<?php echo $totalPlayers; ?>)</div>

                        <?php if (empty($allPlayers)): ?>
                            <div style="text-align:center; padding: 2rem; color: #94a3b8;">
                                <div style="font-size: 2rem;">▦</div>
                                <p>Nenhuma cartela criada ainda.</p>
                            </div>
                        <?php endif; ?>

                        <div
                            style="display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 1rem; max-height: 400px; overflow-y: auto;">
                            <?php foreach ($allPlayers as $pl):
                                $card = json_decode($pl['card_json'], true);
                                $matches = 0;
                                foreach ($card as $cid) {
                                    if (in_array($cid, $drawnIds))
                                        $matches++;
                                }
                                $isWinner = ($matches === 9); // Assuming 3x3 full card bingo for simplicity
                                ?>
                                <div
                                    style="background:<?php echo $isWinner ? '#bbf7d0' : '#f8fafc'; ?>; border:1px solid #e2e8f0; padding:0.5rem; border-radius:0.5rem;">
                                    <strong
                                        style="display:block; font-size:0.8rem; margin-bottom:0.25rem;"><?php echo htmlspecialchars($pl['name']); ?></strong>
                                    <div style="display:flex; justify-content:space-between; align-items:center;">
                                        <div class="mini-card-grid">
                                            <?php foreach ($card as $cid): ?>
                                                <div class="mini-cell <?php echo in_array($cid, $drawnIds) ? 'marked' : ''; ?>"></div>
                                            <?php endforeach; ?>
                                        </div>
                                        <span
                                            style="font-size:0.8rem; font-weight:700; color:<?php echo $isWinner ? '#15803d' : '#64748b'; ?>"><?php echo $matches; ?>/9</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- 5. Manage Players -->
                    <div class="b-card">
                        <div class="b-title">👥 Gerenciar Jogadores</div>
                        <form method="POST" style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                            <input type="hidden" name="action" value="add_player">
                            <input type="text" name="name" class="form-control" placeholder="Nome do jogador..."
                                style="background: #f1f5f9; border: 1px solid #cbd5e1; color:black;">
                            <button
                                style="background: none; border: 1px solid #cbd5e1; border-radius: 4px; cursor: pointer;">➕</button>
                        </form>
                        <div class="scroll-list">
                            <?php foreach ($allPlayers as $pl): ?>
                                <div class="list-item">
                                    <span><?php echo htmlspecialchars($pl['name']); ?></span>
                                    <div style="display:flex; gap:0.5rem;">
                                        <a href="?mode=play&player_id=<?php echo $pl['id']; ?>" target="_blank"
                                            style="text-decoration:none; font-size:0.8rem;">👁 Ver</a>
                                        <form method="POST" style="margin:0"><input type="hidden" name="action"
                                                value="del_player"><input type="hidden" name="id"
                                                value="<?php echo $pl['id']; ?>"><button
                                                style="color:red; background:none; border:none; cursor:pointer;">&times;</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
            <?php endif; ?>

            <!-- PLAYER VIEW -->
            <?php if ($mode === 'play'):
                $playerId = $_GET['player_id'] ?? null;
                // If no player ID, show a simple "Lobby" or select self if relevant
                // For this demo, we assume the link comes from admin or is selected
                if (!$playerId):
                    ?>
                    <div style="text-align: center; margin-top: 3rem;">
                        <h1>Bem-vindo ao Bingo Corporativo!</h1>
                        <p>Por favor, solicite sua cartela ao administrador ou acesse pelo link enviado.</p>
                    </div>
                <?php else:
                    // Fetch Player Card
                    $stmt = $pdo->prepare("SELECT * FROM bingo_players WHERE id = ?");
                    $stmt->execute([$playerId]);
                    $player = $stmt->fetch();
                    if (!$player)
                        die("Jogador não encontrado.");
                    $cardItems = json_decode($player['card_json'], true);

                    // Fetch content for card items
                    $qs = implode(',', $cardItems);
                    $stmt = $pdo->query("SELECT id, content FROM bingo_phrases WHERE id IN ($qs)");
                    $phrasesMap = [];
                    while ($r = $stmt->fetch())
                        $phrasesMap[$r['id']] = $r['content'];
                    ?>
                    <div style="max-width: 600px; margin: 0 auto; text-align: center;">
                        <h1>Cartela de <?php echo htmlspecialchars($player['name']); ?></h1>
                        <p style="margin-bottom: 2rem;">Marque os itens conforme forem falando na reunião!</p>

                        <div class="player-bingo-grid">
                            <?php foreach ($cardItems as $cId):
                                $isMarked = in_array($cId, $drawnIds);
                                ?>
                                <div class="bingo-cell <?php echo $isMarked ? 'marked' : ''; ?>">
                                    <?php echo htmlspecialchars($phrasesMap[$cId] ?? '???'); ?>
                                    <?php if ($isMarked): ?>
                                        <div style="position: absolute; font-size: 2rem; opacity: 0.3; color: black;">✔</div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <script>
                        // Simple auto-reload to fetch updates (Polling)
                        setTimeout(function () { location.reload(); }, 5000);
                    </script>
                <?php endif; endif; ?>

        </main>
    </div>
</body>

</html>
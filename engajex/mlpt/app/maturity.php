<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkAuth('responsible');

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cred = intval($_POST['credibility']);
    $resp = intval($_POST['respect']);
    $imp = intval($_POST['impartiality']);
    $pride = intval($_POST['pride']);
    $cam = intval($_POST['camaraderie']);

    $overall = ($cred + $resp + $imp + $pride + $cam) / 5;

    $stmt = $pdo->prepare("INSERT INTO maturity_assessments (company_id, credibility_score, respect_score, impartiality_score, pride_score, camaraderie_score, overall_score) VALUES (?, ?, ?, ?, ?, ?, ?)");

    try {
        $stmt->execute([$_SESSION['company_id'], $cred, $resp, $imp, $pride, $cam, $overall]);
        $success = "Avaliação salva com sucesso!";
    } catch (PDOException $e) {
        $error = "Erro ao salvar avaliação.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Avaliação de Maturidade - MLPT</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .app-layout {
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
            padding: 40px;
            border-radius: 16px;
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .range-group {
            margin-bottom: 30px;
        }

        .range-group label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .range-group input[type="range"] {
            width: 100%;
            height: 6px;
            background: #334155;
            border-radius: 5px;
            appearance: none;
            outline: none;
        }

        .range-group input[type="range"]::-webkit-slider-thumb {
            appearance: none;
            width: 20px;
            height: 20px;
            background: var(--primary);
            border-radius: 50%;
            cursor: pointer;
        }

        .score-val {
            color: var(--primary);
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <div class="sidebar">
            <div class="logo">MLPT System</div>
            <a href="index.php" class="menu-item"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
            <a href="maturity.php" class="menu-item active"><i class="fa-solid fa-sliders"></i> Avaliação Maturidade</a>
            <a href="documents.php" class="menu-item"><i class="fa-solid fa-file-contract"></i> Análise Documental</a>
            <a href="trust_index.php" class="menu-item"><i class="fa-solid fa-flask"></i> Simulador Trust Index</a>
            <a href="action_plan.php" class="menu-item"><i class="fa-solid fa-list-check"></i> Planos de Ação</a>
            <a href="../logout.php" class="menu-item"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
        </div>
        <div class="content">
            <h2 style="margin-bottom: 30px;">Nova Avaliação de Maturidade</h2>

            <?php if ($success): ?>
                <div style="color: #10b981; margin-bottom: 20px;"><?php echo $success; ?></div> <?php endif; ?>

            <div class="form-card">
                <form method="POST">
                    <p style="margin-bottom: 30px; color: var(--text-dim);">Auto-avalie a empresa de 0 a 100 em cada um
                        dos pilares.</p>

                    <div class="range-group">
                        <label>Credibilidade <span id="val-cred" class="score-val">50</span></label>
                        <input type="range" name="credibility" min="0" max="100" value="50"
                            oninput="updateVal('cred', this.value)">
                    </div>

                    <div class="range-group">
                        <label>Respeito <span id="val-resp" class="score-val">50</span></label>
                        <input type="range" name="respect" min="0" max="100" value="50"
                            oninput="updateVal('resp', this.value)">
                    </div>

                    <div class="range-group">
                        <label>Imparcialidade <span id="val-imp" class="score-val">50</span></label>
                        <input type="range" name="impartiality" min="0" max="100" value="50"
                            oninput="updateVal('imp', this.value)">
                    </div>

                    <div class="range-group">
                        <label>Orgulho <span id="val-pride" class="score-val">50</span></label>
                        <input type="range" name="pride" min="0" max="100" value="50"
                            oninput="updateVal('pride', this.value)">
                    </div>

                    <div class="range-group">
                        <label>Camaradagem <span id="val-cam" class="score-val">50</span></label>
                        <input type="range" name="camaraderie" min="0" max="100" value="50"
                            oninput="updateVal('cam', this.value)">
                    </div>

                    <button type="submit" class="btn btn-primary btn-full">Salvar Avaliação</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function updateVal(id, val) {
            document.getElementById('val-' + id).innerText = val;
        }
    </script>
</body>

</html>
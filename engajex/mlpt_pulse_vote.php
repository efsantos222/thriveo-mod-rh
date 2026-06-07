<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

$success = false;
$error = '';
$company = 'Sua Empresa';

$survey_id = filter_input(INPUT_GET, 's', FILTER_VALIDATE_INT);

try {
    // Verify survey validity
    if ($survey_id) {
        // First try to fetch just the survey to ensure basic connectivity
        $stmt = $pdo->prepare("SELECT * FROM mlpt_pulse_surveys WHERE id = ? AND status = 'active'");
        $stmt->execute([$survey_id]);
        $survey = $stmt->fetch();

        if ($survey) {
            // Check if we can get company name, fail gracefully if not
            try {
                // Assuming 'empresas' table exists based on previous project analysis.
                // If this fails, we catch it and just keep 'Sua Empresa'
                $stmtComp = $pdo->prepare("SELECT nome_empresa FROM empresas WHERE id = ?");
                $stmtComp->execute([$survey['company_id']]);
                $compRow = $stmtComp->fetch();
                if ($compRow) {
                    $company = $compRow['nome_empresa'];
                }
            } catch (Exception $e) {
                // Ignore company name lookup error
            }
        } else {
            $error = "Pesquisa não encontrada ou já encerrada.";
        }
    } else {
        $error = "Link inválido.";
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($survey) && $survey && !$error) {
        $rating = intval($_POST['rating']);
        $comment = $_POST['comment'];

        if ($rating >= 1 && $rating <= 10) {
            $stmt = $pdo->prepare("INSERT INTO mlpt_pulse_responses (survey_id, rating, comment) VALUES (?, ?, ?)");
            $stmt->execute([$survey_id, $rating, $comment]);
            $success = true;
        } else {
            $error = "Por favor, selecione uma nota.";
        }
    }

} catch (Exception $e) {
    $error = "Erro no sistema: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesquisa de Clima - MLPT</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
        }

        body {
            background: #0f172a;
            color: #f8fafc;
            font-family: 'Outfit', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .vote-card {
            background: #1e293b;
            padding: 40px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            max-width: 500px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .rating-options {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
            gap: 5px;
        }

        .rating-opt {
            flex: 1;
            padding: 10px 0;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 600;
        }

        .rating-opt:hover,
        input[type="radio"]:checked+.rating-opt {
            background: var(--primary);
            border-color: var(--primary);
            box-shadow: 0 0 15px rgba(99, 102, 241, 0.5);
        }

        input[type="radio"] {
            display: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
            font-size: 1rem;
        }

        .btn-primary:hover {
            opacity: 0.9;
        }
    </style>
</head>

<body>
    <div class="vote-card">
        <?php if ($success): ?>
            <h1 style="color: #10b981; margin-bottom: 1rem;">Obrigado!</h1>
            <p style="color: #cbd5e1; line-height: 1.6;">Sua opinião é fundamental para construirmos um lugar melhor para
                trabalhar.</p>
        <?php elseif ($error): ?>
            <h1 style="color: #f87171; margin-bottom: 1rem;">Ops!</h1>
            <p style="color: #cbd5e1;"><?php echo htmlspecialchars($error); ?></p>
            <?php else: ?>
            <h2 style="margin-top: 0;">
                <?php echo htmlspecialchars($survey['title'] ?? 'Pulso de Clima'); ?>
            </h2>
            <p style="color: #94a3b8; margin-top: 5px; font-size: 0.9rem;"><?php echo htmlspecialchars($company); ?></p>

            <form method="POST">
                <p style="margin-top: 30px; font-size: 1.1rem;">Em uma escala de 1 a 10, o quanto você recomendaria esta
                    empresa para trabalhar?</p>

                <div class="rating-options">
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                        <label>
                            <input type="radio" name="rating" value="<?php echo $i; ?>" required>
                            <div class="rating-opt"><?php echo $i; ?></div>
                        </label>
                    <?php endfor; ?>
                </div>

                <div style="text-align: left; margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 10px; color: #cbd5e1; font-size: 0.9rem;">O que poderíamos
                        melhorar? (Opcional)</label>
                    <textarea name="comment"
                        style="width: 100%; height: 100px; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: white; padding: 10px; font-family: inherit; resize: vertical; box-sizing: border-box;"></textarea>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%;">Enviar Resposta Anônima</button>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>
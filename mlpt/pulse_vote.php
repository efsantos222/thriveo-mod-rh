<?php
require_once 'config/database.php';

$success = false;
$error = '';
$company = null;

$survey_id = filter_input(INPUT_GET, 's', FILTER_VALIDATE_INT);

// Verify survey validity
if ($survey_id) {
    $stmt = $pdo->prepare("SELECT s.*, c.name as company_name FROM pulse_surveys s JOIN companies c ON s.company_id = c.id WHERE s.id = ? AND s.status = 'active'");
    $stmt->execute([$survey_id]);
    $survey = $stmt->fetch();

    if ($survey) {
        $company = $survey['company_name'];
    } else {
        $error = "Pesquisa não encontrada ou já encerrada.";
    }
} else {
    $error = "Link inválido.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $survey && !$error) {
    $rating = intval($_POST['rating']);
    $comment = $_POST['comment'];

    if ($rating >= 1 && $rating <= 10) {
        $stmt = $pdo->prepare("INSERT INTO pulse_responses (survey_id, rating, comment) VALUES (?, ?, ?)");
        try {
            $stmt->execute([$survey_id, $rating, $comment]);
            $success = true;
        } catch (Exception $e) {
            $error = "Erro ao registrar voto.";
        }
    } else {
        $error = "Por favor, selecione uma nota.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesquisa de Clima</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: #0f172a;
            color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
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
        }

        .rating-opt:hover,
        input[type="radio"]:checked+.rating-opt {
            background: var(--primary);
            border-color: var(--primary);
        }

        input[type="radio"] {
            display: none;
        }
    </style>
</head>

<body>
    <div class="vote-card">
        <?php if ($success): ?>
            <h2 style="color: #10b981;">Obrigado!</h2>
            <p>Sua opinião é fundamental para construirmos um lugar melhor para trabalhar.</p>
        <?php elseif ($error): ?>
            <h2 style="color: #ef4444;">Ops!</h2>
            <p><?php echo $error; ?></p>
        <?php else: ?>
            <h2>Pulso de Clima</h2>
            <p style="color: #94a3b8; margin-top: 10px;"><?php echo htmlspecialchars($company); ?></p>

            <form method="POST">
                <p style="margin-top: 30px;">Em uma escala de 1 a 10, o quanto você recomendaria esta empresa para
                    trabalhar?</p>

                <div class="rating-options">
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                        <label>
                            <input type="radio" name="rating" value="<?php echo $i; ?>" required>
                            <div class="rating-opt"><?php echo $i; ?></div>
                        </label>
                    <?php endfor; ?>
                </div>

                <div style="text-align: left; margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 10px;">O que poderíamos melhorar? (Opcional)</label>
                    <textarea name="comment"
                        style="width: 100%; height: 100px; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: white; padding: 10px;"></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Enviar Resposta Anônima</button>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>
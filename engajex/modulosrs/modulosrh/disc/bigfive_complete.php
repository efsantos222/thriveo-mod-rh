<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliação Big Five Concluída</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .success-icon {
            font-size: 4rem;
            color: #198754;
        }
        .completion-card {
            max-width: 600px;
            margin: 50px auto;
            text-align: center;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="completion-card">
            <i class="bi bi-check-circle-fill success-icon mb-4"></i>
            <h2 class="mb-4">Avaliação Concluída!</h2>
            <p class="lead mb-4">Obrigado por completar a avaliação Big Five Personality Traits.</p>
            <p class="mb-4">Suas respostas foram registradas com sucesso.</p>
            <p class="text-muted">O responsável pela avaliação entrará em contato quando os resultados estiverem disponíveis.</p>
            <div class="mt-4">
                <a href="index.php" class="btn btn-primary">
                    <i class="bi bi-house"></i> Voltar para a Página Inicial
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

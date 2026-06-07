<?php
session_start();

if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obrigado - Teste MBTI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <?php include '../includes/candidate_header.php'; ?>
        
        <div class="card">
            <div class="card-body text-center">
                <h2 class="card-title mb-4">Obrigado por completar o teste MBTI!</h2>
                <p class="lead">Suas respostas foram registradas com sucesso.</p>
                <p>O resultado será analisado e enviado para o responsável pela seleção.</p>
                <hr>
                <p class="mb-0">Você pode fechar esta janela agora.</p>
                
                <div class="mt-4">
                    <a href="../logout.php" class="btn btn-primary">Sair</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

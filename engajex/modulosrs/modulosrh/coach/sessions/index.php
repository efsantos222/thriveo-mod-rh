<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sessões - Sistema de Coaching</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/sessions.css">
</head>
<body>
    <div class="dashboard-container">
        <nav class="sidebar">
            <div class="user-info">
                <h3>Bem-vindo(a),</h3>
                <p><?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
            </div>
            <ul class="menu">
                <li><a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="../users/index.php"><i class="fas fa-users"></i> Usuários</a></li>
                <li><a href="#" class="active"><i class="fas fa-calendar"></i> Sessões</a></li>
                <li><a href="../questionnaires/index.php"><i class="fas fa-clipboard-list"></i> Questionários</a></li>
                <li><a href="../goals/index.php"><i class="fas fa-bullseye"></i> Metas SMART</a></li>
                <li><a href="../feedback/index.php"><i class="fas fa-comments"></i> Feedback</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
            </ul>
        </nav>
        <main class="content">
            <header class="dashboard-header">
                <h1>Gerenciar Sessões</h1>
                <button class="btn-primary" onclick="openNewSessionModal()">
                    <i class="fas fa-plus"></i> Nova Sessão
                </button>
            </header>
            
            <div class="sessions-grid">
                <!-- Sessions will be loaded here via JavaScript -->
            </div>
        </main>
    </div>

    <script src="../assets/js/sessions.js"></script>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Coaching</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>

<body>
    <div class="dashboard-container">
        <nav class="sidebar">
            <div class="user-info">
                <h3>Bem-vindo(a),</h3>
                <p><?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
            </div>
            <ul class="menu">
                <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                <?php if (in_array($_SESSION['user_profile'], ['administrador', 'master_coach'])): ?>
                    <li><a href="users/index.php"><i class="fas fa-users"></i>
                            <?php echo $_SESSION['user_profile'] === 'master_coach' ? 'Mentorados' : 'Usuários'; ?></a></li>
                <?php endif; ?>
                <?php if ($_SESSION['user_profile'] === 'administrador'): ?>
                    <li><a href="companies/index.php"><i class="fas fa-building"></i> Empresas</a></li>
                <?php endif; ?>
                <li><a href="sessions/index.php"><i class="fas fa-calendar"></i> Sessões</a></li>
                <li><a href="questionnaires/index.php"><i class="fas fa-clipboard-list"></i> Questionários</a></li>
                <li><a href="goals/index.php"><i class="fas fa-bullseye"></i> Metas SMART</a></li>
                <li><a href="feedback/index.php"><i class="fas fa-comments"></i> Feedback</a></li>
                <li><a href="auth/logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
            </ul>
        </nav>
        <main class="content">
            <header class="dashboard-header">
                <h1>Dashboard</h1>
            </header>
            <div class="dashboard-grid">
                <?php if ($_SESSION['user_profile'] === 'administrador'): ?>
                    <div class="card">
                        <h3>Gerenciamento de Usuários</h3>
                        <div class="card-content">
                            <p>Gerencie coaches e participantes do sistema.</p>
                            <a href="users/index.php" class="btn-primary">
                                <i class="fas fa-users"></i> Gerenciar Usuários
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="card">
                    <h3>Próximas Sessões</h3>
                    <div class="card-content" id="upcoming-sessions">
                        Carregando...
                    </div>
                </div>
                <div class="card">
                    <h3>Metas SMART</h3>
                    <div class="card-content" id="smart-goals">
                        Carregando...
                    </div>
                </div>
                <div class="card">
                    <h3>Feedback Recente</h3>
                    <div class="card-content" id="recent-feedback">
                        Carregando...
                    </div>
                </div>
                <div class="card">
                    <h3>Questionários</h3>
                    <div class="card-content">
                        <a href="questionnaires/disc.php" class="btn-secondary">DISC</a>
                        <a href="questionnaires/mbti.php" class="btn-secondary">MBTI</a>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="https://kit.fontawesome.com/your-code.js" crossorigin="anonymous"></script>
    <script src="assets/js/dashboard.js"></script>
</body>

</html>
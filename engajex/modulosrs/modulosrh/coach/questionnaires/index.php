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
    <title>Questionários - Sistema de Coaching</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/questionnaires.css">
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
                <li><a href="../sessions/index.php"><i class="fas fa-calendar"></i> Sessões</a></li>
                <li><a href="#" class="active"><i class="fas fa-clipboard-list"></i> Questionários</a></li>
                <li><a href="../goals/index.php"><i class="fas fa-bullseye"></i> Metas SMART</a></li>
                <li><a href="../feedback/index.php"><i class="fas fa-comments"></i> Feedback</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
            </ul>
        </nav>
        <main class="content">
            <header class="dashboard-header">
                <h1>Questionários</h1>
                <button class="btn-primary" onclick="openNewQuestionnaireModal()">
                    <i class="fas fa-plus"></i> Novo Questionário
                </button>
            </header>
            
            <div class="questionnaires-grid">
                <!-- DISC Assessment -->
                <div class="card questionnaire-card">
                    <div class="questionnaire-icon disc-icon">D</div>
                    <h3>Perfil DISC</h3>
                    <p>Avalie seu estilo comportamental através das dimensões Dominância, Influência, Estabilidade e Conformidade.</p>
                    <div class="questionnaire-actions">
                        <a href="disc.php" class="btn-primary">Iniciar Avaliação</a>
                        <a href="disc-results.php" class="btn-secondary">Ver Resultados</a>
                    </div>
                </div>
                
                <!-- MBTI Assessment -->
                <div class="card questionnaire-card">
                    <div class="questionnaire-icon mbti-icon">M</div>
                    <h3>Perfil MBTI</h3>
                    <p>Descubra seu tipo de personalidade através do indicador Myers-Briggs Type Indicator.</p>
                    <div class="questionnaire-actions">
                        <a href="mbti.php" class="btn-primary">Iniciar Avaliação</a>
                        <a href="mbti-results.php" class="btn-secondary">Ver Resultados</a>
                    </div>
                </div>
                
                <!-- Custom Assessments -->
                <div class="card questionnaire-card">
                    <div class="questionnaire-icon custom-icon">A</div>
                    <h3>Autoavaliações</h3>
                    <p>Questionários personalizados para avaliar diferentes aspectos do seu desenvolvimento.</p>
                    <div class="questionnaire-actions">
                        <a href="custom.php" class="btn-primary">Ver Questionários</a>
                        <?php if ($_SESSION['user_profile'] === 'coach'): ?>
                        <a href="custom-create.php" class="btn-secondary">Criar Novo</a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Results History -->
                <div class="card questionnaire-card">
                    <div class="questionnaire-icon history-icon">H</div>
                    <h3>Histórico</h3>
                    <p>Visualize o histórico completo de todas as suas avaliações e acompanhe sua evolução.</p>
                    <div class="questionnaire-actions">
                        <a href="history.php" class="btn-primary">Ver Histórico</a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/questionnaires.js"></script>
</body>
</html>

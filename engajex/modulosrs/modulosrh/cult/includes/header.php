<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sinergy Cult | Análise de Cultura Organizacional com IA</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;700&display=swap"
        rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <!-- Chart.js for reports -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-brand">SinergyCult</a>
            <div class="nav-links">
                <?php if ($_SESSION['role'] === 'superadmin'): ?>
                    <a href="admin_companies.php" class="nav-link">Empresas</a>
                    <a href="admin_settings.php" class="nav-link">Configurações</a>
                <?php elseif ($_SESSION['role'] === 'manager'): ?>
                    <a href="manager_users.php" class="nav-link">Usuários</a>
                    <a href="manager_identity.php" class="nav-link">Identidade</a>
                    <a href="manager_surveys.php" class="nav-link">Pesquisas</a>
                    <a href="manager_culture.php" class="nav-link">Relatório Cultural</a>
                <?php elseif ($_SESSION['role'] === 'user'): ?>
                    <a href="user_surveys.php" class="nav-link">Minhas Pesquisas</a>
                <?php endif; ?>

                <a href="logout.php" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.8rem;">Sair</a>
            </div>
        </div>
    </nav>


    <main class="container animate-fade">
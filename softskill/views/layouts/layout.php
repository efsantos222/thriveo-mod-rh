<?php
$role = $_SESSION['role'] ?? 'guest';
$userName = $_SESSION['user_name'] ?? 'Visitante';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TestProf SoftSkill - <?php echo $pageTitle ?? ''; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
</head>

<body>
    <div class="app-container">
        <aside class="sidebar">
            <div class="logo">SoftSkill <span style="color:var(--primary)">AI</span></div>
            <nav>
                <?php if ($role === 'admin'): ?>
                    <a href="<?php echo BASE_URL; ?>admin/dashboard" class="nav-link">Dashboard</a>
                    <a href="<?php echo BASE_URL; ?>admin/recruiters" class="nav-link">Recrutadores</a>
                    <a href="<?php echo BASE_URL; ?>admin/questions" class="nav-link">Questões</a>
                    <a href="<?php echo BASE_URL; ?>admin/settings" class="nav-link">Configurações API</a>
                <?php elseif ($role === 'recruiter'): ?>
                    <a href="<?php echo BASE_URL; ?>recruiter/dashboard" class="nav-link">Dashboard</a>
                    <a href="<?php echo BASE_URL; ?>recruiter/candidates" class="nav-link">Candidatos</a>
                <?php elseif ($role === 'candidate'): ?>
                    <a href="<?php echo BASE_URL; ?>candidate/dashboard" class="nav-link">Meus Testes</a>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>logout" class="nav-link"
                    style="margin-top: auto; color: var(--danger)">Sair</a>
            </nav>
        </aside>
        <main class="main-content">
            <header class="header">
                <h2 class="page-title"><?php echo $pageTitle ?? 'Dashboard'; ?></h2>
                <div style="display:flex; align-items:center; gap:10px">
                    <div
                        style="background:var(--primary); width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold; color:white">
                        <?php echo strtoupper(substr($userName, 0, 1)); ?>
                    </div>
                </div>
            </header>
            <div class="content-body">
                <?php require_once $contentView; ?>
            </div>
        </main>
    </div>
</body>

</html>
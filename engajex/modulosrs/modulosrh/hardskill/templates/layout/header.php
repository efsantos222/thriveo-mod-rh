<?php
$user = current_user();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/style.css">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

    <header>
        <div class="container">
            <nav>
                <a href="<?= APP_URL ?>/" class="logo">TestProf <span
                        style="font-weight: 300; color: var(--text-muted)">Hardskill</span></a>

                <div class="nav-links">
                    <?php if ($user): ?>
                        <?php if ($user['role'] === 'admin'): ?>
                            <a href="<?= APP_URL ?>/admin/dashboard"><i class="fas fa-home"></i> Dashboard</a>
                            <a href="<?= APP_URL ?>/admin/recruiters"><i class="fas fa-users"></i> Recrutadores</a>
                            <a href="<?= APP_URL ?>/admin/settings"><i class="fas fa-cog"></i> Configurações</a>
                        <?php elseif ($user['role'] === 'recruiter'): ?>
                            <a href="<?= APP_URL ?>/recruiter/dashboard"><i class="fas fa-home"></i> Dashboard</a>
                            <a href="<?= APP_URL ?>/recruiter/tests"><i class="fas fa-file-alt"></i> Testes</a>
                            <a href="<?= APP_URL ?>/recruiter/candidates"><i class="fas fa-users"></i> Candidatos</a>
                        <?php elseif ($user['role'] === 'candidate'): ?>
                            <a href="<?= APP_URL ?>/candidate/dashboard"><i class="fas fa-home"></i> Meus Testes</a>
                        <?php endif; ?>

                        <a href="<?= APP_URL ?>/logout" style="color: var(--danger)"><i class="fas fa-sign-out-alt"></i> Sair</a>
                    <?php else: ?>
                        <a href="<?= APP_URL ?>/login">Login</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <main class="container mt-4">
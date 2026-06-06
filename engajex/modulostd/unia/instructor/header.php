<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instrutor | Thriveo Unia</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            padding-top: 80px;
        }

        .instructor-nav {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 1px 3px 0 rgba(0,0,0,0.07);
        }

        .container-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .nav-links a {
            margin-left: 1.5rem;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .nav-links a:hover {
            color: #4f46e5;
        }
    </style>
</head>

<body>

    <nav class="instructor-nav">
        <div class="container-nav">
            <a href="index.php" class="logo" style="font-size: 1.2rem;">Thriveo <span
                    style="font-weight:400; font-size: 1rem;">Instrutor</span></a>
            <div class="nav-links">
                <a href="index.php">Dashboard</a>
                <a href="courses.php">Meus Cursos</a>
                <a href="events.php">Eventos/Aulas</a>
                <a href="ai_create.php" style="color: #a855f7;">✨ Assistente IA</a>
                <a href="../back_to_system.php" style="color: var(--primary-color);">Voltar ao Sistema</a>
                <a href="../logout.php">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container" style="padding-top: 2rem;">
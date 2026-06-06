<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'coordinator') {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coordenador | Thriveo Unia</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            padding-top: 80px;
        }

        .coord-nav {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 1px 3px 0 rgba(0,0,0,0.07);
        }

        .coord-nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-links a {
            margin-left: 1.5rem;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: #4f46e5;
        }

        .table-container {
            overflow-x: auto;
            margin-top: 2rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            color: var(--text-main);
        }

        th,
        td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--glass-border);
        }

        th {
            color: var(--text-muted);
            font-weight: 600;
        }

        tr:hover {
            background: #f8fafc;
        }
    </style>
</head>

<body>

    <nav class="coord-nav">
        <div class="container">
            <a href="index.php" class="logo" style="font-size: 1.2rem;">Thriveo <span
                    style="font-weight:400; font-size: 1rem;">Coordenador</span></a>
            <div class="nav-links">
                <a href="index.php">Dashboard</a>
                <a href="courses.php">Gerenciar Cursos</a>
                <a href="instructors.php">Gerenciar Instrutores</a>
                <a href="../back_to_system.php" style="color: var(--primary-color);">Voltar ao Sistema</a>
                <a href="../logout.php" style="color: var(--secondary-color);">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container" style="padding-top: 2rem;">
<?php
require_once '../config.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Competências - Em Desenvolvimento</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .app-layout {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .main-content {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
            background: var(--bg-color);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .construction-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            padding: 4rem;
            border-radius: 2rem;
            text-align: center;
            max-width: 600px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            animation: float 6s ease-in-out infinite;
        }

        .icon-large {
            font-size: 5rem;
            margin-bottom: 1.5rem;
            display: block;
            filter: drop-shadow(0 0 20px rgba(249, 115, 22, 0.3));
        }

        h1 {
            font-size: 2.5rem;
            color: white;
            margin-bottom: 1rem;
            font-weight: 800;
            background: linear-gradient(135deg, #fff 0%, #cbd5e1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p {
            color: #94a3b8;
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .feature-badge {
            background: rgba(249, 115, 22, 0.1);
            color: #f97316;
            padding: 0.75rem 1.5rem;
            border-radius: 2rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0.5rem;
            border: 1px solid rgba(249, 115, 22, 0.2);
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }

            100% {
                transform: translateY(0px);
            }
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include '../includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="construction-card">
                <span class="icon-large">🚀</span>
                <h1>Em Desenvolvimento</h1>
                <p>Estamos preparando algo incrível para impulsionar a carreira dos seus colaboradores.</p>

                <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 0.5rem;">
                    <span class="feature-badge">📊 Avaliação de Desempenho por Competências</span>
                    <span class="feature-badge">🎯 Plano de Desenvolvimento Individual (PDI)</span>
                </div>
            </div>
        </main>
    </div>
</body>

</html>
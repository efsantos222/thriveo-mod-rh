<?php
require_once 'config.php';
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Ajuda - Matriz de Talentos (9-Box)</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .help-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: var(--card-bg);
            border-radius: 1rem;
            border: 1px solid var(--glass-border);
            color: var(--text-color);
        }

        h1 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }

        h2 {
            color: white;
            margin-top: 2rem;
            border-bottom: 1px solid var(--glass-border);
            padding-bottom: 0.5rem;
        }

        p,
        li {
            line-height: 1.6;
            color: var(--text-muted);
            margin-bottom: 1rem;
        }

        .box-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin: 2rem 0;
        }

        .box-item {
            background: rgba(255, 255, 255, 0.05);
            padding: 1rem;
            border-radius: 0.5rem;
            text-align: center;
            font-size: 0.8rem;
        }
    </style>
</head>

<body>
    <div style="display: flex;">
        <?php include 'includes/sidebar.php'; ?>
        <main style="flex: 1; padding: 2rem; background: var(--bg-color); height: 100vh; overflow-y: auto;">
            <div class="help-container">
                <a href="allocation.php" style="color: var(--primary-color); text-decoration: none;">&larr; Voltar para
                    Matriz</a>

                <h1>Matriz de Talentos (9-Box)</h1>
                <p>A Matriz 9-Box é uma ferramenta estratégica para avaliar e desenvolver talentos, cruzando
                    <strong>Desempenho</strong> (Performance) com <strong>Potencial</strong>.</p>

                <h2>Como Usar</h2>
                <ol>
                    <li>Selecione um colaborador na lista lateral.</li>
                    <li>Arraste o card do colaborador para um dos 9 quadrantes.</li>
                    <li>O sistema salvará automaticamente a nova classificação.</li>
                </ol>

                <h2>Entendendo os Quadrantes</h2>
                <div class="box-grid">
                    <div class="box-item" style="border: 1px solid #eab308;">
                        <strong>Enigma</strong><br>Alto Potencial<br>Baixo Desempenho
                    </div>
                    <div class="box-item" style="border: 1px solid #22c55e;">
                        <strong>Estrela em Ascensão</strong><br>Alto Potencial<br>Médio Desempenho
                    </div>
                    <div class="box-item" style="border: 1px solid #22c55e;">
                        <strong>Talento Futuro</strong><br>Alto Potencial<br>Alto Desempenho
                    </div>

                    <div class="box-item" style="border: 1px solid #eab308;">
                        <strong>Dilema</strong><br>Médio Potencial<br>Baixo Desempenho
                    </div>
                    <div class="box-item" style="border: 1px solid #3b82f6;">
                        <strong>Mantenedor</strong><br>Médio Potencial<br>Médio Desempenho
                    </div>
                    <div class="box-item" style="border: 1px solid #22c55e;">
                        <strong>Forte Desempenho</strong><br>Médio Potencial<br>Alto Desempenho
                    </div>

                    <div class="box-item" style="border: 1px solid #ef4444;">
                        <strong>Risco</strong><br>Baixo Potencial<br>Baixo Desempenho
                    </div>
                    <div class="box-item" style="border: 1px solid #eab308;">
                        <strong>Eficaz</strong><br>Baixo Potencial<br>Médio Desempenho
                    </div>
                    <div class="box-item" style="border: 1px solid #3b82f6;">
                        <strong>Especialista</strong><br>Baixo Potencial<br>Alto Desempenho
                    </div>
                </div>

                <h2>Dicas de Ação</h2>
                <ul>
                    <li><strong>Alto Potencial / Alto Desempenho:</strong> Prepare para promoções e desafios maiores.
                    </li>
                    <li><strong>Baixo Potencial / Baixo Desempenho:</strong> Avalie se há problemas de fit cultural ou
                        necessidade de desligamento.</li>
                    <li><strong>Especialistas:</strong> São cruciais para a operação, mesmo que não queiram cargos de
                        liderança.</li>
                </ul>
            </div>
        </main>
    </div>
</body>

</html>
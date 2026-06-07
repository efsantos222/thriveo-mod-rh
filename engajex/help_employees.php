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
    <title>Ajuda - Gestão de Colaboradores</title>
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

        .badge {
            background: var(--primary-color);
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.8rem;
        }
    </style>
</head>

<body>
    <div style="display: flex;">
        <?php include 'includes/sidebar.php'; ?>
        <main style="flex: 1; padding: 2rem; background: var(--bg-color); height: 100vh; overflow-y: auto;">
            <div class="help-container">
                <a href="manage_employees.php" style="color: var(--primary-color); text-decoration: none;">&larr; Voltar
                    para Gestão</a>

                <h1>Ajuda da Gestão de Colaboradores</h1>
                <p>Nesta área, você pode gerenciar todo o cadastro da equipe e definir a estrutura organizacional.</p>

                <h2>1. Cadastrar Novo Colaborador</h2>
                <p>Clique no botão <strong>+ Novo Colaborador</strong> para abrir o formulário. Preencha os dados
                    obrigatórios:</p>
                <ul>
                    <li><strong>Nome:</strong> Nome completo do funcionário.</li>
                    <li><strong>Email:</strong> Será usado para login.</li>
                    <li><strong>Cargo:</strong> Função atual na empresa.</li>
                    <li><strong>Nascimento:</strong> Importante para o módulo de aniversariantes.</li>
                </ul>

                <h2>2. Papéis e Permissões</h2>
                <p>O campo <strong>Papel (Role)</strong> define o nível de acesso:</p>
                <ul>
                    <li><span class="badge">Funcionario</span> Acesso padrão (responder pesquisas, ver mural, etc).</li>
                    <li><span class="badge">Gestor</span> Pode ver relatórios e gerenciar seu time direto.</li>
                    <li><span class="badge">Responsavel</span> Acesso total (Admin) para configurar a empresa.</li>
                </ul>

                <h2>3. Gestor / Padrinho</h2>
                <p>Ao selecionar um <strong>Gestor</strong> para o colaborador, você cria a hierarquia da empresa. Isso
                    permite:</p>
                <ul>
                    <li>Que o gestor acompanhe o humor do time (<a href="modules/mood.php">Mood Tracker</a>).</li>
                    <li>Que o sistema gere organogramas automáticos (futuro).</li>
                </ul>

                <h2>4. Moedas (EngajaCoins)</h2>
                <p>Você pode premiar ou ajustar o saldo de moedas de um colaborador manualmente clicando em
                    <strong>Editar</strong>. Essas moedas são usadas na Loja de Recompensas.</p>
            </div>
        </main>
    </div>
</body>

</html>
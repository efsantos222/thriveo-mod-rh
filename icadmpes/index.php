<?php
// index.php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thriveo - Inteligência Competitiva</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <header>
        <div class="container navbar">
            <div class="brand">Thriveo Intelligence</div>
            <div class="nav-links">
                <?php if (isset($_SESSION['user_id']) && isset($_SESSION['role'])): ?>
                    <a
                        href="<?php echo $_SESSION['role'] === 'admin' ? 'admin/index.php' : 'app/index.php'; ?>">Dashboard</a>
                    <a href="logout.php">Sair</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary">Entrar</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="container">
        <section class="hero">
            <h1>Inteligência Competitiva de Mercado</h1>
            <p>Monitore legislações, decisões judiciais e dissídios sindicais com o poder da Inteligência Artificial.
            </p>

            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="login.php" class="btn btn-primary">Acessar Sistema</a>
            <?php endif; ?>
        </section>

        <section class="lgpd-banner">
            <strong>Privacidade e LGPD:</strong> Este sistema está em total conformidade com a Lei Geral de Proteção de
            Dados (LGPD). Seus dados são armazenados de forma segura e utilizados apenas para os fins contratados.
        </section>

        <section class="feature-grid">
            <div class="feature-card">
                <h3>Monitoramento Legal</h3>
                <p>Acompanhe novas leis e normas regulamentadoras (NRs) em tempo real, filtradas pelo setor da sua
                    empresa.</p>
            </div>
            <div class="feature-card">
                <h3>Jurisprudência</h3>
                <p>Pesquise decisões judiciais relevantes para antecipar riscos e ajustar estratégias.</p>
            </div>
            <div class="feature-card">
                <h3>Dissídios Sindicais</h3>
                <p>Analise acordos e convenções coletivas de trabalho indicando o sindicato específico.</p>
            </div>
        </section>

        <section
            style="margin-top: 4rem; text-align: center; background: #fff; padding: 2rem; border-radius: 1rem; border: 1px solid #e2e8f0;">
            <h2>Solicitar Acesso</h2>
            <p style="margin: 1rem 0;">O acesso é restrito a empresas parceiras. Para solicitar o credenciamento, envie
                um e-mail corporativo.</p>
            <div
                style="background: #f8fafc; display: inline-block; padding: 1.5rem; border-radius: 0.5rem; text-align: left;">
                <p><strong>Para:</strong> efsantos@proftest.com.br</p>
                <p><strong>Assunto:</strong> Solicitação de Acesso - Thriveo</p>
                <p><strong>Corpo do E-mail:</strong></p>
                <ul style="margin-left: 1.5rem; margin-top: 0.5rem;">
                    <li>Nome Completo</li>
                    <li>E-mail Corporativo (e-mails pessoais não serão aceitos)</li>
                    <li>Nome da Empresa</li>
                </ul>
            </div>
        </section>
    </main>

    <footer style="text-align: center; padding: 2rem 0; color: #64748b; font-size: 0.9rem;">
        &copy;
        <?php echo date('Y'); ?> Thriveo. Todos os direitos reservados.
    </footer>
</body>

</html>
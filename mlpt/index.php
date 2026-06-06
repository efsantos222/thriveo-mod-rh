<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MLPT - Sistema de Certificação de Cultura Organizacional</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <nav class="navbar">
        <div class="container">
            <div class="logo">MLPT System</div>
            <div class="nav-links">
                <a href="#funcionalidades">Funcionalidades</a>
                <a href="#acesso">Solicitar Acesso</a>
                <a href="login.php" class="btn btn-primary">Login</a>
            </div>
        </div>
    </nav>

    <header class="hero">
        <div class="container hero-content">
            <span class="badge">Potencializado por IA</span>
            <h1>Sua jornada rumo ao <span class="gradient-text">Melhor Lugar Para Trabalhar</span> começa aqui.</h1>
            <p>Um sistema completo para análise, diagnóstico e aprimoramento da cultura organizacional, focado nos
                pilares de Credibilidade, Respeito, Imparcialidade, Orgulho e Camaradagem.</p>
            <div class="hero-btns">
                <a href="#funcionalidades" class="btn btn-secondary">Conheça a Plataforma</a>
            </div>
        </div>
    </header>

    <section id="funcionalidades" class="features">
        <div class="container">
            <div class="section-header">
                <h2>Tecnologia avançada para RH</h2>
                <p>Ferramentas estratégicas para elevar o nível da sua organização.</p>
            </div>
            <div class="feature-grid">
                <div class="feature-card">
                    <div class="icon"><i class="fa-solid fa-chart-pie"></i></div>
                    <h3>Dashboard de Maturidade</h3>
                    <p>Avalie o nível de preparação da sua empresa nos 5 pilares essenciais: Credibilidade, Respeito,
                        Imparcialidade, Orgulho e Camaradagem.</p>
                </div>
                <div class="feature-card">
                    <div class="icon"><i class="fa-solid fa-robot"></i></div>
                    <h3>Análise Documental com IA</h3>
                    <p>Faça upload de políticas e códigos de conduta. Nossa IA identifica automaticamente gaps em
                        relação às exigências de certificação.</p>
                </div>
                <div class="feature-card">
                    <div class="icon"><i class="fa-solid fa-flask"></i></div>
                    <h3>Simulador de Trust Index</h3>
                    <p>Tenha uma prévia da pesquisa oficial com análise preditiva de resultados e identifique áreas de
                        risco.</p>
                </div>
                <div class="feature-card">
                    <div class="icon"><i class="fa-solid fa-list-check"></i></div>
                    <h3>Gerador de Planos de Ação</h3>
                    <p>Receba um roadmap priorizado e personalizado baseado nos gaps identificados pela inteligência
                        artificial.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="lgpd-section">
        <div class="container">
            <div class="lgpd-content">
                <div class="lgpd-icon"><i class="fa-solid fa-shield-halved"></i></div>
                <div class="lgpd-text">
                    <h2>Compromisso com a Privacidade (LGPD)</h2>
                    <p>A segurança dos seus dados é nossa prioridade. O sistema MLPT foi desenvolvido em estrita
                        conformidade com a Lei Geral de Proteção de Dados (LGPD). Todos os dados processados e
                        documentos analisados são criptografados e utilizados exclusivamente para fins de geração de
                        relatórios e insights de cultura organizacional.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="acesso" class="access-section">
        <div class="container">
            <div class="access-card">
                <h2>Solicitação de Acesso</h2>
                <p>O Sitema MLPT é uma plataforma exclusiva para empresas convidadas e parceiros. Para garantir a
                    segurança e a integridade da comunidade, não permitimos auto-cadastro.</p>

                <div class="access-instructions">
                    <p>Para obter suas credenciais, envie um e-mail para:</p>
                    <a href="mailto:efsantos@proftest.com.br" class="email-link">efsantos@proftest.com.br</a>

                    <div class="requirements">
                        <h4>Informações Obrigatórias:</h4>
                        <ul>
                            <li><i class="fa-solid fa-check"></i> Nome Completo</li>
                            <li><i class="fa-solid fa-check"></i> E-mail Corporativo (e-mails pessoais não serão
                                aceitos)</li>
                            <li><i class="fa-solid fa-check"></i> Nome da Empresa</li>
                            <li><i class="fa-solid fa-check"></i> Cargo / Função</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> MLPT System. Todos os direitos reservados.</p>
            <p class="small">Sistema de Apoio à Gestão de Cultura e Clima Organizacional.</p>
        </div>
    </footer>
</body>

</html>
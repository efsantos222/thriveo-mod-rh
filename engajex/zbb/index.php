<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thriveo ZBB - Inteligência Competitiva e Gestão Orçamentária</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
</head>

<body>

    <header class="header">
        <div class="container nav">
            <div class="logo">Thriveo ZBB</div>
            <ul class="nav-links">
                <li><a href="#funcionalidades" class="nav-link">Funcionalidades</a></li>
                <li><a href="#privacidade" class="nav-link">LGPD & Privacidade</a></li>
                <li><a href="login.php" class="btn btn-primary">Entrar no Sistema</a></li>
            </ul>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container hero-content">
                <div class="lgpd-badge">
                    <span style="margin-right: 0.5rem">🔒</span> 100% Em conformidade com a LGPD
                </div>
                <h1 class="hero-title">Inteligência Competitiva & <br>Orçamento Base Zero (ZBB)</h1>
                <p class="hero-subtitle">
                    Transforme a gestão da sua empresa com análises preditivas, controle orçamentário rigoroso e tomadas
                    de decisão baseadas em dados.
                </p>
                <div style="margin-top: 2rem;">
                    <a href="#contato" class="btn btn-primary">Solicitar Acesso</a>
                    <a href="#funcionalidades" class="btn btn-secondary" style="margin-left: 1rem">Saiba Mais</a>
                </div>
            </div>
        </section>

        <section id="funcionalidades" class="section">
            <div class="container">
                <h2 class="text-center mb-4">Funcionalidades do Sistema</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">

                    <div class="card">
                        <h3>📊 Gestão Orçamentária ZBB</h3>
                        <p>Controle total com a metodologia Zero-Based Budgeting. Justifique cada despesa do zero,
                            elimine desperdícios e otimize recursos com fluxos de aprovação multinível.</p>
                    </div>

                    <div class="card">
                        <h3>🤖 Inteligência Competitiva</h3>
                        <p>Integração com IA (GPT-4o) para análise de cenários, detecção de anomalias e insights
                            estratégicos de mercado.</p>
                    </div>

                    <div class="card">
                        <h3>📈 Dashboard & Analytics</h3>
                        <p>Visualizações interativas, comparativos Mês a Mês, análises verticais e horizontais e
                            identificação automática de outliers.</p>
                    </div>

                    <div class="card">
                        <h3>📑 Relatórios Executivos</h3>
                        <p>Geração automática de relatórios em Excel e PDF, com projeções baseadas em histórico e
                            simulações de cenários (What-If).</p>
                    </div>

                </div>
            </div>
        </section>

        <section id="privacidade" class="section" style="background-color: var(--surface-color);">
            <div class="container">
                <h2 class="text-center mb-4">Segurança e Privacidade (LGPD)</h2>
                <div style="max-width: 800px; margin: 0 auto; text-align: center;">
                    <p>
                        O sistema Thriveo ZBB foi desenvolvido seguindo rigorosamente as diretrizes da Lei Geral de
                        Proteção de Dados (LGPD).
                        Garantimos a confidencialidade, integridade e disponibilidade dos seus dados.
                    </p>
                    <p>
                        Todos os acessos são monitorados, e dados sensíveis são criptografados. Você tem total controle
                        sobre suas informações.
                    </p>
                </div>
            </div>
        </section>

        <section id="contato" class="section">
            <div class="container text-center">
                <h2 class="mb-4">Solicite seu Acesso</h2>
                <div class="card" style="max-width: 600px; margin: 0 auto;">
                    <p style="font-size: 1.1rem; margin-bottom: 1.5rem;">
                        O acesso ao sistema é restrito a empresas parceiras e colaboradores autorizados.
                    </p>
                    <p>
                        Para solicitar o cadastro da sua empresa e usuário, encaminhe um e-mail corporativo para:
                    </p>
                    <a href="mailto:efsantos@proftest.com.br"
                        style="font-size: 1.5rem; font-weight: 700; color: var(--primary-color);">
                        efsantos@proftest.com.br
                    </a>
                    <p class="mt-4" style="font-size: 0.9rem; color: var(--text-secondary);">
                        <strong>Importante:</strong> No e-mail, informe seu <u>Nome Completo</u>, <u>Empresa</u> e
                        <u>Cargo</u>.
                        <br>
                        <span style="color: var(--danger-color);">Não aceitamos solicitações de e-mails pessoais (Gmail,
                            Hotmail, etc).</span>
                    </p>
                </div>
            </div>
        </section>

    </main>

    <footer
        style="background-color: var(--accent-color); color: white; padding: 2rem 0; text-align: center; margin-top: 4rem;">
        <div class="container">
            <p>&copy; 2026 Thriveo ZBB. Todos os direitos reservados.</p>
            <p style="font-size: 0.8rem; opacity: 0.7;">Sistema desenvolvido com PHP 8, MySQL e IA.</p>
        </div>
    </footer>

</body>

</html>
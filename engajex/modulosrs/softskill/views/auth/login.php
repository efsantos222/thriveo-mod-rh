<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TestProf SoftSkill - Login Corporativo</title>
    <!-- Added time() to force cache clearing -->
    <link rel="stylesheet"
        href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>public/css/style.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Force reset body background to avoid dark theme flash/persistence */
        body {
            background: #ffffff !important;
            color: #333 !important;
            display: block !important;
            /* Override flex from dashboard layout */
        }
    </style>
</head>

<body>

    <!-- 1. HERO SECTION (Blue) containing Login -->
    <section class="hero-section">
        <div class="container hero-container">
            <!-- Left: Text -->
            <div class="hero-text">
                <h1>Avaliação de <br> SoftSkills</h1>
                <p>Avalie competências comportamentais com precisão através de testes especializados. Total conformidade
                    com a LGPD.</p>
                <div class="hero-buttons">
                    <a href="#features" class="btn btn-outline-white">Saiba Mais</a>
                    <a href="#access" class="btn btn-outline-white">Solicitar Acesso</a>
                </div>
            </div>

            <!-- Right: Login Card -->
            <div class="hero-login-card">
                <h3>Acesso Rápido</h3>
                <p class="login-subtitle">Credenciais de Acesso</p>

                <?php if (isset($error)): ?>
                    <div class="alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <form action="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>login" method="POST">
                    <!-- Adjusted for the reference image's visual style -->
                    <div class="form-group-minimal">
                        <span class="input-icon-minimal">🔑</span>
                        <input type="email" name="email" class="form-control-minimal" placeholder="Seu e-mail aqui"
                            required>
                    </div>

                    <!-- Hidden password field -->
                    <div class="form-group-minimal" style="margin-top: 10px;">
                        <span class="input-icon-minimal">🔒</span>
                        <input type="password" name="password" class="form-control-minimal" placeholder="Sua senha"
                            required>
                    </div>

                    <button type="submit" class="btn btn-primary-full">
                        <span class="btn-icon">➜</span> Acessar
                    </button>

                    <div class="login-footer-note">
                        🛡️ Sistemas Proftest
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- 2. FEATURES SECTION (White) -->
    <section id="features" class="features-section">
        <div class="container">
            <div class="section-header">
                <h2>Funcionalidades do Sistema</h2>
                <p>Tudo o que você precisa para avaliar SoftSkills da sua equipe ou candidatos</p>
            </div>

            <div class="features-grid">
                <div class="feature-item">
                    <div class="icon-circle icon-blue">📊</div>
                    <h3>Relatórios em Tempo Real</h3>
                    <p>Acompanhe os resultados com análises dos perfis de cada candidato ou colaborador através de
                        dashboards
                        intuitivos.</p>
                </div>
                <div class="feature-item">
                    <div class="icon-circle icon-blue">🛡️</div>
                    <h3>Anonimato Garantido</h3>
                    <p>As respostas são processadas de forma a garantir o sigilo, incentivando a honestidade dos
                        candidatos e
                        colaboradores.</p>
                </div>
                <div class="feature-item">
                    <div class="icon-circle icon-blue">👥</div>
                    <h3>Gestão Multi-Empresa</h3>
                    <p>Plataforma robusta capaz de gerenciar múltiplas empresas e departamentos com isolamento total de
                        dados.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. LGPD SECTION (Light Gray) -->
    <section class="lgpd-section">
        <div class="container">
            <div class="lgpd-box">
                <div class="lgpd-icon">🔒</div>
                <h2>Compromisso com a LGPD</h2>
                <p>Nossa plataforma foi desenvolvida seguindo rigorosamente as diretrizes da Lei Geral de Proteção de
                    Dados (Lei nº 13.709/2018).</p>
                <div class="lgpd-checks">
                    <div class="check-item">✅ Coleta mínima de dados pessoais.</div>
                    <div class="check-item">✅ Armazenamento seguro e criptografado.</div>
                    <div class="check-item">✅ Acesso restrito e auditável aos dados.</div>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. FOOTER/ACCESS SECTION (Dark) -->
    <section id="access" class="footer-section">
        <div class="container">
            <h2 class="footer-title">Solicite seu Acesso</h2>
            <p class="footer-subtitle">Para implementar o Sistema de Avaliação de SoftSkills na sua empresa, entre em
                contato
                conosco.</p>

            <div class="contact-card">
                <p class="contact-header">Envie um e-mail para <a
                        href="mailto:efsantos@proftest.com.br">efsantos@proftest.com.br</a></p>

                <div class="info-box">
                    <strong>ℹ️ No e-mail, informe:</strong>
                    <ul class="info-list">
                        <li>• Nome completo do responsável</li>
                        <li>• E-mail corporativo</li>
                        <li>• Nome da Empresa</li>
                    </ul>
                </div>

                <div class="warning-text">
                    ⚠️ <strong>Atenção:</strong> Não aceitamos solicitações de domínios de e-mail pessoais (Gmail,
                    Hotmail, etc). Apenas e-mails corporativos.
                </div>
            </div>
        </div>
    </section>

</body>

</html>
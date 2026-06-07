<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Engajex — Plataforma de Engajamento Corporativo</title>
    <meta name="description" content="Plataforma completa para engajar, desenvolver e conectar profissionais. Gestão de desempenho, feedback contínuo e people analytics em um só lugar.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/landing.css">
</head>
<body class="page-landing">

<!-- ════════════════════════════════════════════════════════
     NAVBAR
     ════════════════════════════════════════════════════════ -->
<nav class="landing-nav" id="landingNav">
    <a href="index.php" class="landing-nav__logo">
        <img src="http://thriveo.com.br/wp-content/uploads/2026/03/logo_thriveo_branco_transparente.png" alt="Engajex">
    </a>

    <ul class="landing-nav__links">
        <li><a href="#features">Funcionalidades</a></li>
        <li><a href="#lgpd">Privacidade</a></li>
        <li><a href="#acesso">Acesso</a></li>
    </ul>

    <div class="landing-nav__actions">
        <a href="register.php" class="landing-nav__btn-outline">Cadastrar Empresa</a>
        <a href="login.php"    class="landing-nav__btn-primary">
            <i class="fa-solid fa-right-to-bracket"></i>
            Entrar
        </a>
    </div>

    <button class="landing-nav__hamburger" onclick="toggleMobileMenu()" aria-label="Menu">
        <span></span><span></span><span></span>
    </button>
</nav>


<!-- ════════════════════════════════════════════════════════
     HERO
     ════════════════════════════════════════════════════════ -->
<section class="landing-hero">
    <div class="landing-hero__bg"></div>
    <div class="landing-hero__grid"></div>

    <div class="landing-hero__content">
        <div class="landing-hero__badge">
            <i class="fa-solid fa-bolt"></i>
            Plataforma Completa de RH para o Brasil
        </div>

        <h1 class="landing-hero__title">
            Transforme a<br>
            <span class="highlight">Cultura da Sua Empresa</span>
        </h1>

        <p class="landing-hero__subtitle">
            Uma plataforma integrada para engajar colaboradores, acelerar o desenvolvimento
            e tomar decisões estratégicas com People Analytics em tempo real.
        </p>

        <div class="landing-hero__ctas">
            <a href="#acesso" class="hero-btn-primary">
                Solicitar Acesso
                <i class="fa-solid fa-arrow-right"></i>
            </a>
            <a href="#features" class="hero-btn-secondary">
                <i class="fa-solid fa-circle-play"></i>
                Ver Funcionalidades
            </a>
        </div>
    </div>

    <div class="landing-hero__scroll">
        <span>Explorar</span>
        <i class="fa-solid fa-chevron-down"></i>
    </div>
</section>


<!-- ════════════════════════════════════════════════════════
     STATS
     ════════════════════════════════════════════════════════ -->
<div class="landing-stats">
    <div class="landing-stats__grid">
        <div class="landing-stats__item reveal">
            <span class="landing-stats__value"><span>12</span>+</span>
            <span class="landing-stats__label">Módulos integrados</span>
        </div>
        <div class="landing-stats__item reveal reveal-delay-1">
            <span class="landing-stats__value"><span>100</span>%</span>
            <span class="landing-stats__label">LGPD Compliant</span>
        </div>
        <div class="landing-stats__item reveal reveal-delay-2">
            <span class="landing-stats__value"><span>IA</span></span>
            <span class="landing-stats__label">Integrada nativamente</span>
        </div>
        <div class="landing-stats__item reveal reveal-delay-3">
            <span class="landing-stats__value"><span>1</span> lugar</span>
            <span class="landing-stats__label">Para gerir pessoas</span>
        </div>
    </div>
</div>


<!-- ════════════════════════════════════════════════════════
     FEATURES
     ════════════════════════════════════════════════════════ -->
<section class="landing-section" id="features">
    <div class="landing-section__header reveal">
        <p class="section-eyebrow">Funcionalidades</p>
        <h2>Tudo que seu RH precisa<br>em um único lugar</h2>
        <p>Módulos integrados que cobrem desde o recrutamento até o desenvolvimento contínuo dos colaboradores.</p>
    </div>

    <div class="features-grid">

        <div class="card-feature reveal reveal-delay-1">
            <div class="card-feature__icon">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <h3 class="card-feature__title">Gestão de Desempenho</h3>
            <p class="card-feature__desc">Avaliação por competências, PDI e acompanhamento de metas para impulsionar o crescimento do time.</p>
            <span class="card-feature__link">Saiba mais <i class="fa-solid fa-arrow-right"></i></span>
        </div>

        <div class="card-feature reveal reveal-delay-2">
            <div class="card-feature__icon card-feature__icon--success" style="background:rgba(16,185,129,.12);color:#10b981">
                <i class="fa-solid fa-comments"></i>
            </div>
            <h3 class="card-feature__title">Feedback Contínuo</h3>
            <p class="card-feature__desc">Canais abertos para 1:1, ouvidoria, feedback 360° e celebrações de conquistas em equipe.</p>
            <span class="card-feature__link">Saiba mais <i class="fa-solid fa-arrow-right"></i></span>
        </div>

        <div class="card-feature reveal reveal-delay-3">
            <div class="card-feature__icon" style="background:rgba(245,158,11,.12);color:#f59e0b">
                <i class="fa-solid fa-gamepad"></i>
            </div>
            <h3 class="card-feature__title">Gamificação</h3>
            <p class="card-feature__desc">Dinâmicas de grupo, bingo corporativo, conexões virtuais e pontuação para motivar e integrar equipes.</p>
            <span class="card-feature__link">Saiba mais <i class="fa-solid fa-arrow-right"></i></span>
        </div>

        <div class="card-feature reveal reveal-delay-1">
            <div class="card-feature__icon" style="background:rgba(139,92,246,.12);color:#8b5cf6">
                <i class="fa-solid fa-chart-bar"></i>
            </div>
            <h3 class="card-feature__title">People Analytics</h3>
            <p class="card-feature__desc">Turnover, humor da equipe, métricas de clima e aderência cultural com dashboards em tempo real.</p>
            <span class="card-feature__link">Saiba mais <i class="fa-solid fa-arrow-right"></i></span>
        </div>

        <div class="card-feature reveal reveal-delay-2">
            <div class="card-feature__icon" style="background:rgba(6,182,212,.12);color:#06b6d4">
                <i class="fa-solid fa-brain"></i>
            </div>
            <h3 class="card-feature__title">Recrutamento com IA</h3>
            <p class="card-feature__desc">Match de CVs, avaliação de soft skills, roteiro de entrevista gerado por IA e testes comportamentais.</p>
            <span class="card-feature__link">Saiba mais <i class="fa-solid fa-arrow-right"></i></span>
        </div>

        <div class="card-feature reveal reveal-delay-3">
            <div class="card-feature__icon" style="background:rgba(244,63,94,.12);color:#f43f5e">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <h3 class="card-feature__title">T&D — Thriveo UniA</h3>
            <p class="card-feature__desc">Plataforma de treinamento e desenvolvimento com trilhas personalizadas, Coaching e Assessments.</p>
            <span class="card-feature__link">Saiba mais <i class="fa-solid fa-arrow-right"></i></span>
        </div>

    </div>
</section>


<!-- ════════════════════════════════════════════════════════
     LGPD
     ════════════════════════════════════════════════════════ -->
<section class="landing-lgpd reveal" id="lgpd">
    <div class="landing-lgpd__inner">
        <div class="landing-lgpd__icon-wrap">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div class="landing-lgpd__content">
            <h3>Compromisso com a Privacidade e Conformidade</h3>
            <p>
                O Engajex está em total conformidade com a Lei Geral de Proteção de Dados (LGPD).
                Todos os dados são tratados com máxima transparência, criptografados em repouso e em
                trânsito, e nunca compartilhados com terceiros sem consentimento explícito.
            </p>
            <div class="landing-lgpd__badges">
                <span class="lgpd-tag"><i class="fa-solid fa-check"></i> LGPD Compliant</span>
                <span class="lgpd-tag"><i class="fa-solid fa-check"></i> Dados Criptografados</span>
                <span class="lgpd-tag"><i class="fa-solid fa-check"></i> Hospedagem no Brasil</span>
                <span class="lgpd-tag"><i class="fa-solid fa-check"></i> Sem Venda de Dados</span>
            </div>
        </div>
    </div>
</section>


<!-- ════════════════════════════════════════════════════════
     ACESSO / CTA
     ════════════════════════════════════════════════════════ -->
<section class="landing-access reveal" id="acesso">
    <div class="landing-access__card">
        <h2>Solicite seu Acesso</h2>
        <p>
            O acesso é exclusivo para empresas cadastradas. Envie um e-mail com seus dados
            e nossa equipe entrará em contato em até 24 horas.
        </p>

        <a href="mailto:efsantos@thriveo.com.br" class="access-email">
            <i class="fa-solid fa-envelope"></i>
            efsantos@thriveo.com.br
        </a>

        <div class="access-requirements">
            <h4>Dados necessários no e-mail</h4>
            <ul>
                <li><i class="fa-solid fa-check"></i> Nome completo</li>
                <li><i class="fa-solid fa-check"></i> E-mail corporativo (não são aceitos e-mails pessoais)</li>
                <li><i class="fa-solid fa-check"></i> Nome da empresa</li>
                <li><i class="fa-solid fa-check"></i> Cargo / função</li>
            </ul>
        </div>
    </div>
</section>


<!-- ════════════════════════════════════════════════════════
     FOOTER
     ════════════════════════════════════════════════════════ -->
<footer class="landing-footer">
    <span class="landing-footer__copy">
        &copy; <?php echo date('Y'); ?> Engajex · Thriveo. Todos os direitos reservados.
    </span>
    <nav class="landing-footer__links">
        <a href="login.php">Entrar</a>
        <a href="register.php">Cadastrar</a>
        <a href="#lgpd">Privacidade</a>
    </nav>
</footer>


<!-- ════════════════════════════════════════════════════════
     SCRIPTS
     ════════════════════════════════════════════════════════ -->
<script>
(function () {
    // ── Navbar scroll effect ──────────────────────────────
    var nav = document.getElementById('landingNav');
    window.addEventListener('scroll', function () {
        nav.classList.toggle('scrolled', window.scrollY > 40);
    }, { passive: true });

    // ── Reveal on scroll (Intersection Observer) ─────────
    var reveals = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });

        reveals.forEach(function (el) { observer.observe(el); });
    } else {
        // Fallback para browsers antigos
        reveals.forEach(function (el) { el.classList.add('visible'); });
    }

    // ── Mobile menu ───────────────────────────────────────
    window.toggleMobileMenu = function () {
        // Implementação básica — pode ser expandida
        var links = document.querySelector('.landing-nav__links');
        if (links) {
            links.style.display = links.style.display === 'flex' ? 'none' : 'flex';
        }
    };

    // ── Smooth scroll para âncoras ────────────────────────
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            var target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
})();
</script>

</body>
</html>

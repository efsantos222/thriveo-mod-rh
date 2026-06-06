<?php
// index.php - Landing Page Brilhamente
// Developed with strict adherence to single-file requirement.
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brilhamente | IA para Estudantes e Profissionais</title>
    <meta name="description"
        content="Plataforma de inteligência artificial para potencializar estudantes e profissionais. Avaliações, currículos, simulados e muito mais.">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Plus+Jakarta+Sans:wght@400;500;700&display=swap"
        rel="stylesheet">

    <style>
        :root {
            /* Palette - Deep Space & Neon AI */
            --bg-color: #030712;
            --bg-secondary: #0f172a;
            --primary: #6366f1;
            /* Indigo */
            --primary-glow: rgba(99, 102, 241, 0.4);
            --accent: #d946ef;
            /* Fuchsia */
            --accent-glow: rgba(217, 70, 239, 0.4);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --card-bg: rgba(30, 41, 59, 0.5);
            --glass-border: rgba(255, 255, 255, 0.1);
            --gradient-main: linear-gradient(135deg, #6366f1 0%, #d946ef 100%);

            --transition-speed: 0.3s;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            overflow-x: hidden;
            line-height: 1.6;
        }

        h1,
        h2,
        h3,
        h4,
        h5 {
            font-family: 'Outfit', sans-serif;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* --- Background Animation --- */
        .bg-orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            z-index: -1;
            opacity: 0.4;
            animation: orb-float 10s infinite alternate ease-in-out;
        }

        .orb-1 {
            width: 400px;
            height: 400px;
            background: var(--primary);
            top: -100px;
            left: -100px;
        }

        .orb-2 {
            width: 500px;
            height: 500px;
            background: var(--accent);
            bottom: -150px;
            right: -150px;
            animation-delay: -5s;
        }

        .orb-3 {
            width: 300px;
            height: 300px;
            background: #06b6d4;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.2;
            animation-duration: 15s;
        }

        @keyframes orb-float {
            0% {
                transform: translate(0, 0) scale(1);
            }

            100% {
                transform: translate(30px, 50px) scale(1.1);
            }
        }

        /* --- Layout & Typography --- */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            position: relative;
            z-index: 1;
        }

        .section-padding {
            padding: 6rem 0;
        }

        .text-gradient {
            background: var(--gradient-main);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* --- Header --- */
        header {
            padding: 1.5rem 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 100;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            transition: all var(--transition-speed);
        }

        header.scrolled {
            background: rgba(3, 7, 18, 0.8);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }

        .nav-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo svg {
            width: 32px;
            height: 32px;
            fill: url(#logoGradient);
        }

        /* --- Hero Section --- */
        .hero {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
            padding-top: 5rem;
        }

        .hero h1 {
            font-size: 4rem;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .hero p {
            font-size: 1.25rem;
            color: var(--text-muted);
            max-width: 700px;
            margin-bottom: 3rem;
        }

        .cta-button {
            display: inline-flex;
            align-items: center;
            padding: 1rem 2.5rem;
            background: var(--gradient-main);
            color: white;
            font-weight: 600;
            border-radius: 50px;
            font-size: 1.1rem;
            transition: transform var(--transition-speed), box-shadow var(--transition-speed);
            box-shadow: 0 0 20px var(--primary-glow);
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 40px var(--accent-glow);
        }

        /* --- Features Grid --- */
        .features {
            background: rgba(15, 23, 42, 0.3);
            border-top: 1px solid var(--glass-border);
        }

        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-header h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2rem;
            transition: all var(--transition-speed);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 100%;
            backdrop-filter: blur(5px);
        }

        .card:hover {
            transform: translateY(-5px);
            border-color: rgba(99, 102, 241, 0.4);
            background: rgba(30, 41, 59, 0.7);
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--gradient-main);
            opacity: 0;
            transition: opacity var(--transition-speed);
        }

        .card:hover::before {
            opacity: 1;
        }

        .card-icon {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }

        .card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--text-main);
        }

        .card p {
            color: var(--text-muted);
            margin-bottom: 1.5rem;
            flex-grow: 1;
        }

        .card-link {
            color: var(--primary);
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: gap var(--transition-speed);
        }

        .card-link:hover {
            gap: 0.8rem;
            text-shadow: 0 0 10px var(--primary-glow);
        }

        /* --- Footer --- */
        footer {
            border-top: 1px solid var(--glass-border);
            padding: 4rem 0;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        footer p {
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
        }
    </style>
</head>

<body>

    <!-- Background Elements -->
    <div class="bg-orb orb-1"></div>
    <div class="bg-orb orb-2"></div>
    <div class="bg-orb orb-3"></div>

    <svg width="0" height="0" style="position: absolute;">
        <defs>
            <linearGradient id="logoGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:#6366f1;stop-opacity:1" />
                <stop offset="100%" style="stop-color:#d946ef;stop-opacity:1" />
            </linearGradient>
        </defs>
    </svg>

    <!-- Header -->
    <header id="header">
        <div class="container nav-content">
            <a href="#" class="logo">
                <svg viewBox="0 0 24 24">
                    <path
                        d="M12 2L2 7l10 5 10-5-10-5zm0 9l2.5-1.25L12 8.5l-2.5 1.25L12 11zm0 2.5l-5-2.5-5 2.5L12 22l10-8.5-5-2.5-5 2.5z" />
                </svg>
                <span class="text-gradient">Brilhamente</span>
            </a>
            <!-- Could add nav links here if needed -->
        </div>
    </header>

    <!-- Hero -->
    <section class="hero container">
        <h1>Inteligência Artificial <br><span class="text-gradient">Potencializando Seu Futuro</span></h1>
        <p>Acelere sua carreira e estudos com nossas ferramentas avançadas de IA.
            Desde avaliações acadêmicas até simulações de entrevistas, temos a tecnologia que você precisa para brilhar.
        </p>
        <a href="#ferramentas" class="cta-button">Explorar Ferramentas</a>
    </section>

    <!-- Services Grid -->
    <section id="ferramentas" class="features section-padding">
        <div class="container">
            <div class="section-header">
                <h2>Tecnologias Disponíveis</h2>
                <p style="color: var(--text-muted); max-width: 600px; margin: 0 auto;">Nossa suíte de aplicativos foi
                    desenhada para cobrir cada etapa da sua jornada de desenvolvimento.</p>
            </div>

            <div class="grid">
                <!-- 1. Avaliação de Desempenho -->
                <div class="card">
                    <div class="card-icon">📊</div>
                    <h3>Desempenho Acadêmico</h3>
                    <p>Avalie seu progresso com métricas precisas e insights gerados por IA para melhorar suas notas.
                    </p>
                    <a href="https://brilhamente.thriveo.com.br/avd" class="card-link">
                        Acessar Sistema <span>→</span>
                    </a>
                </div>

                <!-- 2. Certificações -->
                <div class="card">
                    <div class="card-icon">🏆</div>
                    <h3>Exames de Certificação</h3>
                    <p>Simuladores realistas para as certificações profissionais mais exigidas do mercado.</p>
                    <a href="https://brilhamente.thriveo.com.br/certp" class="card-link">
                        Simular Agora <span>→</span>
                    </a>
                </div>

                <!-- 3. Currículos IA -->
                <div class="card">
                    <div class="card-icon">📄</div>
                    <h3>Gerador de Currículos</h3>
                    <p>Crie currículos otimizados para ATS que destacam suas melhores qualidades automaticamente.</p>
                    <a href="https://brilhamente.thriveo.com.br/vitae/" class="card-link">
                        Criar CV <span>→</span>
                    </a>
                </div>

                <!-- 4. Concursos Públicos -->
                <div class="card">
                    <div class="card-icon">🏛️</div>
                    <h3>Simulador de Concursos</h3>
                    <p>Prepare-se para cargos públicos com uma vasta base de questões e provas simuladas.</p>
                    <a href="https://brilhamente.thriveo.com.br/concp" class="card-link">
                        Começar Treino <span>→</span>
                    </a>
                </div>

                <!-- 5. Correção de Testes -->
                <div class="card">
                    <div class="card-icon">📝</div>
                    <h3>Correção Inteligente</h3>
                    <p>Sistema automatizado para correção de testes, exames e provas com feedback instantâneo.</p>
                    <a href="https://brilhamente.thriveo.com.br/prova/" class="card-link">
                        Corrigir Provas <span>→</span>
                    </a>
                </div>

                <!-- 6. Simulador de Entrevista -->
                <div class="card">
                    <div class="card-icon">💬</div>
                    <h3>Simulador de Entrevista</h3>
                    <p>Treine suas respostas com um entrevistador virtual e receba dicas de comportamento e fala.</p>
                    <a href="https://brilhamente.thriveo.com.br/entrev/" class="card-link">
                        Iniciar Entrevista <span>→</span>
                    </a>
                </div>

                <!-- 7. Resumos Acadêmicos -->
                <div class="card">
                    <div class="card-icon">📚</div>
                    <h3>Resumos Acadêmicos</h3>
                    <p>Transforme textos longos e complexos em resumos claros e objetivos para facilitar seu estudo.</p>
                    <a href="https://brilhamente.thriveo.com.br/resumo/" class="card-link">
                        Gerar Resumo <span>→</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> Brilhamente | Thriveo. Todos os direitos reservados.</p>
            <p style="opacity: 0.6; font-size: 0.8rem;">Desenvolvido com Tecnologia de Ponta</p>
        </div>
    </footer>

    <script>
        // Header scroll effect
        window.addEventListener('scroll', () => {
            const header = document.getElementById('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Entrance animations for cards
        const observerOptions = {
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.card').forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            card.style.transitionDelay = `${index * 0.1}s`;
            observer.observe(card);
        });
    </script>
</body>

</html>
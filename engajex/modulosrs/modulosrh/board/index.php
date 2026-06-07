<?php
require_once 'config/config.php';

if (isLoggedIn()) {
    redirect('pages/dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de Gestão de RH com IA - Onboarding, Offboarding e Cultura">
    <title>Proftest Board - Gestão Inteligente de Talentos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="logo">
            <i class="fa-solid fa-brain"></i> Proftest Board
        </div>
        <div class="nav-links">
            <a href="#features">Funcionalidades</a>
            <a href="#request">Solicitar Acesso</a>
            <a href="login.php" class="btn btn-primary">Entrar</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Transforme a Jornada do Colaborador com Inteligência Artificial</h1>
            <p>Do onboarding ao offboarding, nossa plataforma utiliza IA avançada para engajar, reter e capturar
                conhecimento institucional de forma automática e humanizada.</p>
            <div class="hero-buttons">
                <a href="#request" class="btn btn-primary">Solicitar Acesso</a>
                <a href="#features" class="btn btn-outline">Saiba Mais</a>
            </div>
        </div>
        <!-- Optional: Hero Image or Graphic could go here -->
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="section-title">
            <h2>Funcionalidades Exclusivas</h2>
            <p>Tecnologia de ponta para potencializar o seu RH</p>
        </div>

        <div class="features-grid">
            <!-- AI Onboarding Buddy -->
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fa-solid fa-robot"></i>
                </div>
                <h3>AI Onboarding Buddy</h3>
                <p>Assistente virtual que acompanha novos colaboradores nos primeiros 90 dias. Respostas instantâneas e
                    check-ins automatizados para garantir uma integração suave.</p>
            </div>

            <!-- OffBoarding Inteligente -->
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fa-solid fa-door-open"></i>
                </div>
                <h3>OffBoarding Inteligente</h3>
                <p>Chatbot empático que conduz entrevistas de desligamento, identificando motivos reais da saída e
                    percepções sobre a empresa e liderança.</p>
            </div>

            <!-- Knowledge Transfer -->
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fa-solid fa-brain"></i>
                </div>
                <h3>Knowledge Transfer Automático</h3>
                <p>Captura conhecimento crítico de quem está saindo através de IA. Transcrição e organização automática
                    para reduzir a perda de capital intelectual.</p>
            </div>

            <!-- Identidade Organizacional -->
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fa-solid fa-users"></i>
                </div>
                <h3>Cultura & Identidade</h3>
                <p>Gestão completa da identidade organizacional: Propósito, Missão, Visão e Valores. Gere e analise a
                    cultura da sua empresa com dados reais.</p>
            </div>
        </div>
    </section>

    <!-- LGPD Section -->
    <section class="lgpd-section">
        <div class="lgpd-content">
            <span class="lgpd-badge"><i class="fa-solid fa-shield-halved"></i> LGPD Compliance</span>
            <h2>Segurança e Privacidade em Primeiro Lugar</h2>
            <p>Estamos totalmente adequados à Lei Geral de Proteção de Dados (LGPD). Todos os dados processados, desde
                entrevistas até informações pessoais, são criptografados e tratados com o máximo rigor de privacidade e
                ética.</p>
        </div>
    </section>

    <!-- Access Request Section -->
    <section id="request" class="access-request">
        <div class="section-title">
            <h2>Como Acessar o Sistema</h2>
            <p>O acesso é exclusivo para empresas cadastradas.</p>
        </div>

        <div class="contact-box">
            <p>Para solicitar uma conta para sua empresa, envie um e-mail corporativo para:</p>
            <a href="mailto:efsantos@proftest.com.br" class="contact-email">efsantos@proftest.com.br</a>
            <p><strong>Importante:</strong> Informe seu Nome, Cargo e Empresa. <br>Solicitações de e-mails pessoais
                (Gmail, Hotmail, etc) não serão aceitas para criação de contas corporativas.</p>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Proftest. Todos os direitos reservados.</p>
    </footer>

</body>

</html>
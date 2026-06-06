<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProfTest - Sistema de Recrutamento Inteligente</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Icons (Phosphor Icons for a modern look) -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>

<body>
    <div class="hero">
        <div class="container hero-content">
            <h1>Recrutamento Inteligente com Auxílio de IA</h1>
            <p>Otimize seus processos seletivos. Crie roteiros de entrevista personalizados e gerencie candidatos com
                eficiência.</p>

            <div class="glass-card features">
                <div class="feature-card">
                    <div class="feature-icon"><i class="ph ph-robot"></i></div>
                    <h3>IA Generativa</h3>
                    <p>Crie roteiros de entrevista completos baseados no cargo e especificações usando inteligência
                        artificial avançada.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="ph ph-users-three"></i></div>
                    <h3>Gestão de Candidatos</h3>
                    <p>Organize seus processos seletivos e mantenha o controle dos candidatos de forma centralizada.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="ph ph-shield-check"></i></div>
                    <h3>Segurança Total</h3>
                    <p>Acesso restrito e seguro, garantindo a privacidade dos dados da sua empresa e candidatos.</p>
                </div>
            </div>

            <div class="contact-info glass-card">
                <h3>Solicitar Acesso</h3>
                <p>Para ter acesso ao sistema, encaminhe um e-mail informando <strong>Nome</strong>,
                    <strong>E-mail</strong> e <strong>Empresa</strong> para:</p>
                <p class="contact-email">efsantos@proftest.com.br</p>
                <p><small style="color: #ef4444; display: block; margin-top: 0.5rem;">* E-mails pessoais (gmail,
                        hotmail, etc) não serão aceitos.</small></p>
                <div style="margin-top: 1.5rem;">
                    <a href="login.php" class="btn btn-primary">Já tenho acesso</a>
                </div>
            </div>

            <div class="lgpd-notice">
                <p><i class="ph ph-lock-key"></i> <strong>Conformidade LGPD:</strong> Este sistema segue rigorosamente
                    as diretrizes da Lei Geral de Proteção de Dados. Seus dados são armazenados de forma segura e
                    utilizados apenas para os fins do processo seletivo.</p>
            </div>
        </div>
    </div>
</body>

</html>
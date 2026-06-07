<?php
require_once 'config/db.php';

// Pre-check if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['company_id'] = $user['company_id'];

            header("Location: index.php");
            exit;
        } else {
            $error = "Credenciais inválidas.";
        }
    } else {
        $error = "Preencha todos os campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sinergy Cult | Transforme a Cultura da sua Empresa</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;700&display=swap"
        rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>

<body>

    <!-- Navbar for Landing Page -->
    <nav class="navbar"
        style="position: fixed; width: 100%; top: 0; backdrop-filter: blur(10px); background: rgba(15, 23, 42, 0.8); border-bottom: 1px solid rgba(255,255,255,0.05);">
        <div class="nav-container">
            <div class="nav-brand" style="font-size: 1.8rem;">SinergyCult</div>
            <div>
                <a href="#funcionalidades" class="nav-link">Funcionalidades</a>
                <a href="#lgpd" class="nav-link">LGPD</a>
                <a href="#solicitar" class="btn btn-primary"
                    style="margin-left: 1rem; font-size: 0.9rem; padding: 0.5rem 1rem;">Solicitar Acesso</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="container hero-section animate-fade" style="margin-top: 60px;">
        <div class="hero-content">
            <h1 style="font-size: 3.5rem; line-height: 1.1; margin-bottom: 1.5rem;">
                Desvende a <span
                    style="background: linear-gradient(135deg, #3b82f6 0%, #10b981 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Cultura</span>
                da sua Organização com IA
            </h1>
            <p style="font-size: 1.25rem; color: var(--text-muted); margin-bottom: 2.5rem; max-width: 600px;">
                Uma plataforma inteligente que conecta a identidade que você define com a percepção real dos seus
                colaboradores. Alinhe propósitos, identifique gaps e fortaleça seus valores.
            </p>
            <div style="display: flex; gap: 1rem;">
                <a href="#funcionalidades" class="btn btn-outline">Saiba Mais</a>
            </div>
        </div>

        <!-- Login Box -->
        <div class="card hero-form">
            <h2 class="text-center" style="margin-bottom: 1.5rem; font-size: 1.5rem;">Área do Cliente</h2>
            <?php if ($error): ?>
                <div
                    style="background: rgba(239, 68, 68, 0.2); color: #fca5a5; padding: 0.75rem; border-radius: 0.5rem; margin-bottom: 1.5rem; text-align: center; font-size: 0.9rem;">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="input-group">
                    <label>E-mail</label>
                    <input type="email" name="email" class="form-control" required placeholder="seu@empresa.com">
                </div>

                <div class="input-group">
                    <label>Senha</label>
                    <input type="password" name="password" class="form-control" required placeholder="••••••••">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">Entrar na
                    Plataforma</button>
            </form>
        </div>
    </section>

    <!-- Features Section -->
    <section id="funcionalidades" style="background: rgba(30, 41, 59, 0.3); padding: 5rem 0;">
        <div class="container">
            <div class="text-center" style="margin-bottom: 4rem;">
                <h2 style="font-size: 2.5rem;">Como Funciona</h2>
                <p style="color: var(--text-muted);">Tecnologia a serviço do capital humano.</p>
            </div>

            <div class="grid-3">
                <div class="feature-box">
                    <div class="feature-icon"><i class="fas fa-fingerprint"></i></div>
                    <h3>Identidade Organizacional</h3>
                    <p style="color: var(--text-muted)">Defina claramente seu propósito, missão, visão e valores.
                        Estabeleça os pilares que guiam sua empresa.</p>
                </div>

                <div class="feature-box">
                    <div class="feature-icon"><i class="fas fa-poll-h"></i></div>
                    <h3>Pesquisas de Clima</h3>
                    <p style="color: var(--text-muted)">Crie formulários customizáveis para captar a percepção real dos
                        colaboradores sobre o ambiente de trabalho.</p>
                </div>

                <div class="feature-box">
                    <div class="feature-icon"><i class="fas fa-brain"></i></div>
                    <h3>Análise via IA</h3>
                    <p style="color: var(--text-muted)">Nossa Inteligência Artificial cruza os dados e gera insights
                        profundos, identificando divergências e sugerindo correções.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- LGPD Section -->
    <section id="lgpd" class="container">
        <div class="lgpd-section">
            <h2 style="margin-bottom: 1rem;">Compromisso com a LGPD</h2>
            <p style="max-width: 800px; margin: 0 auto; margin-bottom: 2rem; color: var(--text-muted);">
                A segurança dos seus dados é nossa prioridade. O Sinergy Cult está em total conformidade com a Lei Geral
                de Proteção de Dados (13.709/2018). Todos os dados processados são anonimizados para fins estatísticos e
                utilizamos criptografia de ponta a ponta.
            </p>
            <div style="display: flex; gap: 2rem; justify-content: center; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 0.5rem;"><i class="fas fa-check-circle"
                        style="color: var(--success)"></i> Dados Criptografados</div>
                <div style="display: flex; align-items: center; gap: 0.5rem;"><i class="fas fa-check-circle"
                        style="color: var(--success)"></i> Anonimização de Respostas</div>
                <div style="display: flex; align-items: center; gap: 0.5rem;"><i class="fas fa-check-circle"
                        style="color: var(--success)"></i> Transparência Total</div>
            </div>
        </div>
    </section>

    <!-- Access Request Section -->
    <section id="solicitar" class="container access-request">
        <div class="grid-2" style="align-items: center;">
            <div>
                <h2 style="font-size: 2.5rem; margin-bottom: 1.5rem;">Solicite seu Acesso</h2>
                <p style="color: var(--text-muted); font-size: 1.1rem; margin-bottom: 2rem;">
                    O Sinergy Cult é uma plataforma exclusiva para empresas comprometidas com a evolução cultural. Para
                    garantir a segurança e a integridade da comunidade, realizamos uma validação manual de cada
                    solicitação.
                </p>

                <div
                    style="background: rgba(239, 68, 68, 0.1); border-left: 4px solid var(--danger); padding: 1.5rem; margin-bottom: 2rem;">
                    <h4 style="color: var(--danger); margin-bottom: 0.5rem;"><i class="fas fa-exclamation-triangle"></i>
                        Atenção</h4>
                    <p style="color: #fca5a5; font-size: 0.95rem;">
                        Não aceitamos cadastros com e-mails pessoais (Gmail, Hotmail, Yahoo, etc). <br>
                        <strong>Apenas e-mails corporativos serão processados.</strong>
                    </p>
                </div>
            </div>

            <div class="card"
                style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border: 1px solid rgba(255,255,255,0.1);">
                <h3 style="margin-bottom: 1.5rem;">Como Solicitar</h3>
                <p style="margin-bottom: 1.5rem; color: var(--text-muted);">Envie um e-mail para nossa equipe de
                    onboarding:</p>

                <a href="mailto:efsantos@proftest.com.br"
                    style="display: block; background: rgba(255,255,255,0.05); padding: 1.5rem; border-radius: var(--radius); text-align: center; margin-bottom: 2rem; border: 1px dashed var(--primary);">
                    <i class="fas fa-envelope"
                        style="font-size: 1.5rem; color: var(--primary); margin-bottom: 0.5rem; display: block;"></i>
                    <span
                        style="font-size: 1.2rem; color: var(--text-main); font-weight: 600;">efsantos@proftest.com.br</span>
                </a>

                <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 0.5rem;">No corpo do e-mail,
                    informe obrigatoriamente:</p>
                <ul style="padding-left: 1.5rem; color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">
                    <li>Nome Completo do Responsável</li>
                    <li>E-mail Corporativo</li>
                    <li>Nome da Empresa</li>
                    <li>CNPJ (Opcional, para agilizar)</li>
                </ul>
            </div>
        </div>
    </section>

    <footer
        style="background: #020617; padding: 4rem 2rem; margin-top: 4rem; text-align: center; color: var(--text-muted); border-top: 1px solid var(--border);">
        <div class="container">
            <h2 class="nav-brand" style="justify-content: center; display: flex; margin-bottom: 1.5rem;">SinergyCult
            </h2>
            <p>&copy; <?php echo date('Y'); ?> Sinergy Cult. Todos os direitos reservados.</p>
        </div>
    </footer>

</body>

</html>
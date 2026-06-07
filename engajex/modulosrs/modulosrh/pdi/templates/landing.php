<?php
// Include Auth Handling
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth/AuthHandler.php';

use PDI\Auth\AuthHandler;

$authHandler = new AuthHandler($pdo);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Auto-create admin strictly for dev/setup phases or if handled inside AuthHandler
    // $authHandler->createAdmin($email, $password); 

    if ($authHandler->login($email, $password)) {
        header('Location: ?route=dashboard');
        exit;
    } else {
        $error = 'Email ou senha inválidos';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDI Pro - Sistema de Desenvolvimento Profissional</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .login-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            color: #333;
        }

        .login-card h3 {
            margin-bottom: 20px;
            color: #1a202c;
        }

        .form-control {
            display: block;
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }

        .access-info {
            background: rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: 12px;
            margin-top: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .access-info h4 {
            color: #fff;
            margin-bottom: 10px;
        }

        .highlight-email {
            background: rgba(102, 126, 234, 0.2);
            color: #fff;
            padding: 4px 8px;
            border-radius: 4px;
            font-family: monospace;
        }
    </style>
</head>

<body>
    <nav class="navbar" id="navbar">
        <div class="container nav-container">
            <div class="logo">
                <span class="logo-text">PDI<span class="logo-accent">Pro</span></span>
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="hero-background">
            <div class="gradient-orb orb-1"></div>
            <div class="gradient-orb orb-2"></div>
        </div>
        <div class="container hero-container" style="grid-template-columns: 1.2fr 0.8fr;">
            <!-- Left Side: Content -->
            <div class="hero-content">
                <div class="lgpd-badge"
                    style="background: rgba(255,255,255,0.1); color: #fff; border: 1px solid rgba(255,255,255,0.2);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                    </svg>
                    Em conformidade com a LGPD
                </div>
                <h1 class="hero-title" style="font-size: 3rem;">
                    Seu Plano de Desenvolvimento Individual com <span class="gradient-text">Inteligência
                        Artificial</span>
                </h1>
                <p class="hero-description">
                    Sistema focado exclusivamente no crescimento profissional. Alinhe propósitos pessoais com a
                    identidade da empresa, gere PDIs automáticos e acompanhe o desenvolvimento de competências.
                </p>

                <div class="access-info">
                    <h4>Não tem acesso?</h4>
                    <p style="color: #cbd5e0; margin-bottom: 15px;">O sistema é privado para empresas parceiras. Para
                        cadastrar sua organização:</p>
                    <p style="color: #cbd5e0;">Envie um e-mail para <span
                            class="highlight-email">efsantos@proftest.com.br</span> contendo:</p>
                    <ul style="margin: 10px 0 10px 20px; color: #a0aec0;">
                        <li>Nome da Empresa</li>
                        <li>Nome do Responsável</li>
                        <li>E-mail Corporativo (Obrigatório)</li>
                    </ul>
                </div>
            </div>

            <!-- Right Side: Login Form -->
            <div class="hero-visual">
                <div class="login-card">
                    <h3>Acesso ao Sistema</h3>
                    <?php if ($error): ?>
                        <div
                            style="background: #fed7d7; color: #c53030; padding: 10px; border-radius: 6px; margin-bottom: 15px;">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="mb-3">
                            <label style="display:block; margin-bottom:5px; font-weight:500;">E-mail</label>
                            <input type="email" name="email" class="form-control" placeholder="seu@email.com" required>
                        </div>
                        <div class="mb-3">
                            <label style="display:block; margin-bottom:5px; font-weight:500;">Senha</label>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                        </div>
                        <button type="submit" class="login-btn">Entrar</button>
                    </form>

                    <div style="margin-top: 15px; text-align: center; font-size: 0.9rem;">
                        <a href="?route=forgot-password" style="color: #667eea; text-decoration: none;">Esqueceu a
                            senha?</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Specific Features Section -->
    <section class="features" id="funcionalidades">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Foco no Desenvolvimento <span class="gradient-text">Real</span></h2>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <h3 class="feature-title">Identidade & Propósito</h3>
                    <p class="feature-description">
                        Conecte a missão e valores da empresa com os objetivos de cada colaborador.
                    </p>
                </div>
                <div class="feature-card">
                    <h3 class="feature-title">PDI Gerado por IA</h3>
                    <p class="feature-description">
                        Nossa IA analisa competências e gaps para sugerir um plano de ação prático e personalizado.
                    </p>
                </div>
                <div class="feature-card">
                    <h3 class="feature-title">Acompanhamento e Feedback</h3>
                    <p class="feature-description">
                        Gestores e colaboradores acompanham o progresso em tempo real.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Proftest. Todos os direitos reservados. Sistema seguro e adequado à
                    LGPD.</p>
            </div>
        </div>
    </footer>
</body>

</html>
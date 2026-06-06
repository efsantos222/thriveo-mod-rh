<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    try {
        $config = require '../config/database.php';
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
        $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);

        $stmt = $pdo->prepare('SELECT id_usuario, nome, senha, perfil, id_empresa FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && $password === $user['senha']) {
            $_SESSION['user_id'] = $user['id_usuario'];
            $_SESSION['user_name'] = $user['nome'];
            $_SESSION['user_profile'] = $user['perfil'];
            $_SESSION['id_empresa'] = $user['id_empresa'];
            header('Location: ../dashboard.php');
            exit;
        } else {
            $_SESSION['error'] = 'Email ou senha inválidos';
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Erro ao conectar com o banco de dados: ' . $e->getMessage();
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sinergy Coaching - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #0ea5e9;
            --dark: #0f172a;
            --light: #f8fafc;
            --gray: #64748b;
            --white: #ffffff;
            --glass: rgba(255, 255, 255, 0.95);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--dark);
            line-height: 1.6;
            background-color: var(--light);
            overflow-x: hidden;
        }

        /* Navigation */
        .navbar {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
        }

        .nav-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            font-size: 0.95rem;
        }

        .nav-links a:hover {
            color: var(--white);
        }

        /* Hero Section with Login */
        .hero {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--dark) 0%, #1e293b 100%);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -20%;
            left: -10%;
            width: 50%;
            height: 50%;
            background: radial-gradient(circle, var(--secondary) 0%, transparent 60%);
            opacity: 0.1;
            filter: blur(100px);
        }

        .hero::after {
            content: '';
            position: absolute;
            bottom: -20%;
            right: -10%;
            width: 50%;
            height: 50%;
            background: radial-gradient(circle, var(--primary) 0%, transparent 60%);
            opacity: 0.15;
            filter: blur(100px);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            position: relative;
            z-index: 10;
        }

        .hero-content {
            color: var(--white);
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            background: linear-gradient(to right, #60a5fa, #38bdf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p {
            font-size: 1.25rem;
            color: #cbd5e1;
            margin-bottom: 2rem;
            max-width: 500px;
        }

        .feature-preview {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .preview-item {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 1rem;
            border-radius: 12px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Login Card */
        .login-card {
            background: var(--glass);
            padding: 3rem;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            max-width: 450px;
            width: 100%;
            margin-left: auto;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h2 {
            font-size: 1.8rem;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: var(--gray);
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            font-size: 1.1rem;
        }

        .form-control {
            width: 100%;
            padding: 12px 12px 12px 3rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.4);
        }

        .error-alert {
            background: #fee2e2;
            color: #991b1b;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            text-align: center;
            border: 1px solid #fecaca;
        }

        /* Features Section */
        .features-section {
            padding: 6rem 2rem;
            background: var(--white);
        }

        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1rem;
        }

        .section-subtitle {
            color: var(--gray);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background: var(--light);
            padding: 2rem;
            border-radius: 16px;
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.1);
            border-color: var(--primary);
        }

        .feature-icon {
            width: 50px;
            height: 50px;
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .feature-card h3 {
            font-size: 1.25rem;
            margin-bottom: 1rem;
            color: var(--dark);
        }

        .feature-card p {
            color: var(--gray);
            font-size: 0.95rem;
        }

        /* Security & Compliance */
        .security-section {
            padding: 5rem 2rem;
            background: var(--dark);
            color: var(--white);
            text-align: center;
        }

        .lgpd-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.1);
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Access Info */
        .access-info {
            padding: 4rem 2rem;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            text-align: center;
        }

        .access-box {
            max-width: 700px;
            margin: 0 auto;
            background: var(--white);
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        }

        .email-link {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            font-size: 1.2rem;
            display: block;
            margin: 1rem 0;
        }

        .highlight {
            color: #dc2626;
            font-weight: 600;
            font-size: 0.9rem;
            display: block;
            margin-top: 1rem;
        }

        @media (max-width: 968px) {
            .container {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .hero-content {
                text-align: center;
                margin-bottom: 2rem;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .feature-preview {
                justify-content: center;
            }

            .login-card {
                margin: 0 auto;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="logo" style="color:white; font-weight:700; font-size:1.2rem;">Sinergy Coaching</div>
        <ul class="nav-links">
            <li><a href="#features">Funcionalidades</a></li>
            <li><a href="#lgpd">LGPD</a></li>
            <li><a href="#access">Solicitar Acesso</a></li>
        </ul>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Potencialize seus <br>Resultados de Coaching</h1>
                <p>Uma plataforma completa para gestão de sessões, avaliações comportamentais e acompanhamento de metas
                    SMART.</p>

                <div class="feature-preview">
                    <div class="preview-item">
                        <i class="fas fa-check-circle"></i> Gestão Completa
                    </div>
                    <div class="preview-item">
                        <i class="fas fa-chart-line"></i> Análise de Dados
                    </div>
                </div>
            </div>

            <div class="login-card">
                <div class="login-header">
                    <h2>Bem-vindo de volta</h2>
                    <p>Acesse sua conta para continuar</p>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="error-alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>

                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                    <div class="form-group">
                        <label for="email">E-mail Corporativo</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" class="form-control"
                                placeholder="seu.nome@empresa.com" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password">Senha</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" class="form-control"
                                placeholder="••••••••" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-login">
                        Entrar no Sistema
                    </button>
                    <a href="forgot-password.php"
                        style="display: block; text-align: center; margin-top: 1rem; color: var(--primary); text-decoration: none; font-size: 0.9rem;">Esqueci
                        minha senha</a>
                </form>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="section-header">
            <h2 class="section-title">Funcionalidades do Sistema</h2>
            <p class="section-subtitle">Tudo o que você precisa para elevar o nível da sua gestão de coaching.</p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <h3>Gestão de Sessões</h3>
                <p>Organize e registre todas as sessões de coaching de forma estruturada e eficiente.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-brain"></i>
                </div>
                <h3>Diagnósticos (DISC/MBTI)</h3>
                <p>Ferramentas integradas para análise de perfil comportamental e personalidade.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-bullseye"></i>
                </div>
                <h3>Metas SMART</h3>
                <p>Definição e acompanhamento de metas Específicas, Mensuráveis, Atingíveis, Relevantes e Temporais.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-comment-dots"></i>
                </div>
                <h3>Feedback 360°</h3>
                <p>Sistema completo para solicitação e recebimento de feedbacks contínuos.</p>
            </div>
        </div>
    </section>

    <!-- Security Section -->
    <section class="security-section" id="lgpd">
        <div class="lgpd-badge">
            <i class="fas fa-shield-alt"></i> Totalmente em conformidade com a LGPD
        </div>
        <div style="max-width: 800px; margin: 0 auto; color: #cbd5e1;">
            <p>A segurança dos seus dados é nossa prioridade. Todo o processamento e armazenamento de informações segue
                rigorosamente as diretrizes da Lei Geral de Proteção de Dados (Lei nº 13.709/2018), garantindo
                confidencialidade e integridade.</p>
        </div>
    </section>

    <!-- Access Request -->
    <section class="access-info" id="access">
        <div class="access-box">
            <h3>Solicitação de Acesso</h3>
            <p style="margin-top: 1rem; color: var(--gray);">Este é um sistema exclusivo para empresas parceiras. Para
                solicitar acesso, entre em contato:</p>

            <a href="mailto:efsantos@proftest.com.br" class="email-link">
                efsantos@proftest.com.br
            </a>

            <p style="font-size: 0.9rem; color: var(--gray);">
                No e-mail, favor informar: <strong>Nome Completo</strong>, <strong>E-mail Corporativo</strong> e
                <strong>Empresa</strong>.
            </p>

            <span class="highlight">
                <i class="fas fa-info-circle"></i> Atenção: Não serão aceitos e-mails pessoais (Gmail, Hotmail, etc.),
                apenas contas corporativas.
            </span>
        </div>
    </section>

    <footer style="text-align: center; padding: 2rem; background: var(--white); color: var(--gray); font-size: 0.9rem;">
        &copy; <?php echo date('Y'); ?> Sinergy Coaching Systems. Todos os direitos reservados.
    </footer>
</body>

</html>
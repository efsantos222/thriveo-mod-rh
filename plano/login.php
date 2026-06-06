<?php
session_start();
require_once 'config/db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT u.*, c.name as company_name FROM users u LEFT JOIN companies c ON u.company_id = c.id WHERE u.email = ? AND u.status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Verificar Trial (30 dias) se não for admin
        if ($user['role'] !== 'admin') {
            $trialEnd = date('Y-m-d', strtotime($user['trial_start_date'] . ' + 30 days'));
            if (date('Y-m-d') > $trialEnd) {
                $error = "Seu período de avaliação de 30 dias expirou. Contate o suporte.";
            }
        }

        if (empty($error)) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['company_id'] = $user['company_id'];
            $_SESSION['company_name'] = $user['company_name'];

            header("Location: index.php");
            exit();
        }
    } else {
        $error = "E-mail ou senha incorretos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sinergy Matrix</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563EB;
            --surface: #ffffff;
            --bg: #f8fafc;
            --text-main: #0f172a;
            --text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            display: flex;
            width: 90%;
            max-width: 1000px;
            background: var(--surface);
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            min-height: 600px;
        }

        .login-left {
            flex: 1;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-right {
            flex: 1;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            padding: 3rem;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }

        .login-right::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        h1 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            color: var(--text-main);
        }

        h2 {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            font-weight: 700;
            z-index: 1;
        }

        p {
            color: var(--text-muted);
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .right-text {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
            z-index: 1;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-main);
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.2s;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn {
            width: 100%;
            padding: 0.75rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn:hover {
            background-color: #1d4ed8;
        }

        .alert {
            padding: 1rem;
            background-color: #fef2f2;
            border: 1px solid #fee2e2;
            color: #b91c1c;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .features-list {
            list-style: none;
            margin-bottom: 2rem;
            z-index: 1;
        }

        .features-list li {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .features-list i {
            color: #93c5fd;
        }

        .contact-box {
            background: rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            z-index: 1;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }

            .login-right {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-left">
            <div style="margin-bottom: 2rem;">
                <img src="img/logo.jpg" alt="Sinergy Matrix" style="height: 80px;">
            </div>
            <h1>Bem-vindo de volta</h1>
            <p>Acesse seu painel de gestão estratégica.</p>

            <?php if ($error): ?>
                <div class="alert"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>E-mail Corporativo</label>
                    <input type="email" name="email" required placeholder="seu.nome@empresa.com.br">
                </div>
                <div class="form-group">
                    <label>Senha</label>
                    <input type="password" name="password" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn">Entrar na Plataforma</button>
            </form>

            <div style="margin-top: 2rem; text-align: center; font-size: 0.9rem; color: var(--text-muted);">
                &copy; <?= date('Y') ?> Sinergy Matrix
            </div>
        </div>
        <div class="login-right">
            <h2>Transforme seu Portfólio de TI</h2>

            <ul class="features-list right-text">
                <li><i class="fa-solid fa-check-circle"></i> Matriz de Portfólio Automatizada</li>
                <li><i class="fa-solid fa-check-circle"></i> Inteligência Artificial Estratégica (GPT-4o)</li>
                <li><i class="fa-solid fa-check-circle"></i> Planos de Ação Customizados</li>
                <li><i class="fa-solid fa-check-circle"></i> Análise de Market Upsell</li>
            </ul>

            <div class="contact-box right-text">
                <strong><i class="fa-solid fa-lock"></i> Acesso Restrito</strong>
                <p style="margin-top: 0.5rem; margin-bottom: 1rem; font-size: 0.9rem; color: rgba(255,255,255,0.8);">
                    Para garantir a qualidade e exclusividade, o acesso é concedido apenas mediante solicitação via
                    e-mail corporativo.
                </p>
                <div style="font-size: 0.9rem;">
                    Solicite seu cadastro:<br>
                    <a href="mailto:efsantos@proftest.com.br"
                        style="color: white; font-weight: 700;">efsantos@proftest.com.br</a>
                </div>
                <div
                    style="margin-top: 1rem; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 0.5rem; font-size: 0.8rem;">
                    * Acesso gratuito limitado a 30 dias para avaliação.
                </div>
            </div>
        </div>
    </div>
</body>

</html>
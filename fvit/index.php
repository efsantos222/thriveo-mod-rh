<?php
session_start();
require_once 'config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        if (empty($email) || empty($password)) {
            $error = "Por favor, preencha todos os campos.";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['company_id'] = $user['company_id'];
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Credenciais inválidas.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proftest Formata - Sistema de Currículos com IA</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .page-wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .main-hero {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .content-split {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            max-width: 1200px;
            width: 100%;
            align-items: center;
        }

        @media (max-width: 900px) {
            .content-split {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="page-wrapper">
        <div class="main-hero">
            <div class="content-split">
                <!-- Left: Info -->
                <div class="info-section">
                    <div class="lgpd-badge">
                        <span style="font-size: 1.2em; margin-right: 0.5rem;">🔒</span> Em conformidade com a LGPD
                    </div>
                    <h1>Revolucione sua Gestão de Talentos</h1>
                    <p style="font-size: 1.1rem;">
                        Sistema inteligente de formatação de currículos potencializado por Inteligência Artificial
                        (OpenAI GPT-4o).
                        Organize, padronize e extraia o melhor dos candidatos em segundos.
                    </p>

                    <div class="features-grid" style="grid-template-columns: 1fr; gap: 1rem; margin: 2rem 0;">
                        <div class="feature-card" style="padding: 1rem 1.5rem; display: flex; align-items: center;">
                            <span style="margin-right: 1rem;">📄</span> Upload direto de PDF
                        </div>
                        <div class="feature-card" style="padding: 1rem 1.5rem; display: flex; align-items: center;">
                            <span style="margin-right: 1rem;">🤖</span> Análise e Reescrita via IA
                        </div>
                        <div class="feature-card" style="padding: 1rem 1.5rem; display: flex; align-items: center;">
                            <span style="margin-right: 1rem;">⚙️</span> Customização de Campos
                        </div>
                    </div>

                    <div class="access-info"
                        style="margin-top: 3rem; padding: 1.5rem; background: rgba(255,255,255,0.05); border-radius: 12px;">
                        <h3 style="font-size: 1.2rem; color: var(--text-main);">Como acessar?</h3>
                        <p style="margin-bottom: 0.5rem; font-size: 0.95rem;">
                            Este é um sistema corporativo privado. Para solicitar acesso, encaminhe um e-mail para:
                        </p>
                        <a href="mailto:efsantos@proftest.com.br"
                            style="color: var(--primary-light); font-weight: 600;">efsantos@proftest.com.br</a>
                        <p style="margin-top: 1rem; font-size: 0.9rem; opacity: 0.8;">
                            Informe: <strong>Nome Completo, E-mail Corporativo e Empresa.</strong><br>
                            <em>Nota: Não serão aceitos e-mails pessoais (ex: gmail, hotmail).</em>
                        </p>
                    </div>
                </div>

                <!-- Right: Login Box -->
                <div class="login-section">
                    <div class="glass-card">
                        <h2 style="text-align: center; margin-bottom: 2rem;">Acesso ao Sistema</h2>
                        <?php if ($error): ?>
                            <div
                                style="background: rgba(220, 50, 50, 0.2); border: 1px solid rgba(220, 50, 50, 0.5); padding: 1rem; border-radius: 8px; margin-bottom: 1rem; color: #ffcccc;">
                                <?= $error ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="email">E-mail</label>
                                <input type="email" id="email" name="email" required
                                    placeholder="seu.email@empresa.com">
                            </div>

                            <div class="form-group">
                                <label for="password">Senha</label>
                                <input type="password" id="password" name="password" required placeholder="••••••••">
                            </div>

                            <button type="submit" name="login" class="btn btn-primary"
                                style="width: 100%;">Entrar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
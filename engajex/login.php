<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT id, name, password, role, company_id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $email; // Added email
                $_SESSION['role'] = $user['role'];
                $_SESSION['company_id'] = $user['company_id'] ?? 1;

                if ($user['role'] === 'admin') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit;
            } else {
                // FALLBACK: Check if user exists in SoftSkill (ss_users)
                $stmt_ss = $pdo->prepare("SELECT id, name, password, role FROM ss_users WHERE email = ?");
                $stmt_ss->execute([$email]);
                $user_ss = $stmt_ss->fetch();

                if ($user_ss && password_verify($password, $user_ss['password'])) {
                    $_SESSION['user_id'] = $user_ss['id']; // Map to generic user_id for session consistency
                    $_SESSION['name'] = $user_ss['name'];
                    $_SESSION['email'] = $email;
                    $_SESSION['role'] = $user_ss['role'];

                    // Specific SoftSkill session flags
                    $_SESSION['ss_id'] = $user_ss['id'];
                    $_SESSION['ss_role'] = $user_ss['role'];
                    $_SESSION['ss_user_name'] = $user_ss['name'];
                    $_SESSION['ss_direct_user'] = true; // Prevents SSO bridge from overriding ss_id

                    // Redirect candidate directly to their SoftSkill dashboard
                    if ($user_ss['role'] === 'candidate') {
                        header("Location: modulosrs/softskill/public/candidate/dashboard");
                    } else {
                        header("Location: dashboard.php");
                    }
                    exit;
                }
                $error = "Credenciais inválidas.";
            }
        } catch (PDOException $e) {
            $error = "Erro no sistema. Tente novamente.";
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
    <title>Entrar — Engajex</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body class="page-login">

<div class="login-layout">

    <!-- ── Painel Esquerdo: Branding ───────────────────── -->
    <div class="login-panel login-panel--brand">
        <div class="login-brand__grid"></div>

        <div class="login-brand__shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>

        <div class="login-brand__header">
            <a href="index.php" class="login-brand__logo">
                <img src="http://thriveo.com.br/wp-content/uploads/2026/03/logo_thriveo_branco_transparente.png" alt="Engajex">
            </a>
        </div>

        <div class="login-brand__body">
            <p class="login-brand__tagline">Plataforma de RH</p>
            <h1 class="login-brand__title">
                Engaje,<br>
                Desenvolva,<br>
                <span>Transforme.</span>
            </h1>
            <p class="login-brand__subtitle">
                A plataforma completa para empresas que colocam pessoas em primeiro lugar.
            </p>
            <ul class="login-brand__features">
                <li><i class="fa-solid fa-check"></i> Pesquisa de Clima e Engajamento</li>
                <li><i class="fa-solid fa-check"></i> Feedback Contínuo e 1:1</li>
                <li><i class="fa-solid fa-check"></i> People Analytics em tempo real</li>
                <li><i class="fa-solid fa-check"></i> Recrutamento com IA</li>
                <li><i class="fa-solid fa-check"></i> Gamificação e Dinâmicas</li>
            </ul>
        </div>

        <div class="login-brand__footer">
            <span class="login-brand__badge">
                <i class="fa-solid fa-shield-halved"></i>
                LGPD Compliant
            </span>
            <span class="login-brand__badge">
                <i class="fa-solid fa-lock"></i>
                Dados Criptografados
            </span>
        </div>
    </div>

    <!-- ── Painel Direito: Formulário ──────────────────── -->
    <div class="login-panel login-panel--form">
        <div class="login-form-wrap">

            <div class="login-form-header">
                <h2>Bem-vindo de volta</h2>
                <p>Acesse sua conta para continuar</p>
            </div>

            <?php if (isset($_GET['registered'])): ?>
                <div class="login-alert login-alert--success">
                    <i class="fa-solid fa-circle-check"></i>
                    Cadastro realizado com sucesso! Você tem 15 dias de teste. Acesse sua conta.
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="login-alert login-alert--error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="login-form">
                <div class="form-group">
                    <label class="form-label" for="email">E-mail</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-envelope input-icon"></i>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            placeholder="seu@email.com"
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                            required
                            autocomplete="email">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Senha</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-lock input-icon"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            placeholder="••••••••"
                            required
                            autocomplete="current-password">
                        <button type="button" class="toggle-password" onclick="togglePassword()" title="Mostrar/ocultar senha">
                            <i class="fa-solid fa-eye" id="passwordIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    Entrar na plataforma
                </button>
            </form>

            <div class="login-form-footer">
                <a href="index.php">← Voltar para o início</a>
                <span class="divider">·</span>
                <a href="register.php">Criar conta</a>
            </div>

        </div>
    </div>

</div>

<script>
function togglePassword() {
    var input = document.getElementById('password');
    var icon  = document.getElementById('passwordIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>

</body>
</html>
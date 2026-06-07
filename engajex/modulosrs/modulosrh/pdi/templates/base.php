<?php
// Garantir que nenhum output seja enviado antes dos headers
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-BR" class="h-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'PDI Pro'; ?></title>

    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --accent-color: #00f2fe;
            --text-dark: #1a202c;
            --text-muted: #718096;
            --bg-light: #f7fafc;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Navbar Styling */
        .navbar {
            background: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
            padding: 0.8rem 0;
            border-bottom: 1px solid #edf2f7;
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
            font-size: 1.4rem;
            letter-spacing: -0.5px;
        }

        .nav-link {
            font-weight: 500;
            color: #4a5568 !important;
            transition: color 0.2s;
            margin: 0 5px;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
        }

        .navbar-text {
            color: #4a5568 !important;
            font-weight: 600;
        }

        /* Card Styling */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
            transition: transform 0.2s, box-shadow 0.2s;
            background: #ffffff;
            margin-bottom: 1.5rem;
        }

        .card:hover {}

        /* Removed hover effect for base cards to avoid distraction, applied specifically where needed */

        .card-header {
            background-color: #ffffff;
            border-bottom: 1px solid #edf2f7;
            padding: 1.25rem 1.5rem;
            font-weight: 600;
            color: var(--text-dark);
            border-radius: 12px 12px 0 0 !important;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            box-shadow: 0 2px 4px rgba(118, 75, 162, 0.3);
            font-weight: 500;
            padding: 0.5rem 1.2rem;
            border-radius: 8px;
        }

        .btn-primary:hover {
            opacity: 0.9;
            box-shadow: 0 4px 6px rgba(118, 75, 162, 0.4);
        }

        .btn-outline-light {
            border-color: #e2e8f0;
            color: #4a5568;
        }

        .btn-outline-light:hover {
            background: #edf2f7;
            border-color: #cbd5e0;
            color: #2d3748;
        }

        /* Tables */
        .table {
            color: #4a5568;
        }

        .table thead th {
            font-weight: 600;
            color: #718096;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            border-bottom-width: 1px;
            background-color: #f8fafc;
            padding: 1rem;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #edf2f7;
        }

        /* Badges */
        .badge {
            padding: 0.5em 0.8em;
            font-weight: 600;
            border-radius: 6px;
        }

        /* Footer */
        .footer {
            margin-top: auto;
            background: #ffffff;
            border-top: 1px solid #edf2f7;
            padding: 1.5rem 0;
            color: var(--text-muted);
        }

        /* Page Titles */
        h2 {
            font-weight: 700;
            color: #2d3748;
            letter-spacing: -0.5px;
            margin-bottom: 1.5rem;
        }
    </style>
</head>

<body class="d-flex flex-column h-100">
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="?route=dashboard">
                <i class="fas fa-layer-group me-2"></i>PDI Pro
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php $role = $_SESSION['user_role'] ?? 'employee'; ?>

                        <?php if ($role === 'superadmin'): ?>
                            <li class="nav-item"><a class="nav-link" href="?route=dashboard">Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link" href="?route=admin/companies">Empresas</a></li>
                            <li class="nav-item"><a class="nav-link" href="?route=courses/manage">Cursos</a></li>
                            <li class="nav-item"><a class="nav-link" href="?route=admin/settings">Configurações</a></li>

                        <?php elseif ($role === 'company_admin'): ?>
                            <li class="nav-item"><a class="nav-link" href="?route=dashboard">Painel</a></li>
                            <li class="nav-item"><a class="nav-link" href="?route=company/users">Colaboradores</a></li>
                            <li class="nav-item"><a class="nav-link" href="?route=company/identity">Identidade</a></li>
                            <li class="nav-item"><a class="nav-link" href="?route=company/reports">Relatórios</a></li>
                            <li class="nav-item"><a class="nav-link" href="?route=courses">Catálogo</a></li>

                        <?php else: ?>
                            <li class="nav-item"><a class="nav-link" href="?route=dashboard">Home</a></li>
                            <li class="nav-item"><a class="nav-link" href="?route=pdi">Meu PDI</a></li>
                            <li class="nav-item"><a class="nav-link" href="?route=courses">Cursos</a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>

                <div class="d-flex align-items-center">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="dropdown me-3">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button"
                                data-bs-toggle="dropdown">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2"
                                    style="width: 32px; height: 32px;">
                                    <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?>
                                </div>
                                <span><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuário'); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                                <li><a class="dropdown-item" href="?route=logout"><i
                                            class="fas fa-sign-out-alt me-2 text-danger"></i> Sair</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="?route=login" class="btn btn-primary btn-sm">Entrar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="container flex-grow-1 py-5">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger shadow-sm border-0 border-start border-4 border-danger fade show">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success shadow-sm border-0 border-start border-4 border-success fade show">
                <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php echo $content ?? ''; ?>
    </div>

    <footer class="footer">
        <div class="container text-center">
            <span class="small">&copy; <?php echo date('Y'); ?> PDI Pro System. Todos os direitos reservados.</span>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
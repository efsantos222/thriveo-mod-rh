<?php
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../config/database.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesquisa de Clima - Sys Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #0d6efd;
        }
        .main-title {
            color: #2c3e50;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center main-title">Pesquisa de Clima Semanal</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center mt-4">
            <div class="col-md-5 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center p-5">
                        <div class="card-icon">
                            <i class="bi bi-person-check"></i>
                        </div>
                        <h3 class="card-title mb-3">Responder Pesquisa</h3>
                        <p class="card-text mb-4">
                            Digite seu código único para participar da pesquisa de clima semanal.
                        </p>
                        <form action="validate.php" method="POST">
                            <div class="mb-3">
                                <input type="text" class="form-control form-control-lg text-center" 
                                       id="codigo" name="codigo" required maxlength="10" 
                                       placeholder="Digite seu código">
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    Acessar Pesquisa
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-5 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center p-5">
                        <div class="card-icon">
                            <i class="bi bi-gear"></i>
                        </div>
                        <h3 class="card-title mb-3">Área Administrativa</h3>
                        <p class="card-text mb-4">
                            Acesse o painel administrativo para gerenciar pesquisas e visualizar resultados.
                        </p>
                        <div class="d-grid">
                            <a href="<?php echo rtrim(BASE_URL, '/'); ?>/admin/login.php" class="btn btn-outline-primary btn-lg">
                                Acessar Administração
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="text-center mt-4 text-muted">
            <p>&copy; <?php echo date('Y'); ?> Sys Manager - Todos os direitos reservados</p>
        </footer>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

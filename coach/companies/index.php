<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_profile'] !== 'administrador') {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Empresas - Sistema de Coaching</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/users.css"> <!-- Reusing users css for grid/modal -->
</head>

<body>
    <div class="dashboard-container">
        <nav class="sidebar">
            <div class="user-info">
                <h3>Bem-vindo(a),</h3>
                <p><?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
            </div>
            <ul class="menu">
                <li><a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="../users/index.php"><i class="fas fa-users"></i> Usuários</a></li>
                <li><a href="#" class="active"><i class="fas fa-building"></i> Empresas</a></li>
                <li><a href="../sessions/index.php"><i class="fas fa-calendar"></i> Sessões</a></li>
                <li><a href="../questionnaires/index.php"><i class="fas fa-clipboard-list"></i> Questionários</a></li>
                <li><a href="../goals/index.php"><i class="fas fa-bullseye"></i> Metas SMART</a></li>
                <li><a href="../feedback/index.php"><i class="fas fa-comments"></i> Feedback</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
            </ul>
        </nav>
        <main class="content">
            <header class="dashboard-header">
                <h1>Gerenciar Empresas</h1>
                <button class="btn-primary" onclick="openNewCompanyModal()">
                    <i class="fas fa-plus"></i> Nova Empresa
                </button>
            </header>

            <div class="users-grid" id="companiesGrid">
                <!-- Companies will be loaded here -->
            </div>
        </main>
    </div>

    <!-- Modal for new company -->
    <div id="newCompanyModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Nova Empresa</h2>
                <button class="close-button" onclick="closeNewCompanyModal()">&times;</button>
            </div>
            <form id="newCompanyForm" onsubmit="return submitNewCompany(event)">
                <div class="form-group">
                    <label for="nome_empresa">Nome da Empresa</label>
                    <input type="text" id="nome_empresa" name="nome_empresa" required>
                </div>
                <div class="form-group">
                    <label for="cnpj">CNPJ</label>
                    <input type="text" id="cnpj" name="cnpj">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeNewCompanyModal()">Cancelar</button>
                    <button type="submit" class="btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/companies.js"></script>
</body>

</html>
<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_profile'], ['administrador', 'master_coach'])) {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários - Sistema de Coaching</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/users.css">
    <script>
        const USER_PROFILE = '<?php echo $_SESSION['user_profile']; ?>';
    </script>
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

                <!-- Admin sees Users and Companies -->
                <?php if ($_SESSION['user_profile'] === 'administrador'): ?>
                    <li><a href="#" class="active"><i class="fas fa-users"></i> Usuários</a></li>
                    <li><a href="../companies/index.php"><i class="fas fa-building"></i> Empresas</a></li>
                <?php endif; ?>

                <!-- Master Coach only sees Users (Mentorees) -->
                <?php if ($_SESSION['user_profile'] === 'master_coach'): ?>
                    <li><a href="#" class="active"><i class="fas fa-users"></i> Mentorados</a></li>
                <?php endif; ?>

                <li><a href="../sessions/index.php"><i class="fas fa-calendar"></i> Sessões</a></li>
                <li><a href="../questionnaires/index.php"><i class="fas fa-clipboard-list"></i> Questionários</a></li>
                <li><a href="../goals/index.php"><i class="fas fa-bullseye"></i> Metas SMART</a></li>
                <li><a href="../feedback/index.php"><i class="fas fa-comments"></i> Feedback</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
            </ul>
        </nav>
        <main class="content">
            <header class="dashboard-header">
                <h1>
                    <?php echo $_SESSION['user_profile'] === 'administrador' ? 'Gerenciar Usuários' : 'Meus Mentorados'; ?>
                </h1>
                <button class="btn-primary" onclick="openNewUserModal()">
                    <i class="fas fa-plus"></i> Novo Usuário
                </button>
            </header>

            <div class="users-filters">
                <div class="search-box">
                    <input type="text" id="searchUser" placeholder="Buscar usuário...">
                </div>

                <?php if ($_SESSION['user_profile'] === 'administrador'): ?>
                    <div class="filter-options">
                        <select id="filterProfile">
                            <option value="">Todos os perfis</option>
                            <option value="administrador">Administrador</option>
                            <option value="master_coach">Master Coach</option>
                            <option value="coach">Coach</option>
                            <option value="coachee">Coachee</option>
                            <option value="colaborador">Colaborador</option>
                            <option value="gestor">Gestor</option>
                        </select>
                    </div>
                <?php endif; ?>
            </div>

            <div class="users-grid" id="usersGrid">
                <!-- Users will be loaded here via JavaScript -->
            </div>
        </main>
    </div>

    <!-- Modal para novo usuário -->
    <div id="newUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Novo Usuário</h2>
                <button class="close-button" onclick="closeNewUserModal()">&times;</button>
            </div>
            <form id="newUserForm" onsubmit="return submitNewUser(event)">
                <div class="form-group">
                    <label for="nome">Nome</label>
                    <input type="text" id="nome" name="nome" required>
                </div>
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" required>
                </div>

                <?php if ($_SESSION['user_profile'] === 'administrador'): ?>
                    <div class="form-group">
                        <label for="perfil">Perfil</label>
                        <select id="perfil" name="perfil" required onchange="toggleCompanySelect()">
                            <option value="">Selecione...</option>
                            <option value="administrador">Administrador</option>
                            <option value="master_coach">Master Coach</option>
                            <option value="coach">Coach</option>
                            <option value="coachee">Coachee</option>
                            <option value="colaborador">Colaborador</option>
                            <option value="gestor">Gestor</option>
                        </select>
                    </div>
                    <div class="form-group" id="companyGroup" style="display:none;">
                        <label for="id_empresa">Empresa</label>
                        <select id="id_empresa" name="id_empresa">
                            <option value="">Nenhuma / Carregando...</option>
                        </select>
                    </div>
                <?php else: ?>
                    <input type="hidden" id="perfil" name="perfil" value="coachee">
                <?php endif; ?>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeNewUserModal()">Cancelar</button>
                    <button type="submit" class="btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/users.js?v=<?php echo time(); ?>"></script>
</body>

</html>
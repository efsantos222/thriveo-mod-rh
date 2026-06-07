<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$page = $_GET['page'] ?? 'home';
$msg = $_GET['msg'] ?? '';

// Helpers
function get_companies($pdo)
{
    $stmt = $pdo->query("SELECT * FROM companies ORDER BY name");
    return $stmt->fetchAll();
}

function get_users($pdo)
{
    $stmt = $pdo->query("SELECT u.*, c.name as company_name FROM users u LEFT JOIN companies c ON u.company_id = c.id ORDER BY u.name");
    return $stmt->fetchAll();
}

function get_setting($pdo, $key)
{
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    return $stmt->fetchColumn() ?: '';
}

// Logic for Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_company') {
        $name = $_POST['name'];
        $stmt = $pdo->prepare("INSERT INTO companies (name) VALUES (?)");
        $stmt->execute([$name]);
        header("Location: dashboard.php?page=companies&msg=Empresa adicionada");
        exit;
    } elseif ($action === 'delete_company') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM companies WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: dashboard.php?page=companies&msg=Empresa removida");
        exit;
    } elseif ($action === 'add_user') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];
        $company_id = !empty($_POST['company_id']) ? $_POST['company_id'] : null;

        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, company_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $role, $company_id]);
        header("Location: dashboard.php?page=users&msg=Usuário criado");
        exit;
    } elseif ($action === 'edit_user') {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        $company_id = !empty($_POST['company_id']) ? $_POST['company_id'] : null;

        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ?, company_id = ?, password = ? WHERE id = ?");
            $stmt->execute([$name, $email, $role, $company_id, $password, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ?, company_id = ? WHERE id = ?");
            $stmt->execute([$name, $email, $role, $company_id, $id]);
        }

        header("Location: dashboard.php?page=users&msg=Usuário atualizado");
        exit;
    } elseif ($action === 'delete_user') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: dashboard.php?page=users&msg=Usuário removido");
        exit;
    } elseif ($action === 'update_settings') {
        $key = $_POST['openai_key'];
        // Check if exists
        $stmt = $pdo->prepare("SELECT id FROM settings WHERE setting_key = 'openai_api_key'");
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'openai_api_key'");
            $stmt->execute([$key]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('openai_api_key', ?)");
            $stmt->execute([$key]);
        }
        header("Location: dashboard.php?page=settings&msg=Configurações salvas");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Dashboard - Proftest IC</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: rgba(0, 0, 0, 0.2);
            border-right: 1px solid var(--glass-border);
            padding: 2rem 1rem;
        }

        .content {
            flex: 1;
            padding: 2rem;
        }

        .nav-item {
            display: block;
            padding: 1rem;
            color: var(--text-muted);
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .nav-item:hover,
        .nav-item.active {
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--glass-border);
        }

        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .form-control {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--glass-border);
            padding: 0.5rem;
            border-radius: 0.25rem;
            color: white;
            width: 100%;
            margin-bottom: 1rem;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }
    </style>
</head>

<body>

    <div class="admin-layout">
        <div class="sidebar">
            <h3 style="margin-bottom: 2rem; padding-left: 1rem;">
                <?= $_SESSION['user_role'] === 'admin' ? 'Admin Panel' : 'Painel Cliente' ?>
            </h3>
            <a href="?page=home" class="nav-item <?= $page == 'home' ? 'active' : '' ?>"><i
                    class="fa-solid fa-gauge"></i>
                Visão Geral</a>

            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <a href="?page=companies" class="nav-item <?= $page == 'companies' ? 'active' : '' ?>"><i
                        class="fa-solid fa-building"></i> Empresas</a>
                <a href="?page=users" class="nav-item <?= $page == 'users' ? 'active' : '' ?>"><i
                        class="fa-solid fa-users"></i>
                    Usuários</a>
                <a href="?page=settings" class="nav-item <?= $page == 'settings' ? 'active' : '' ?>"><i
                        class="fa-solid fa-gear"></i> Configurações</a>
            <?php endif; ?>

            <a href="logout.php" class="nav-item"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
        </div>

        <div class="content">
            <?php if ($msg): ?>
                <div
                    style="background: rgba(16, 185, 129, 0.1); color: #34d399; padding: 1rem; margin-bottom: 1rem; border-radius: 0.5rem;">
                    <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>

            <?php if ($page === 'home'): ?>
                <h1>Bem-vindo, <?= htmlspecialchars($_SESSION['user_name']) ?></h1>

                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <p style="color: var(--text-muted);">Selecione uma opção no menu lateral para gerenciar o sistema.</p>
                    <div class="grid" style="margin-top: 3rem;">
                        <div class="card" style="text-align: center;">
                            <h3><?= count(get_companies($pdo)) ?></h3>
                            <p>Empresas</p>
                        </div>
                        <div class="card" style="text-align: center;">
                            <h3><?= count(get_users($pdo)) ?></h3>
                            <p>Usuários</p>
                        </div>
                    </div>
                <?php else: ?>
                    <p style="color: var(--text-muted);">Acesse as funcionalidades de inteligência abaixo.</p>

                    <div class="grid" style="margin-top: 3rem;">
                        <div class="card">
                            <h3><i class="fa-solid fa-robot"></i> Inteligência Competitiva</h3>
                            <p>Acesse o módulo de análise via IA.</p>
                            <br>
                            <a href="intelligence.php" class="btn-login"
                                style="justify-content: center; text-decoration: none;">Iniciar Análise</a>
                        </div>
                        <div class="card">
                            <h3><i class="fa-solid fa-chart-line"></i> Meus Relatórios</h3>
                            <p>Visualize as análises salvas.</p>
                        </div>
                    </div>
                <?php endif; ?>


            <?php elseif ($page === 'companies'): ?>
                <div class="header-actions">
                    <h2>Gerenciar Empresas</h2>
                    <button onclick="document.getElementById('modal-company').style.display='block'" class="btn-login">Nova
                        Empresa</button>
                </div>

                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Data Cadastro</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (get_companies($pdo) as $comp): ?>
                            <tr>
                                <td><?= $comp['id'] ?></td>
                                <td><?= htmlspecialchars($comp['name']) ?></td>
                                <td><?= $comp['created_at'] ?></td>
                                <td>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza?');">
                                        <input type="hidden" name="action" value="delete_company">
                                        <input type="hidden" name="id" value="<?= $comp['id'] ?>">
                                        <button type="submit" class="btn-login btn-sm"
                                            style="background: #ef4444; border-color: #ef4444;">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Modal Add Company -->
                <div id="modal-company"
                    style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:999;">
                    <div
                        style="background: var(--bg-dark); max-width:500px; margin: 100px auto; padding: 2rem; border-radius: 1rem; border: 1px solid var(--glass-border);">
                        <h3>Nova Empresa</h3>
                        <form method="POST">
                            <input type="hidden" name="action" value="add_company">
                            <label>Nome da Empresa</label>
                            <input type="text" name="name" required class="form-control">
                            <div style="display:flex; justify-content: flex-end; gap: 1rem;">
                                <button type="button"
                                    onclick="document.getElementById('modal-company').style.display='none'"
                                    class="btn-login btn-outline">Cancelar</button>
                                <button type="submit" class="btn-login">Salvar</button>
                            </div>
                        </form>
                    </div>
                </div>

            <?php elseif ($page === 'users'): ?>
                <div class="header-actions">
                    <h2>Gerenciar Usuários</h2>
                    <button onclick="document.getElementById('modal-user').style.display='block'" class="btn-login">Novo
                        Usuário</button>
                </div>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Empresa</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (get_users($pdo) as $u): ?>
                            <tr>
                                <td><?= htmlspecialchars($u['name']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><?= $u['role'] ?></td>
                                <td><?= htmlspecialchars($u['company_name'] ?? '-') ?></td>
                                <td>
                                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                        <button onclick='openEditUserModal(<?= json_encode($u) ?>)' class="btn-login btn-sm"
                                            style="background: var(--primary-color); border-color: var(--primary-color);">Editar</button>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza?');">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <button type="submit" class="btn-login btn-sm"
                                                style="background: #ef4444; border-color: #ef4444;">Excluir</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Modal Add User -->
                <div id="modal-user"
                    style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:999;">
                    <div
                        style="background: var(--bg-dark); max-width:500px; margin: 100px auto; padding: 2rem; border-radius: 1rem; border: 1px solid var(--glass-border);">
                        <h3>Novo Usuário</h3>
                        <form method="POST">
                            <input type="hidden" name="action" value="add_user">
                            <label>Nome</label>
                            <input type="text" name="name" required class="form-control">
                            <label>Email</label>
                            <input type="email" name="email" required class="form-control">
                            <label>Senha</label>
                            <input type="password" name="password" required class="form-control">
                            <label>Role</label>
                            <select name="role" class="form-control">
                                <option value="user">Usuário Comum</option>
                                <option value="admin">Administrador</option>
                            </select>
                            <label>Empresa</label>
                            <select name="company_id" class="form-control">
                                <option value="">Selecione...</option>
                                <?php foreach (get_companies($pdo) as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div style="display:flex; justify-content: flex-end; gap: 1rem;">
                                <button type="button" onclick="document.getElementById('modal-user').style.display='none'"
                                    class="btn-login btn-outline">Cancelar</button>
                                <button type="submit" class="btn-login">Salvar</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal Edit User -->
                <div id="modal-edit-user"
                    style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:999;">
                    <div
                        style="background: var(--bg-dark); max-width:500px; margin: 100px auto; padding: 2rem; border-radius: 1rem; border: 1px solid var(--glass-border);">
                        <h3>Editar Usuário</h3>
                        <form method="POST">
                            <input type="hidden" name="action" value="edit_user">
                            <input type="hidden" name="id" id="edit_id">

                            <label>Nome</label>
                            <input type="text" name="name" id="edit_name" required class="form-control">

                            <label>Email</label>
                            <input type="email" name="email" id="edit_email" required class="form-control">

                            <label>Senha (deixe em branco para manter)</label>
                            <input type="password" name="password" class="form-control">

                            <label>Role</label>
                            <select name="role" id="edit_role" class="form-control">
                                <option value="user">Usuário Comum</option>
                                <option value="admin">Administrador</option>
                            </select>

                            <label>Empresa</label>
                            <select name="company_id" id="edit_company_id" class="form-control">
                                <option value="">Selecione...</option>
                                <?php foreach (get_companies($pdo) as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <div style="display:flex; justify-content: flex-end; gap: 1rem;">
                                <button type="button"
                                    onclick="document.getElementById('modal-edit-user').style.display='none'"
                                    class="btn-login btn-outline">Cancelar</button>
                                <button type="submit" class="btn-login">Atualizar</button>
                            </div>
                        </form>
                    </div>
                </div>

                <script>
                    function openEditUserModal(user) {
                        document.getElementById('edit_id').value = user.id;
                        document.getElementById('edit_name').value = user.name;
                        document.getElementById('edit_email').value = user.email;
                        document.getElementById('edit_role').value = user.role;
                        document.getElementById('edit_company_id').value = user.company_id || '';
                        document.getElementById('modal-edit-user').style.display = 'block';
                    }
                </script>

            <?php elseif ($page === 'settings'): ?>
                <h2>Configurações do Sistema</h2>
                <div class="card" style="max-width: 600px; margin-top: 2rem;">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_settings">

                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; margin-bottom: 0.5rem;">OpenAI API Key (GPT-4)</label>
                            <input type="text" name="openai_key"
                                value="<?= htmlspecialchars(get_setting($pdo, 'openai_api_key')) ?>" class="form-control">
                            <p style="font-size: 0.8rem; color: var(--text-muted);">Chave utilizada para as análises de
                                inteligência competitiva.</p>
                        </div>

                        <button type="submit" class="btn-login">Salvar Configurações</button>
                    </form>
                </div>

            <?php endif; ?>
        </div>
    </div>

</body>

</html>
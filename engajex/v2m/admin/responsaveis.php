<?php
session_start();
require_once dirname(__DIR__) . '/config.php';

if (!isAdmin()) {
    redirect('login.php');
}

$message = '';

// Handle Delete User
if (isset($_GET['delete_user'])) {
    $id = $_GET['delete_user'];
    try {
        $stmt = $pdo->prepare("DELETE FROM responsaveis WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Responsável excluído com sucesso!";
    } catch (PDOException $e) {
        $message = "Erro ao excluir: " . $e->getMessage();
    }
}

// Handle Edit User (POST method update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $id = $_POST['user_id'];
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $cargo = $_POST['cargo'];
    $empresa_id = $_POST['empresa_id'];
    $status = $_POST['status'];

    // Optional password update
    if (!empty($_POST['senha'])) {
        $senha_plana = $_POST['senha'];
        $hash = password_hash($senha_plana, PASSWORD_DEFAULT);
        $sql = "UPDATE responsaveis SET nome=?, email=?, cargo=?, id_empresa=?, status=?, senha_hash=? WHERE id=?";
        $params = [$nome, $email, $cargo, $empresa_id, $status, $hash, $id];
    } else {
        $sql = "UPDATE responsaveis SET nome=?, email=?, cargo=?, id_empresa=?, status=? WHERE id=?";
        $params = [$nome, $email, $cargo, $empresa_id, $status, $id];
    }

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $message = "Responsável atualizado com sucesso!";
    } catch (PDOException $e) {
        $message = "Erro ao atualizar: " . $e->getMessage();
    }
}

// Handle Create User
// Handle Create User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $empresa_id = $_POST['empresa_id'];
    $cargo = $_POST['cargo'];
    $senha_plana = $_POST['senha'];

    // Hash da senha informada
    $hash = password_hash($senha_plana, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO responsaveis (id_empresa, nome, email, senha_hash, cargo) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$empresa_id, $nome, $email, $hash, $cargo]);
        $message = "Responsável criado com sucesso!";
    } catch (PDOException $e) {
        $message = "Erro: " . $e->getMessage();
    }
}

// Fetch Users and Companies for select
$users = $pdo->query("SELECT r.*, e.razao_social FROM responsaveis r JOIN empresas e ON r.id_empresa = e.id ORDER BY r.nome")->fetchAll();
$companies = $pdo->query("SELECT id, razao_social FROM empresas WHERE status='ativo'")->fetchAll();

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Responsáveis - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            padding-top: 80px;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 80px;
            bottom: 0;
            width: 250px;
            background: rgba(30, 41, 59, 0.9);
            border-right: 1px solid var(--glass-border);
            padding: 20px;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid var(--glass-border);
            color: #cbd5e1;
        }

        .admin-nav li {
            margin-bottom: 10px;
        }

        .admin-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 6px;
            color: #cbd5e1;
        }

        .admin-nav a:hover,
        .admin-nav a.active {
            background: var(--primary-color);
            color: white;
        }
    </style>
</head>

<body>

    <header class="glass"
        style="position: fixed; top: 0; width: 100%; height: 80px; z-index: 50; display: flex; align-items: center; padding: 0 20px; justify-content: space-between;">
        <div class="logo"><i class="ph ph-strategy"></i> V2MOM ADMIN</div>
        <a href="logout.php" class="btn btn-outline" style="padding: 5px 15px;">Sair</a>
    </header>

    <aside class="sidebar">
        <ul class="admin-nav">
            <li><a href="dashboard.php"><i class="ph ph-squares-four"></i> Dashboard</a></li>
            <li><a href="empresas.php"><i class="ph ph-buildings"></i> Empresas</a></li>
            <li><a href="responsaveis.php" class="active"><i class="ph ph-users"></i> Responsáveis</a></li>
            <li><a href="config_ia.php"><i class="ph ph-robot"></i> Configuração IA</a></li>
            <li><a href="logs.php"><i class="ph ph-scroll"></i> Logs & Auditoria</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h1>Gestão de Responsáveis</h1>

        <?php if ($message): ?>
            <div
                style="padding: 15px; background: rgba(16, 185, 129, 0.2); color: #6ee7b7; border-radius: 6px; margin: 20px 0;">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <div class="glass" style="padding: 20px; margin-top: 20px;">
            <h3>Novo Responsável</h3>
            <form method="POST"
                style="margin-top: 15px; display: grid; gap: 15px; grid-template-columns: repeat(2, 1fr);">
                <input type="hidden" name="create_user" value="1">

                <select name="empresa_id" required
                    style="padding: 10px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px;">
                    <option value="">Selecione a Empresa</option>
                    <?php foreach ($companies as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['razao_social']) ?></option>
                    <?php endforeach; ?>
                </select>

                <input type="text" name="nome" placeholder="Nome Completo" required
                    style="padding: 10px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px;">
                <input type="email" name="email" placeholder="E-mail Corporativo" required
                    style="padding: 10px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px;">
                <input type="text" name="cargo" placeholder="Cargo" required
                    style="padding: 10px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px;">

                <input type="password" name="senha" placeholder="Senha de Acesso" required
                    style="padding: 10px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px;">

                <div style="grid-column: span 2;">
                    <button type="submit" class="btn btn-primary">Criar Responsável</button>
                </div>
            </form>
        </div>

        <div class="glass" style="padding: 20px; margin-top: 30px;">
            <h3>Responsáveis Ativos</h3>
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Empresa</th>
                        <th>Cargo</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['nome']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars($u['razao_social']) ?></td>
                            <td><?= htmlspecialchars($u['cargo']) ?></td>
                            <td>
                                <span
                                    style="padding: 2px 8px; border-radius: 10px; background: <?= $u['status'] == 'ativo' ? 'rgba(16,185,129,0.2)' : 'rgba(239,68,68,0.2)' ?>; color: <?= $u['status'] == 'ativo' ? '#6ee7b7' : '#fca5a5' ?>">
                                    <?= ucfirst($u['status']) ?>
                                </span>
                            </td>
                            <td>
                                <button onclick='openEditModal(<?= json_encode($u) ?>)' class="btn btn-outline" style="padding: 5px 10px; font-size: 0.8rem; margin-right: 5px;">
                                    <i class="ph ph-pencil"></i>
                                </button>
                                <a href="?delete_user=<?= $u['id'] ?>" onclick="return confirm('Tem certeza que deseja excluir este responsável?');" class="btn btn-outline" style="padding: 5px 10px; font-size: 0.8rem; border-color: var(--danger-color); color: var(--danger-color);">
                                    <i class="ph ph-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    </main>

    <!-- Modal de Edição -->
    <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 100; align-items: center; justify-content: center;">
        <div class="glass" style="width: 100%; max-width: 600px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <h3>Editar Responsável</h3>
                <button onclick="closeEditModal()" style="background: none; border: none; color: white; cursor: pointer; font-size: 1.2rem;"><i class="ph ph-x"></i></button>
            </div>
            
            <form method="POST" style="display: grid; gap: 15px; grid-template-columns: repeat(2, 1fr);">
                <input type="hidden" name="edit_user" value="1">
                <input type="hidden" name="user_id" id="edit_id">

                <div style="grid-column: span 2;">
                    <label>Empresa</label>
                    <select name="empresa_id" id="edit_empresa" required style="width: 100%; padding: 10px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px;">
                        <?php foreach($companies as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['razao_social']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>Nome</label>
                    <input type="text" name="nome" id="edit_nome" required style="width: 100%; padding: 10px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px;">
                </div>
                
                <div>
                   <label>E-mail</label>
                   <input type="email" name="email" id="edit_email" required style="width: 100%; padding: 10px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px;">
                </div>

                <div>
                    <label>Cargo</label>
                    <input type="text" name="cargo" id="edit_cargo" required style="width: 100%; padding: 10px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px;">
                </div>

                <div>
                    <label>Status</label>
                    <select name="status" id="edit_status" required style="width: 100%; padding: 10px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px;">
                        <option value="ativo">Ativo</option>
                        <option value="inativo">Inativo</option>
                    </select>
                </div>
                
                <div style="grid-column: span 2;">
                    <label>Nova Senha (deixe em branco para não alterar)</label>
                    <input type="password" name="senha" placeholder="********" style="width: 100%; padding: 10px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px;">
                </div>

                <div style="grid-column: span 2; margin-top: 10px;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(user) {
            document.getElementById('editModal').style.display = 'flex';
            document.getElementById('edit_id').value = user.id;
            document.getElementById('edit_empresa').value = user.id_empresa;
            document.getElementById('edit_nome').value = user.nome;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_cargo').value = user.cargo;
            document.getElementById('edit_status').value = user.status;
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
    </script>
</body>

</html>
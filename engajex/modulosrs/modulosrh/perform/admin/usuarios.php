<?php
session_start();
require_once '../config.php';

// Verificar se está logado e é administrador
if (!checkSession() || getUserRole() !== 'administrador') {
    header('Location: ../login.php');
    exit();
}

$user = $_SESSION[SESSION_NAME];
$message = '';
$error = '';

try {
    $pdo = getConnection();
    
    // Processar ações POST
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'create':
                    $nome = sanitize($_POST['nome']);
                    $email = sanitize($_POST['email']);
                    $senha = $_POST['senha'];
                    $role = sanitize($_POST['role']);
                    $cpf = sanitize($_POST['cpf']);
                    $telefone = sanitize($_POST['telefone']);
                    
                    // Verificar se email já existe
                    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
                    $stmt->execute([$email]);
                    if ($stmt->fetch()) {
                        $error = 'Email já cadastrado no sistema.';
                    } else {
                        $stmt = $pdo->prepare("
                            INSERT INTO usuarios (nome, email, senha, role, cpf, telefone, ativo) 
                            VALUES (?, ?, ?, ?, ?, ?, 1)
                        ");
                        $stmt->execute([
                            $nome, 
                            $email, 
                            hashPassword($senha), 
                            $role, 
                            $cpf, 
                            $telefone
                        ]);
                        
                        // Log de atividade
                        $stmt = $pdo->prepare("INSERT INTO logs_atividades (usuario_id, acao, tabela_afetada, registro_id, ip_address) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$user['id'], 'criar_usuario', 'usuarios', $pdo->lastInsertId(), $_SERVER['REMOTE_ADDR']]);
                        
                        $message = 'Usuário criado com sucesso!';
                    }
                    break;
                    
                case 'edit':
                    $id = (int)$_POST['id'];
                    $nome = sanitize($_POST['nome']);
                    $email = sanitize($_POST['email']);
                    $role = sanitize($_POST['role']);
                    $cpf = sanitize($_POST['cpf']);
                    $telefone = sanitize($_POST['telefone']);
                    $ativo = isset($_POST['ativo']) ? 1 : 0;
                    
                    $sql = "UPDATE usuarios SET nome = ?, email = ?, role = ?, cpf = ?, telefone = ?, ativo = ?";
                    $params = [$nome, $email, $role, $cpf, $telefone, $ativo];
                    
                    // Se uma nova senha foi fornecida
                    if (!empty($_POST['senha'])) {
                        $sql .= ", senha = ?";
                        $params[] = hashPassword($_POST['senha']);
                    }
                    
                    $sql .= " WHERE id = ?";
                    $params[] = $id;
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    
                    // Log de atividade
                    $stmt = $pdo->prepare("INSERT INTO logs_atividades (usuario_id, acao, tabela_afetada, registro_id, ip_address) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$user['id'], 'editar_usuario', 'usuarios', $id, $_SERVER['REMOTE_ADDR']]);
                    
                    $message = 'Usuário atualizado com sucesso!';
                    break;
                    
                case 'delete':
                    $id = (int)$_POST['id'];
                    
                    // Não permitir excluir o próprio usuário
                    if ($id == $user['id']) {
                        $error = 'Você não pode excluir seu próprio usuário.';
                    } else {
                        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
                        $stmt->execute([$id]);
                        
                        // Log de atividade
                        $stmt = $pdo->prepare("INSERT INTO logs_atividades (usuario_id, acao, tabela_afetada, registro_id, ip_address) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$user['id'], 'excluir_usuario', 'usuarios', $id, $_SERVER['REMOTE_ADDR']]);
                        
                        $message = 'Usuário excluído com sucesso!';
                    }
                    break;
                    
                case 'toggle_status':
                    $id = (int)$_POST['id'];
                    
                    $stmt = $pdo->prepare("UPDATE usuarios SET ativo = NOT ativo WHERE id = ?");
                    $stmt->execute([$id]);
                    
                    $message = 'Status do usuário atualizado!';
                    break;
            }
        }
    }
    
    // Buscar usuários
    $search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
    $role_filter = isset($_GET['role']) ? sanitize($_GET['role']) : '';
    
    $sql = "SELECT * FROM usuarios WHERE 1=1";
    $params = [];
    
    if ($search) {
        $sql .= " AND (nome LIKE ? OR email LIKE ? OR cpf LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if ($role_filter) {
        $sql .= " AND role = ?";
        $params[] = $role_filter;
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $usuarios = $stmt->fetchAll();
    
} catch(PDOException $e) {
    error_log($e->getMessage());
    $error = 'Erro ao processar operação.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários - <?php echo SYSTEM_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="sidebar-logo">
                    <span>ProfTest Admin</span>
                </a>
            </div>
            
            <nav class="sidebar-menu">
                <div class="menu-title">Principal</div>
                <a href="dashboard.php" class="menu-item">
                    <span>Dashboard</span>
                </a>
                
                <div class="menu-title">Gestão</div>
                <a href="usuarios.php" class="menu-item active">
                    <span>Usuários</span>
                </a>
                <a href="testes.php" class="menu-item">
                    <span>Testes</span>
                </a>
                <a href="categorias.php" class="menu-item">
                    <span>Categorias</span>
                </a>
                <a href="aplicacoes.php" class="menu-item">
                    <span>Aplicações</span>
                </a>
                
                <div class="menu-title">Relatórios</div>
                <a href="relatorios.php" class="menu-item">
                    <span>Relatórios</span>
                </a>
                <a href="logs.php" class="menu-item">
                    <span>Logs do Sistema</span>
                </a>
                
                <div class="menu-title">Sistema</div>
                <a href="configuracoes.php" class="menu-item">
                    <span>Configurações</span>
                </a>
                <a href="../logout.php" class="menu-item">
                    <span>Sair</span>
                </a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <h1 class="header-title">Gerenciar Usuários</h1>
                <div class="header-actions">
                    <button onclick="openModal('createModal')" class="btn btn-primary">
                        + Novo Usuário
                    </button>
                </div>
            </header>
            
            <!-- Content -->
            <div class="content">
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <!-- Filtros -->
                <div class="card mb-3">
                    <div class="card-body">
                        <form method="GET" class="d-flex gap-2 align-items-center">
                            <input type="text" name="search" placeholder="Buscar por nome, email ou CPF..." 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   style="flex: 1; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border-color);">
                            
                            <select name="role" style="padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border-color);">
                                <option value="">Todos os papéis</option>
                                <option value="administrador" <?php echo $role_filter == 'administrador' ? 'selected' : ''; ?>>Administradores</option>
                                <option value="aplicador" <?php echo $role_filter == 'aplicador' ? 'selected' : ''; ?>>Aplicadores</option>
                                <option value="candidato" <?php echo $role_filter == 'candidato' ? 'selected' : ''; ?>>Candidatos</option>
                            </select>
                            
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                            <a href="usuarios.php" class="btn btn-outline">Limpar</a>
                        </form>
                    </div>
                </div>
                
                <!-- Tabela de Usuários -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>CPF</th>
                                        <th>Papel</th>
                                        <th>Status</th>
                                        <th>Criado em</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td>#<?php echo $usuario['id']; ?></td>
                                        <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['cpf'] ?? '-'); ?></td>
                                        <td>
                                            <span class="badge badge-primary">
                                                <?php echo ucfirst($usuario['role']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $usuario['ativo'] ? 'badge-success' : 'badge-danger'; ?>">
                                                <?php echo $usuario['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($usuario['created_at'])); ?></td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <button onclick="editUser(<?php echo htmlspecialchars(json_encode($usuario)); ?>)" 
                                                        class="btn btn-sm btn-outline">Editar</button>
                                                
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($usuarios)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">Nenhum usuário encontrado</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Modal Criar Usuário -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Novo Usuário</h3>
                <button onclick="closeModal('createModal')" class="modal-close">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nome Completo *</label>
                        <input type="text" name="nome" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Senha *</label>
                        <input type="password" name="senha" required minlength="6">
                    </div>
                    
                    <div class="form-group">
                        <label>Papel *</label>
                        <select name="role" required>
                            <option value="">Selecione...</option>
                            <option value="administrador">Administrador</option>
                            <option value="aplicador">Aplicador de Teste</option>
                            <option value="candidato">Candidato</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>CPF</label>
                        <input type="text" name="cpf" maxlength="14">
                    </div>
                    
                    <div class="form-group">
                        <label>Telefone</label>
                        <input type="text" name="telefone" maxlength="15">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closeModal('createModal')" class="btn btn-outline">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Criar Usuário</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Editar Usuário -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Editar Usuário</h3>
                <button onclick="closeModal('editModal')" class="modal-close">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nome Completo *</label>
                        <input type="text" name="nome" id="edit_nome" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" id="edit_email" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Nova Senha (deixe em branco para manter a atual)</label>
                        <input type="password" name="senha" minlength="6">
                    </div>
                    
                    <div class="form-group">
                        <label>Papel *</label>
                        <select name="role" id="edit_role" required>
                            <option value="administrador">Administrador</option>
                            <option value="aplicador">Aplicador de Teste</option>
                            <option value="candidato">Candidato</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>CPF</label>
                        <input type="text" name="cpf" id="edit_cpf" maxlength="14">
                    </div>
                    
                    <div class="form-group">
                        <label>Telefone</label>
                        <input type="text" name="telefone" id="edit_telefone" maxlength="15">
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="ativo" id="edit_ativo">
                            <span>Usuário Ativo</span>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closeModal('editModal')" class="btn btn-outline">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }
        
        function editUser(user) {
            document.getElementById('edit_id').value = user.id;
            document.getElementById('edit_nome').value = user.nome;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_role').value = user.role;
            document.getElementById('edit_cpf').value = user.cpf || '';
            document.getElementById('edit_telefone').value = user.telefone || '';
            document.getElementById('edit_ativo').checked = user.ativo == 1;
            openModal('editModal');
        }
    </script>
</body>
</html>

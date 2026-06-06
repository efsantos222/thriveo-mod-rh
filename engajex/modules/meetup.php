<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config.php';
require_once '../includes/flash_toast.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$companyId = $_SESSION['company_id'] ?? 1;

$userRole = $_SESSION['role'];
$canManage = ($userRole === 'admin' || $userRole === 'responsible' || $userRole === 'manager');
$mode = $_GET['mode'] ?? 'presentation'; // presentation, manage, my_info

// Force users to 'my_info' first if they have a pending entry?? 
// Let's decide mode based on role default, but allow override
if ($canManage && !isset($_GET['mode']))
    $mode = 'presentation';
if (!$canManage && !isset($_GET['mode']))
    $mode = 'my_info';

$message = '';

// --- ACTIONS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. ADMIN: ADD NEW HIRE WITH PHOTO
    if ($canManage && $_POST['action'] === 'add_hire') {
        $targetUserId = $_POST['user_id'];
        $roleName = $_POST['role_name'];
        $photoUrl = null;

        // Handle File Upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($ext, $allowed)) {
                $filename = 'meetup_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                $dest = '../uploads/meetup/' . $filename;

                if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
                    $photoUrl = 'uploads/meetup/' . $filename;
                }
            }
        }

        try {
            $pdo->prepare("INSERT INTO meetup_entries (company_id, user_id, role_name, photo_url, status) VALUES (?, ?, ?, ?, 'pending')")
                ->execute([$companyId, $targetUserId, $roleName, $photoUrl]);
            $message = "Novo colaborador cadastrado com foto!";
        } catch (PDOException $e) {
            $message = "Erro: Usuário já cadastrado.";
        }
    }

    // 2. USER: UPDATE INFO
    if ($_POST['action'] === 'update_info') {
        $manager = $_POST['manager'];
        $client = $_POST['client'];
        $projects = $_POST['projects'];
        $education = $_POST['education'];
        $city = $_POST['city'];
        $hobby = $_POST['hobby'];

        $pdo->prepare("UPDATE meetup_entries SET manager=?, client=?, projects=?, education=?, city=?, hobby=?, status='completed' WHERE user_id=? AND company_id=?")
            ->execute([$manager, $client, $projects, $education, $city, $hobby, $userId, $companyId]);

        $message = "Suas informações foram salvas com sucesso! Você está pronto para o Meetup.";
    }

    // 3. ADMIN: REMOVE
    if ($canManage && $_POST['action'] === 'delete_entry') {
        $pdo->prepare("DELETE FROM meetup_entries WHERE id = ?")->execute([$_POST['id']]);
        $message = "Registro removido.";
    }
}

// --- FETCH DATA ---

// 1. For Presentation (Grouped by Client)
$groups = [];
if ($mode === 'presentation') {
    $stmt = $pdo->prepare("
        SELECT m.*, u.name as user_name, u.email 
        FROM meetup_entries m 
        JOIN users u ON m.user_id = u.id 
        WHERE m.company_id = ? AND m.status != 'pending'
        ORDER BY m.client, u.name
    ");
    $stmt->execute([$companyId]);
    $entries = $stmt->fetchAll();

    foreach ($entries as $e) {
        $clientKey = $e['client'] ?: 'Sem Cliente Definido';
        $groups[$clientKey][] = $e;
    }
}

// 2. For Management List
$allEntries = [];
if ($canManage && $mode === 'manage') {
    $stmt = $pdo->prepare("SELECT m.*, u.name as user_name FROM meetup_entries m JOIN users u ON m.user_id = u.id WHERE m.company_id = ? ORDER BY m.created_at DESC");
    $stmt->execute([$companyId]);
    $allEntries = $stmt->fetchAll();

    // Fetch potential users to add (users not in meetup)
    $availUsers = $pdo->prepare("
        SELECT id, name FROM users 
        WHERE company_id = ? 
        AND id NOT IN (SELECT user_id FROM meetup_entries WHERE company_id = ?)
        ORDER BY name
    ");
    $availUsers->execute([$companyId, $companyId]);
    $candidates = $availUsers->fetchAll();
}

// 3. For My Info
$myEntry = null;
if ($mode === 'my_info') {
    $stmt = $pdo->prepare("SELECT * FROM meetup_entries WHERE user_id = ? AND company_id = ?");
    $stmt->execute([$userId, $companyId]);
    $myEntry = $stmt->fetch();
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Meetup - Novos Colaboradores</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .app-layout {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .main-content {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
            background: var(--bg-color);
        }

        /* TABS */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--glass-border);
            padding-bottom: 1rem;
        }

        .mode-tabs {
            display: flex;
            gap: 1rem;
        }

        .mode-link {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 500;
            transition: all 0.2s;
        }

        .mode-link:hover {
            background: rgba(255, 255, 255, 0.05);
            color: white;
        }

        .mode-link.active {
            background: var(--primary-color);
            color: white;
        }

        /* PRESENTATION CARDS */
        .client-section {
            margin-bottom: 3rem;
        }

        .client-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: #f97316;
            margin-bottom: 1rem;
            border-left: 5px solid #f97316;
            padding-left: 1rem;
            line-height: 1.2;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }

        .profile-card {
            background: rgba(30, 41, 59, 0.7);
            /* Dark Glass */
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 1.5rem;
            overflow: hidden;
            transition: transform 0.3s;
            position: relative;
        }

        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .card-header-bg {
            height: 80px;
            background: linear-gradient(135deg, #6366f1, #a855f7);
        }

        .avatar-container {
            width: 100px;
            height: 100px;
            background: #fff;
            border-radius: 50%;
            border: 4px solid var(--bg-color);
            position: absolute;
            top: 30px;
            left: 20px;
            display: grid;
            place-items: center;
            font-size: 2rem;
            font-weight: 800;
            color: #4f46e5;
            overflow: hidden;
        }

        .card-body {
            padding: 3.5rem 1.5rem 1.5rem 1.5rem;
        }

        .p-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.25rem;
        }

        .p-role {
            color: #f97316;
            font-weight: 600;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.75rem;
            font-size: 0.9rem;
        }

        .info-item {
            display: flex;
            gap: 0.5rem;
        }

        .info-label {
            color: var(--text-muted);
            min-width: 80px;
        }

        .info-val {
            color: #e2e8f0;
            font-weight: 500;
        }

        .hobby-pill {
            display: inline-block;
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.8rem;
            margin-top: 1rem;
        }

        /* FORM STYLES */
        .form-card {
            max-width: 600px;
            margin: 0 auto;
            background: var(--card-bg);
            padding: 2rem;
            border-radius: 1rem;
            border: 1px solid var(--glass-border);
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--glass-border);
            border-radius: 0.5rem;
            color: white;
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include '../includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <div>
                    <h1 style="font-size: 1.8rem;">Meetup: Novos Talentos</h1>
                    <p style="color: var(--text-muted);">Apresentação dos novos integrantes da equipe.</p>
                </div>

                <div class="mode-tabs">
                    <?php if ($canManage): ?>
                        <a href="?mode=presentation"
                            class="mode-link <?php echo $mode === 'presentation' ? 'active' : ''; ?>">📺
                            Apresentação</a>
                        <a href="?mode=manage" class="mode-link <?php echo $mode === 'manage' ? 'active' : ''; ?>">⚙️ Gestão
                            (RH)</a>
                    <?php endif; ?>
                    <a href="?mode=my_info" class="mode-link <?php echo $mode === 'my_info' ? 'active' : ''; ?>">📝 Meus
                        Dados</a>
                </div>
            </div>

            <?php if ($message): flashToast($message, 'success'); endif; ?>

            <!-- ================= PRESENTATION VIEW ================= -->
            <?php if ($mode === 'presentation'): ?>
                <?php if (empty($groups)): ?>
                    <div style="text-align: center; padding: 4rem; color: var(--text-muted);">
                        <h3>Nenhum novo colaborador pronto para apresentação.</h3>
                        <p>Aguarde o preenchimento dos dados pelos novos integrantes.</p>
                    </div>
                <?php endif; ?>

                <?php foreach ($groups as $client => $users): ?>
                    <div class="client-section">
                        <div class="client-title"><?php echo htmlspecialchars($client); ?></div>
                        <div class="cards-grid">
                            <?php foreach ($users as $u): ?>
                                <div class="profile-card">
                                    <div class="card-header-bg"></div>
                                    <div class="avatar-container">
                                        <?php if (!empty($u['photo_url'])): ?>
                                            <img src="../<?php echo htmlspecialchars($u['photo_url']); ?>"
                                                style="width:100%; height:100%; object-fit:cover;">
                                        <?php else: ?>
                                            <?php echo strtoupper(substr($u['user_name'], 0, 2)); ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body">
                                        <div class="p-name"><?php echo htmlspecialchars($u['user_name']); ?></div>
                                        <div class="p-role"><?php echo htmlspecialchars($u['role_name']); ?></div>

                                        <div class="info-grid">
                                            <div class="info-item">
                                                <span class="info-label">Gerente:</span>
                                                <span class="info-val"><?php echo htmlspecialchars($u['manager']); ?></span>
                                            </div>
                                            <div class="info-item">
                                                <span class="info-label">Cidade:</span>
                                                <span class="info-val"><?php echo htmlspecialchars($u['city']); ?></span>
                                            </div>
                                            <div class="info-item">
                                                <span class="info-label">Formação:</span>
                                                <span class="info-val"><?php echo htmlspecialchars($u['education']); ?></span>
                                            </div>
                                            <div class="info-item" style="flex-direction: column; gap: 0.2rem; margin-top: 0.5rem;">
                                                <span class="info-label">Projetos/Atividades:</span>
                                                <span class="info-val"
                                                    style="font-size: 0.85rem; opacity: 0.8; line-height: 1.4;"><?php echo nl2br(htmlspecialchars($u['projects'])); ?></span>
                                            </div>

                                            <div class="hobby-pill">❤️ <?php echo htmlspecialchars($u['hobby']); ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- ================= MANAGEMENT VIEW ================= -->
            <?php if ($mode === 'manage' && $canManage): ?>
                <div class="cards-grid" style="grid-template-columns: 1fr 2fr; align-items: start;">

                    <!-- ADD FORM -->
                    <div class="form-card" style="width: 100%;">
                        <h3>Cadastrar Novo Colaborador</h3>
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="add_hire">
                            <div class="form-group">
                                <label class="form-label">Selecione o Usuário</label>
                                <select name="user_id" class="form-control" required style="background: #1e293b;">
                                    <option value="">-- Selecione --</option>
                                    <?php foreach ($candidates as $c): ?>
                                        <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Cargo (Role)</label>
                                <input type="text" name="role_name" class="form-control" placeholder="Ex: Desenvolvedor Jr"
                                    required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Foto de Rosto (Opcional)</label>
                                <input type="file" name="photo" class="form-control" accept="image/*">
                            </div>
                            <button class="btn btn-primary" style="width: 100%;">Cadastrar</button>
                        </form>
                    </div>

                    <!-- LIST -->
                    <div class="card" style="padding: 1.5rem; background: var(--card-bg);">
                        <h3>Lista de Novos (Meetup)</h3>
                        <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
                            <thead>
                                <tr
                                    style="border-bottom: 1px solid var(--glass-border); text-align: left; color: var(--text-muted); font-size: 0.9rem;">
                                    <th style="padding: 0.5rem;">Nome</th>
                                    <th style="padding: 0.5rem;">Cargo</th>
                                    <th style="padding: 0.5rem;">Status</th>
                                    <th style="padding: 0.5rem;">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allEntries as $e): ?>
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                        <td style="padding: 0.75rem;"><?php echo htmlspecialchars($e['user_name']); ?></td>
                                        <td style="padding: 0.75rem;"><?php echo htmlspecialchars($e['role_name']); ?></td>
                                        <td style="padding: 0.75rem;">
                                            <?php if ($e['status'] === 'pending'): ?>
                                                <span style="color: #fbbf24; font-size: 0.8rem;">⏳ Pendente</span>
                                            <?php else: ?>
                                                <span style="color: #34d399; font-size: 0.8rem;">✅ Pronto</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 0.75rem;">
                                            <form method="POST" onsubmit="return confirm('Remover da lista?');"
                                                style="margin:0;">
                                                <input type="hidden" name="action" value="delete_entry">
                                                <input type="hidden" name="id" value="<?php echo $e['id']; ?>">
                                                <button
                                                    style="background:none; border:none; color: #ef4444; cursor: pointer;">🗑</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ================= MY INFO VIEW ================= -->
            <?php if ($mode === 'my_info'): ?>
                <?php if (!$myEntry): ?>
                    <div style="text-align: center; padding: 4rem;">
                        <h2>Você não está listado para o próximo Meetup.</h2>
                        <p style="color: var(--text-muted);">Se você é um novo colaborador, solicite ao RH para te incluir na
                            lista.</p>
                    </div>
                <?php else: ?>
                    <div class="form-card">
                        <h2 style="margin-bottom: 0.5rem;">Complete seu Perfil</h2>
                        <p style="color: var(--text-muted); margin-bottom: 2rem;">Essas informações serão usadas no card de
                            apresentação.</p>

                        <form method="POST">
                            <input type="hidden" name="action" value="update_info">

                            <div class="form-group">
                                <label class="form-label">Meu Cargo (Definido pelo RH)</label>
                                <input type="text" value="<?php echo htmlspecialchars($myEntry['role_name']); ?>"
                                    class="form-control" disabled style="opacity: 0.7;">
                            </div>

                            <div class="game-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label class="form-label">Gerente / Líder *</label>
                                    <input type="text" name="manager" class="form-control"
                                        value="<?php echo htmlspecialchars($myEntry['manager'] ?? ''); ?>" required
                                        placeholder="Quem é seu gestor?">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Cliente / Área *</label>
                                    <input type="text" name="client" class="form-control"
                                        value="<?php echo htmlspecialchars($myEntry['client'] ?? ''); ?>" required
                                        placeholder="Em qual cliente você atua?">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Formação Acadêmica</label>
                                <input type="text" name="education" class="form-control"
                                    value="<?php echo htmlspecialchars($myEntry['education'] ?? ''); ?>"
                                    placeholder="Ex: Engenharia de Software - USP">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Cidade de Residência</label>
                                <input type="text" name="city" class="form-control"
                                    value="<?php echo htmlspecialchars($myEntry['city'] ?? ''); ?>" placeholder="Cidade/UF">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Hobby Principal</label>
                                <input type="text" name="hobby" class="form-control"
                                    value="<?php echo htmlspecialchars($myEntry['hobby'] ?? ''); ?>"
                                    placeholder="O que você ama fazer fora do trabalho?">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Projetos / Atividades Atuais</label>
                                <textarea name="projects" class="form-control" rows="3"
                                    placeholder="Descreva brevemente em que está atuando..."><?php echo htmlspecialchars($myEntry['projects'] ?? ''); ?></textarea>
                            </div>

                            <button class="btn btn-primary" style="width: 100%; margin-top: 1rem;">💾 Salvar
                                Informações</button>
                        </form>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

        </main>
    </div>
</body>

</html>
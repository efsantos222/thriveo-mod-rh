<?php
require_once 'config.php';

// Check permissions
if (!isLoggedIn() || !in_array($_SESSION['role'], ['responsible', 'company_admin', 'admin'])) {
    header("Location: login.php");
    exit;
}

$message = '';
$error = '';

// Get current user's company ID
if ($_SESSION['role'] === 'responsible' || $_SESSION['role'] === 'company_admin') {
    $currentUserId = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT company_id FROM users WHERE id = ?");
    $stmt->execute([$currentUserId]);
    $companyId = $stmt->fetchColumn();
} else {
    // Admin handling (optional context, assume session company_id if set, or redirect)
    // For simplicity, this module is primarily for the 'Responsável'.
    // If admin, maybe we need to select company first. But I'll stick to Responsible context as requested.
    $companyId = $_SESSION['company_id'] ?? null;
}

// Handle Form Submission (Add/Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = $_POST['user_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND company_id = ?");
            $stmt->execute([$id, $companyId]);
            $message = "Usuário excluído com sucesso.";
        } catch (PDOException $e) {
            $error = "Erro ao excluir: " . $e->getMessage();
        }
    } else {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $matricula = $_POST['matricula'];
        $cargo = $_POST['cargo'];
        $area = $_POST['area'];
        $role = $_POST['role']; // manager or employee

        // Password handling
        $password = $_POST['password'];
        $hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $error = "E-mail já cadastrado no sistema.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (company_id, registration_number, name, email, password, role, cargo, area) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$companyId, $matricula, $name, $email, $hash, $role, $cargo, $area]);
                $message = "Usuário cadastrado com sucesso! Senha temporária é a matrícula.";
            }
        } catch (PDOException $e) {
            $error = "Erro no banco de dados: " . $e->getMessage();
        }
    }
}

// Handle CSV Import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'import_csv') {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, "r");

        $successCount = 0;
        $errorCount = 0;
        $row = 0;

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $row++;
            // Skip header if it looks like header (check if first col is 'Matricula' or similar)
            if ($row === 1 && (strtolower($data[0]) === 'matricula' || strtolower($data[0]) === 'matrícula')) {
                continue;
            }

            // Expected columns: 0:Matricula, 1:Nome, 2:Email, 3:Senha, 4:Tipo(manager/employee), 5:Cargo, 6:Area
            if (count($data) < 7) {
                // Try guessing if basic layout
                // Let's assume strict format for simplicity and warn user
                $errorCount++; // Invalid format
                continue;
            }

            $matricula = trim($data[0]);
            $name = trim($data[1]);
            $email = trim($data[2]);
            $password = trim($data[3]);
            $role = strtolower(trim($data[4])); // 'manager' or 'employee'
            $cargo = trim($data[5]);
            $area = trim($data[6]);

            // Normalise Type
            if ($role !== 'manager')
                $role = 'employee';

            // Default password if empty -> matricula
            if (empty($password))
                $password = $matricula;
            $hash = password_hash($password, PASSWORD_DEFAULT);

            try {
                // Check dupes
                $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $check->execute([$email]);
                if ($check->rowCount() == 0) {
                    $stmt = $pdo->prepare("INSERT INTO users (company_id, registration_number, name, email, password, role, cargo, area) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$companyId, $matricula, $name, $email, $hash, $role, $cargo, $area]);
                    $successCount++;
                } else {
                    $errorCount++; // Email exists
                }
            } catch (Exception $e) {
                $errorCount++;
            }
        }
        fclose($handle);
        $message = "Importação concluída: $successCount importados com sucesso. $errorCount ignorados (duplicados/erro).";
    } else {
        $error = "Erro no upload do arquivo.";
    }
}

// Fetch Lists
$employees = [];
$managers = [];
$admins = [];

if ($companyId) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE company_id = ? AND role = 'employee' ORDER BY name");
    $stmt->execute([$companyId]);
    $employees = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT * FROM users WHERE company_id = ? AND role = 'manager' ORDER BY name");
    $stmt->execute([$companyId]);
    $managers = $stmt->fetchAll();

    // company_admin vê também responsáveis e outros company_admins da empresa
    if (in_array($_SESSION['role'], ['company_admin', 'admin'])) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE company_id = ? AND role IN ('responsible', 'company_admin') ORDER BY name");
        $stmt->execute([$companyId]);
        $admins = $stmt->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Gestão de Pessoas - TestProf Engaja</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
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

        .tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--glass-border);
        }

        .tab-btn {
            padding: 0.75rem 1.5rem;
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-weight: 600;
            position: relative;
        }

        .tab-btn.active {
            color: var(--primary-color);
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--primary-color);
        }

        .table-container {
            background: var(--card-bg);
            border-radius: 0.5rem;
            border: 1px solid var(--glass-border);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--glass-border);
            font-size: 0.9rem;
        }

        th {
            background: var(--slate-50);
            color: var(--text-muted);
            font-weight: 600;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal.open {
            display: flex;
        }

        .modal-content {
            background: #ffffff;
            border: 1px solid var(--slate-200);
            padding: 2rem;
            border-radius: 1rem;
            width: 100%;
            max-width: 500px;
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1>Gestão de Pessoas</h1>
                <div>
                    <button onclick="openImportModal()" class="btn btn-outline"
                        style="margin-right: 0.5rem; border-color: var(--emerald-600); color: var(--emerald-600);">📥 Importar Excel
                        (.csv)</button>
                    <button onclick="openModal()" class="btn btn-primary">+ Novo Cadastro</button>
                </div>
            </div>

            <?php if ($message): ?>
                <div
                    style="background: #f0fdf4; color: #15803d; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; border: 1px solid rgba(16, 185, 129, 0.3);">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div
                    style="background: #fef2f2; color: #b91c1c; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; border: 1px solid rgba(239, 68, 68, 0.3);">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="tabs">
                <?php if (in_array($_SESSION['role'], ['company_admin', 'admin'])): ?>
                <button class="tab-btn active" onclick="switchTab('admins')">Administradores</button>
                <button class="tab-btn" onclick="switchTab('managers')">Gerentes</button>
                <button class="tab-btn" onclick="switchTab('employees')">Colaboradores</button>
                <?php else: ?>
                <button class="tab-btn active" onclick="switchTab('managers')">Gerentes</button>
                <button class="tab-btn" onclick="switchTab('employees')">Colaboradores</button>
                <?php endif; ?>
            </div>

            <?php if (in_array($_SESSION['role'], ['company_admin', 'admin'])): ?>
            <!-- Admins Table -->
            <div id="tab-admins" class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Perfil</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($admins)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 2rem;">Nenhum administrador cadastrado.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($admins as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo $user['role'] === 'company_admin' ? 'Admin da Empresa' : 'Responsável'; ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" style="background:none; border:none; color: var(--red-600); cursor:pointer;">Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- Managers Table -->
            <div id="tab-managers" class="table-container" <?php if (in_array($_SESSION['role'], ['company_admin', 'admin'])): ?>style="display:none;"<?php endif; ?>>
                <table>
                    <thead>
                        <tr>
                            <th>Matrícula</th>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Cargo</th>
                            <th>Área</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($managers)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">Nenhum
                                    gerente cadastrado.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($managers as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['registration_number']); ?></td>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['cargo']); ?></td>
                                    <td><?php echo htmlspecialchars($user['area']); ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit"
                                                style="background:none; border:none; color: var(--red-600); cursor:pointer;">Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Employees Table -->
            <div id="tab-employees" class="table-container" style="display: none;">
                <table>
                    <thead>
                        <tr>
                            <th>Matrícula</th>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Cargo</th>
                            <th>Área</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($employees)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">Nenhum
                                    colaborador cadastrado.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($employees as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['registration_number']); ?></td>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['cargo']); ?></td>
                                    <td><?php echo htmlspecialchars($user['area']); ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit"
                                                style="background:none; border:none; color: var(--red-600); cursor:pointer;">Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>

    <!-- Modal Form -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <h2 style="margin-bottom: 1.5rem;">Novo Cadastro</h2>
            <form method="POST">
                <input type="hidden" name="action" value="create">

                <div class="form-group">
                    <label class="form-label">Tipo de Cadastro</label>
                    <select name="role" class="form-control" required>
                        <option value="employee">Colaborador</option>
                        <option value="manager">Gerente</option>
                        <?php if (in_array($_SESSION['role'], ['company_admin', 'admin'])): ?>
                        <option value="responsible">Responsável</option>
                        <option value="company_admin">Admin da Empresa</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Matrícula</label>
                    <input type="text" name="matricula" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Nome Completo</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Senha Inicial</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Cargo</label>
                        <input type="text" name="cargo" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Área</label>
                        <input type="text" name="area" class="form-control" required>
                    </div>
                </div>

                <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Salvar</button>
                    <button type="button" onclick="closeModal()" class="btn btn-outline"
                        style="flex: 1;">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Import Modal -->
    <div id="importModal" class="modal">
        <div class="modal-content">
            <h2 style="margin-bottom: 1rem;">Importar Planilha</h2>
            <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:1.5rem;">
                O arquivo deve ser <b>.csv</b> (separado por vírgulas) com as seguintes colunas na ordem:<br><br>
                <code>Matrícula, Nome, Email, Senha, Tipo (manager ou employee), Cargo, Área</code>
            </p>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="import_csv">
                <div class="form-group">
                    <label class="form-label">Selecionar Arquivo CSV</label>
                    <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                </div>
                <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Importar Dados</button>
                    <button type="button" onclick="closeImportModal()" class="btn btn-outline"
                        style="flex: 1;">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            // Hide all
            document.getElementById('tab-managers').style.display = 'none';
            document.getElementById('tab-employees').style.display = 'none';

            // Remove active class
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));

            // Show selected
            document.getElementById('tab-' + tab).style.display = 'block';
            event.target.classList.add('active');
        }

        function openModal() {
            document.getElementById('userModal').classList.add('open');
        }

        function closeModal() {
            document.getElementById('userModal').classList.remove('open');
        }
        function openImportModal() {
            document.getElementById('importModal').classList.add('open');
        }

        function closeImportModal() {
            document.getElementById('importModal').classList.remove('open');
        }
    </script>
</body>

</html>
<?php
$pageTitle = 'Gerenciar Responsáveis';
require_once '../config.php';
require_once 'header.php';

$message = '';

// Handle Create/Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $company_id = $_POST['company_id'];

            if ($name && $email && $password && $company_id) {
                // Check email existence
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM entrev_users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetchColumn() > 0) {
                    $message = "E-mail já está em uso.";
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO entrev_users (name, email, password, role, company_id) VALUES (?, ?, ?, 'responsible', ?)");
                    try {
                        $stmt->execute([$name, $email, $hash, $company_id]);
                        $message = "Usuário criado com sucesso.";
                    } catch (Exception $e) {
                        $message = "Erro ao criar usuário: " . $e->getMessage();
                    }
                }
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM entrev_users WHERE id = ?");
            $stmt->execute([$id]);
            $message = "Usuário removido com sucesso.";
        }
    }
}

// List Users with Company Name
$users = $pdo->query("SELECT u.*, c.name as company_name FROM entrev_users u LEFT JOIN entrev_companies c ON u.company_id = c.id WHERE u.role = 'responsible' ORDER BY u.created_at DESC")->fetchAll();
$companies = $pdo->query("SELECT * FROM entrev_companies ORDER BY name ASC")->fetchAll();
?>

<?php if ($message): ?>
    <div
        style="background: rgba(16, 185, 129, 0.2); color: #6ee7b7; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<div class="glass-card" style="margin-bottom: 2rem;">
    <h3>Novo Responsável</h3>
    <form method="POST"
        style="margin-top: 1rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
        <input type="hidden" name="action" value="create">
        <div class="form-group" style="margin-bottom:0;">
            <input type="text" name="name" class="form-control" placeholder="Nome Completo" required>
        </div>
        <div class="form-group" style="margin-bottom:0;">
            <input type="email" name="email" class="form-control" placeholder="E-mail" required>
        </div>
        <div class="form-group" style="margin-bottom:0;">
            <input type="password" name="password" class="form-control" placeholder="Senha" required>
        </div>
        <div class="form-group" style="margin-bottom:0;">
            <select name="company_id" class="form-control" required style="background: rgba(15, 23, 42, 0.9);">
                <option value="">Selecione a Empresa</option>
                <?php foreach ($companies as $company): ?>
                    <option value="<?= $company['id'] ?>">
                        <?= htmlspecialchars($company['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Adicionar</button>
    </form>
</div>

<div class="glass-card">
    <h3>Lista de Responsáveis</h3>
    <div style="overflow-x: auto; margin-top: 1rem;">
        <table style="width: 100%; border-collapse: collapse; color: var(--text-color);">
            <thead>
                <tr style="border-bottom: 1px solid var(--glass-border); text-align: left;">
                    <th style="padding: 1rem;">Nome</th>
                    <th style="padding: 1rem;">E-mail</th>
                    <th style="padding: 1rem;">Empresa</th>
                    <th style="padding: 1rem;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td style="padding: 1rem;">
                            <?= htmlspecialchars($user['name']) ?>
                        </td>
                        <td style="padding: 1rem;">
                            <?= htmlspecialchars($user['email']) ?>
                        </td>
                        <td style="padding: 1rem;">
                            <?= htmlspecialchars($user['company_name'] ?? 'N/A') ?>
                        </td>
                        <td style="padding: 1rem;">
                            <form method="POST" onsubmit="return confirm('Tem certeza?');" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                <button type="submit"
                                    style="background: none; border: none; color: #ef4444; cursor: pointer;">Excluir</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'footer.php'; ?>
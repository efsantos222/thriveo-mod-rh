<?php
$pageTitle = 'Gerenciar Empresas';
require_once '../config.php';
require_once 'header.php';

$message = '';

// Handle Create/Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            $name = $_POST['name'];
            if ($name) {
                $stmt = $pdo->prepare("INSERT INTO entrev_companies (name) VALUES (?)");
                $stmt->execute([$name]);
                $message = "Empresa criada com sucesso.";
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM entrev_companies WHERE id = ?");
            $stmt->execute([$id]);
            $message = "Empresa removida com sucesso.";
        }
    }
}

// List Companies
$companies = $pdo->query("SELECT * FROM entrev_companies ORDER BY created_at DESC")->fetchAll();
?>

<?php if ($message): ?>
    <div
        style="background: rgba(16, 185, 129, 0.2); color: #6ee7b7; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<div class="glass-card" style="margin-bottom: 2rem;">
    <h3>Nova Empresa</h3>
    <form method="POST" style="margin-top: 1rem; display: flex; gap: 1rem;">
        <input type="hidden" name="action" value="create">
        <input type="text" name="name" class="form-control" placeholder="Nome da Empresa" required>
        <button type="submit" class="btn btn-primary">Adicionar</button>
    </form>
</div>

<div class="glass-card">
    <h3>Lista de Empresas</h3>
    <div style="overflow-x: auto; margin-top: 1rem;">
        <table style="width: 100%; border-collapse: collapse; color: var(--text-color);">
            <thead>
                <tr style="border-bottom: 1px solid var(--glass-border); text-align: left;">
                    <th style="padding: 1rem;">ID</th>
                    <th style="padding: 1rem;">Nome</th>
                    <th style="padding: 1rem;">Data Cadastro</th>
                    <th style="padding: 1rem;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($companies as $company): ?>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td style="padding: 1rem;">
                            <?= $company['id'] ?>
                        </td>
                        <td style="padding: 1rem;">
                            <?= htmlspecialchars($company['name']) ?>
                        </td>
                        <td style="padding: 1rem;">
                            <?= date('d/m/Y', strtotime($company['created_at'])) ?>
                        </td>
                        <td style="padding: 1rem;">
                            <form method="POST"
                                onsubmit="return confirm('Tem certeza? Isso excluirá todos os usuários e dados desta empresa.');"
                                style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $company['id'] ?>">
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
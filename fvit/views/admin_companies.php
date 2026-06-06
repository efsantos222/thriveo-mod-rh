<?php
// Add Company
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_company'])) {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO companies (name) VALUES (?)");
        $stmt->execute([$name]);
    }
}

// Delete Company
if (isset($_GET['delete_company'])) {
    $id = $_GET['delete_company'];
    $stmt = $pdo->prepare("DELETE FROM companies WHERE id = ?");
    $stmt->execute([$id]);
    echo "<script>window.location.href='dashboard.php?page=companies';</script>";
}

$companies = $pdo->query("SELECT * FROM companies ORDER BY created_at DESC")->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2>Gerenciar Empresas</h2>
</div>

<div class="glass-card" style="margin-bottom: 2rem;">
    <h3>Adicionar Nova Empresa</h3>
    <form method="POST" style="display: flex; gap: 1rem; align-items: flex-end;">
        <div class="form-group" style="flex: 1; margin-bottom: 0;">
            <label>Nome da Empresa</label>
            <input type="text" name="name" required placeholder="Ex: Tech Solutions Ltda">
        </div>
        <button type="submit" name="add_company" class="btn btn-primary">Adicionar</button>
    </form>
</div>

<div class="glass-card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Data Criação</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($companies as $c): ?>
                <tr>
                    <td>#
                        <?= $c['id'] ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($c['name']) ?>
                    </td>
                    <td>
                        <?= date('d/m/Y', strtotime($c['created_at'])) ?>
                    </td>
                    <td>
                        <a href="?page=companies&delete_company=<?= $c['id'] ?>" class="btn-danger"
                            style="padding: 0.4rem 0.8rem; font-size: 0.8rem; border-radius: 4px;"
                            onclick="return confirm('Tem certeza? Isso pode afetar usuários vinculados.')">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
require_once '../config/db.php';
require_once 'header.php';

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$message = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $cnpj = $_POST['cnpj'];
    
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Update
        $stmt = $pdo->prepare("UPDATE companies SET name = ?, cnpj = ? WHERE id = ?");
        $stmt->execute([$name, $cnpj, $_POST['id']]);
        $message = "Empresa atualizada com sucesso!";
    } else {
        // Create
        $stmt = $pdo->prepare("INSERT INTO companies (name, cnpj) VALUES (?, ?)");
        $stmt->execute([$name, $cnpj]);
        $message = "Empresa criada com sucesso!";
    }
    $action = 'list'; // Redirect back to list
}

// Handle Delete
if ($action === 'delete' && $id) {
    $stmt = $pdo->prepare("DELETE FROM companies WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: companies.php"); // Refresh to clear params
    exit;
}

?>

<div style="margin-bottom: 2rem;">
    <?php if ($message): ?>
        <div style="background: rgba(16, 185, 129, 0.2); color: #10b981; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1>Gerenciar Empresas</h1>
        <?php if($action === 'list'): ?>
            <a href="companies.php?action=create" class="btn btn-primary">+ Nova Empresa</a>
        <?php else: ?>
            <a href="companies.php" class="btn btn-outline">Voltar</a>
        <?php endif; ?>
    </div>
</div>

<?php if ($action === 'create' || $action === 'edit'): ?>
    <?php
    $company = ['name' => '', 'cnpj' => '', 'id' => ''];
    if ($action === 'edit' && $id) {
        $stmt = $pdo->prepare("SELECT * FROM companies WHERE id = ?");
        $stmt->execute([$id]);
        $company = $stmt->fetch();
    }
    ?>
    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <h3><?= $action === 'create' ? 'Cadastrar Empresa' : 'Editar Empresa' ?></h3>
        <form method="POST" action="companies.php">
            <input type="hidden" name="id" value="<?= $company['id'] ?>">
            <div class="form-group">
                <label>Nome da Empresa</label>
                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($company['name']) ?>">
            </div>
            <div class="form-group">
                <label>CNPJ</label>
                <input type="text" name="cnpj" class="form-control" value="<?= htmlspecialchars($company['cnpj']) ?>">
            </div>
            <button type="submit" class="btn btn-primary">Salvar</button>
        </form>
    </div>

<?php else: ?>
    <!-- LIST VIEW -->
    <?php
    $stmt = $pdo->query("SELECT * FROM companies ORDER BY created_at DESC");
    $companies = $stmt->fetchAll();
    ?>
    <div class="card table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>CNPJ</th>
                    <th>Data Cadastro</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($companies as $c): ?>
                <tr>
                    <td>#<?= $c['id'] ?></td>
                    <td><?= htmlspecialchars($c['name']) ?></td>
                    <td><?= htmlspecialchars($c['cnpj']) ?></td>
                    <td><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
                    <td>
                        <a href="companies.php?action=edit&id=<?= $c['id'] ?>" class="btn btn-outline" style="font-size: 0.8rem; padding: 0.2rem 0.6rem;">Editar</a>
                        <a href="companies.php?action=delete&id=<?= $c['id'] ?>" class="btn btn-outline" style="font-size: 0.8rem; padding: 0.2rem 0.6rem; border-color: #ef4444; color: #ef4444;" onclick="return confirm('Tem certeza?')">Excluir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if(count($companies) == 0): ?>
            <p style="text-align: center; padding: 2rem; color: var(--text-muted);">Nenhuma empresa cadastrada.</p>
        <?php endif; ?>
    </div>
<?php endif; ?>

</body>
</html>

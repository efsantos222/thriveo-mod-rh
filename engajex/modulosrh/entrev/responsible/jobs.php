<?php
$pageTitle = 'Gerenciar Vagas';
require_once '../config.php';
require_once 'header.php';

$company_id = $_SESSION['company_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $specifications = $_POST['specifications'];

            $stmt = $pdo->prepare("INSERT INTO entrev_jobs (company_id, title, description, specifications) VALUES (?, ?, ?, ?)");
            $stmt->execute([$company_id, $title, $description, $specifications]);
            $message = "Vaga criada com sucesso.";
        } elseif ($_POST['action'] === 'delete') {
            $id = $_POST['id'];
            // Verify ownership
            $check = $pdo->prepare("SELECT id FROM entrev_jobs WHERE id = ? AND company_id = ?");
            $check->execute([$id, $company_id]);
            if ($check->rowCount() > 0) {
                $stmt = $pdo->prepare("DELETE FROM entrev_jobs WHERE id = ?");
                $stmt->execute([$id]);
                $message = "Vaga removida com sucesso.";
            }
        }
    }
}

$jobs = $pdo->prepare("SELECT * FROM entrev_jobs WHERE company_id = ? ORDER BY created_at DESC");
$jobs->execute([$company_id]);
$jobs = $jobs->fetchAll();
?>

<?php if ($message): ?>
    <div
        style="background: rgba(16, 185, 129, 0.2); color: #6ee7b7; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<div class="glass-card" style="margin-bottom: 2rem;">
    <h3>Nova Vaga</h3>
    <form method="POST" style="margin-top: 1rem;">
        <input type="hidden" name="action" value="create">
        <div class="form-group">
            <label class="form-label">Cargo / Título</label>
            <input type="text" name="title" class="form-control" placeholder="Ex: Desenvolvedor Fullstack" required>
        </div>
        <div class="form-group">
            <label class="form-label">Descrição da Vaga</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Resumo das atividades..."
                required></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Especificações (Requisitos, Skills)</label>
            <textarea name="specifications" class="form-control" rows="3"
                placeholder="Ex: PHP, CSS, Experiência c/ Hostgator..." required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Cadastrar Vaga</button>
    </form>
</div>

<div class="glass-card">
    <h3>Vagas Cadastradas</h3>
    <div style="overflow-x: auto; margin-top: 1rem;">
        <table style="width: 100%; border-collapse: collapse; color: var(--text-color);">
            <thead>
                <tr style="border-bottom: 1px solid var(--glass-border); text-align: left;">
                    <th style="padding: 1rem;">Cargo</th>
                    <th style="padding: 1rem;">Data</th>
                    <th style="padding: 1rem;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jobs as $job): ?>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td style="padding: 1rem;">
                            <?= htmlspecialchars($job['title']) ?>
                        </td>
                        <td style="padding: 1rem;">
                            <?= date('d/m/Y', strtotime($job['created_at'])) ?>
                        </td>
                        <td style="padding: 1rem; display: flex; gap: 0.5rem;">
                            <a href="job_details.php?id=<?= $job['id'] ?>" class="btn btn-primary"
                                style="padding: 6px 12px; font-size: 0.85rem;">Gerenciar</a>
                            <form method="POST" onsubmit="return confirm('Tem certeza?');" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $job['id'] ?>">
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
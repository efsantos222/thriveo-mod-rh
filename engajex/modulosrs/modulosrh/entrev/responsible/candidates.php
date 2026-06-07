<?php
$pageTitle = 'Gerenciar Candidatos';
require_once '../config.php';
require_once 'header.php';

$company_id = $_SESSION['company_id'];
$message = '';

// Handle Upload Directory
$uploadDir = '../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            $name = $_POST['name'];
            $email = $_POST['email'];

            $pdfName = null;
            if (isset($_FILES['linkedin_pdf']) && $_FILES['linkedin_pdf']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['linkedin_pdf']['name'], PATHINFO_EXTENSION));
                if ($ext === 'pdf') {
                    $newName = uniqid() . '_' . time() . '.pdf';
                    if (move_uploaded_file($_FILES['linkedin_pdf']['tmp_name'], $uploadDir . $newName)) {
                        $pdfName = $newName;
                    } else {
                        $message = "Erro ao fazer upload do arquivo.";
                    }
                } else {
                    $message = "Apenas arquivos PDF são permitidos.";
                }
            }

            if (!$message) { // Proceed if no upload error
                $stmt = $pdo->prepare("INSERT INTO candidates (company_id, name, email, linkedin_pdf) VALUES (?, ?, ?, ?)");
                $stmt->execute([$company_id, $name, $email, $pdfName]);
                $message = "Candidato cadastrado com sucesso.";
            }

        } elseif ($_POST['action'] === 'delete') {
            $id = $_POST['id'];
            $check = $pdo->prepare("SELECT id, linkedin_pdf FROM candidates WHERE id = ? AND company_id = ?");
            $check->execute([$id, $company_id]);
            $candidate = $check->fetch();

            if ($candidate) {
                if ($candidate['linkedin_pdf'] && file_exists($uploadDir . $candidate['linkedin_pdf'])) {
                    unlink($uploadDir . $candidate['linkedin_pdf']);
                }
                $stmt = $pdo->prepare("DELETE FROM candidates WHERE id = ?");
                $stmt->execute([$id]);
                $message = "Candidato removido com sucesso.";
            }
        }
    }
}

$candidates = $pdo->prepare("SELECT * FROM candidates WHERE company_id = ? ORDER BY created_at DESC");
$candidates->execute([$company_id]);
$candidates = $candidates->fetchAll();
?>

<?php if ($message): ?>
    <div
        style="background: rgba(16, 185, 129, 0.2); color: #6ee7b7; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<div class="glass-card" style="margin-bottom: 2rem;">
    <h3>Novo Candidato</h3>
    <form method="POST" enctype="multipart/form-data" style="margin-top: 1rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
        <input type="hidden" name="action" value="create">
        <div class="form-group" style="margin-bottom:0;">
            <input type="text" name="name" class="form-control" placeholder="Nome Completo" required>
        </div>
        <div class="form-group" style="margin-bottom:0;">
            <input type="email" name="email" class="form-control" placeholder="E-mail" required>
        </div>
        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" style="font-size: 0.8rem; margin-bottom: 2px;">Perfil LinkedIn (PDF)</label>
            <input type="file" name="linkedin_pdf" class="form-control" accept=".pdf">
        </div>
        <button type="submit" class="btn btn-primary" style="height: fit-content; align-self: flex-end;">Cadastrar</button>
    </form>
</div>

<div class="glass-card">
    <h3>Candidatos Cadastrados</h3>
    <div style="overflow-x: auto; margin-top: 1rem;">
        <table style="width: 100%; border-collapse: collapse; color: var(--text-color);">
            <thead>
                <tr style="border-bottom: 1px solid var(--glass-border); text-align: left;">
                    <th style="padding: 1rem;">Nome</th>
                    <th style="padding: 1rem;">E-mail</th>
                    <th style="padding: 1rem;">PDF</th>
                    <th style="padding: 1rem;">Data Cadastro</th>
                    <th style="padding: 1rem;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($candidates as $candidate): ?>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td style="padding: 1rem;"><?= htmlspecialchars($candidate['name']) ?></td>
                        <td style="padding: 1rem;"><?= htmlspecialchars($candidate['email']) ?></td>
                        <td style="padding: 1rem;">
                            <?php if(!empty($candidate['linkedin_pdf'])): ?>
                                <a href="../uploads/<?= htmlspecialchars($candidate['linkedin_pdf']) ?>" target="_blank" style="color: var(--primary-color);">Ver PDF</a>
                            <?php else: ?>
                                <span style="color: var(--text-muted);">-</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 1rem;"><?= date('d/m/Y', strtotime($candidate['created_at'])) ?></td>
                        <td style="padding: 1rem;">
                            <form method="POST" onsubmit="return confirm('Tem certeza?');" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $candidate['id'] ?>">
                                <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer;">Excluir</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'footer.php'; ?>
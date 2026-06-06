<?php
require_once '../config/db.php';
require_once 'header.php';

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$message = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    // Automatically assign current coordinator if not specified (though specific assignment logic could be added)
    $coordinator_id = $_SESSION['user_id'];

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Update
        $stmt = $pdo->prepare("UPDATE courses SET title = ?, description = ? WHERE id = ?");
        $stmt->execute([$title, $description, $_POST['id']]);
        $message = "Curso atualizado com sucesso!";
    } else {
        // Create
        $stmt = $pdo->prepare("INSERT INTO courses (title, description, coordinator_id) VALUES (?, ?, ?)");
        $stmt->execute([$title, $description, $coordinator_id]);
        $message = "Curso criado com sucesso!";
    }
    $action = 'list';
}

// Handle Delete
if ($action === 'delete' && $id) {
    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: courses.php");
    exit;
}
?>

<div style="margin-bottom: 2rem;">
    <?php if ($message): ?>
        <div
            style="background: rgba(16, 185, 129, 0.2); color: #10b981; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1>Gerenciar Cursos</h1>
        <?php if ($action === 'list'): ?>
            <a href="courses.php?action=create" class="btn btn-primary">+ Novo Curso</a>
        <?php else: ?>
            <a href="courses.php" class="btn btn-outline">Voltar</a>
        <?php endif; ?>
    </div>
</div>

<?php if ($action === 'create' || $action === 'edit'): ?>
    <?php
    $course = ['title' => '', 'description' => '', 'id' => ''];
    if ($action === 'edit' && $id) {
        $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->execute([$id]);
        $course = $stmt->fetch();
    }
    ?>
    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <h3>
            <?= $action === 'create' ? 'Cadastrar Curso' : 'Editar Curso' ?>
        </h3>
        <form method="POST" action="courses.php">
            <input type="hidden" name="id" value="<?= $course['id'] ?>">
            <div class="form-group">
                <label>Título do Curso</label>
                <input type="text" name="title" class="form-control" required
                    value="<?= htmlspecialchars($course['title']) ?>">
            </div>
            <div class="form-group">
                <label>Descrição</label>
                <textarea name="description" class="form-control"
                    rows="5"><?= htmlspecialchars($course['description']) ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Salvar</button>
        </form>
    </div>

<?php else: ?>
    <!-- LIST VIEW -->
    <?php
    $stmt = $pdo->query("SELECT * FROM courses ORDER BY created_at DESC");
    $courses = $stmt->fetchAll();
    ?>
    <div class="card table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Descrição</th>
                    <th>Data Criação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $c): ?>
                    <tr>
                        <td>#
                            <?= $c['id'] ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($c['title']) ?>
                        </td>
                        <td>
                            <?= htmlspecialchars(substr($c['description'], 0, 50)) ?>...
                        </td>
                        <td>
                            <?= date('d/m/Y', strtotime($c['created_at'])) ?>
                        </td>
                        <td>
                            <a href="courses.php?action=edit&id=<?= $c['id'] ?>" class="btn btn-outline"
                                style="font-size: 0.8rem; padding: 0.2rem 0.6rem;">Editar</a>
                            <a href="courses.php?action=delete&id=<?= $c['id'] ?>" class="btn btn-outline"
                                style="font-size: 0.8rem; padding: 0.2rem 0.6rem; border-color: #ef4444; color: #ef4444;"
                                onclick="return confirm('Tem certeza?')">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (count($courses) == 0): ?>
            <p style="text-align: center; padding: 2rem; color: var(--text-muted);">Nenhum curso cadastrado.</p>
        <?php endif; ?>
    </div>
<?php endif; ?>

</body>

</html>
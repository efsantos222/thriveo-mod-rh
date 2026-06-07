<?php
requireLogin();
$courseManager = new \Courses\CourseManager($pdo);
$courses = $courseManager->listCourses($_GET);
$userCourses = $courseManager->getUserCourses($_SESSION['user_id']);

$content = <<<HTML
<div class="row mb-4">
    <div class="col">
        <h2>Catálogo de Cursos</h2>
    </div>
    <?php if ($_SESSION['user_role'] === 'admin'): ?>
    <div class="col-auto">
        <a href="/courses/create" class="btn btn-primary">Novo Curso</a>
    </div>
    <?php endif; ?>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form method="get" class="row g-3">
                    <div class="col-md-4">
                        <select name="category" class="form-select">
                            <option value="">Todas as Categorias</option>
                            <option value="leadership" <?= $_GET['category'] === 'leadership' ? 'selected' : '' ?>>Liderança</option>
                            <option value="communication" <?= $_GET['category'] === 'communication' ? 'selected' : '' ?>>Comunicação</option>
                            <option value="management" <?= $_GET['category'] === 'management' ? 'selected' : '' ?>>Gestão</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select name="level" class="form-select">
                            <option value="">Todos os Níveis</option>
                            <option value="basic" <?= $_GET['level'] === 'basic' ? 'selected' : '' ?>>Básico</option>
                            <option value="intermediate" <?= $_GET['level'] === 'intermediate' ? 'selected' : '' ?>>Intermediário</option>
                            <option value="advanced" <?= $_GET['level'] === 'advanced' ? 'selected' : '' ?>>Avançado</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <?php foreach ($courses as $course): ?>
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($course['title']) ?></h5>
                <p class="card-text text-muted">
                    <span class="badge bg-primary"><?= getCategoryLabel($course['category']) ?></span>
                    <span class="badge bg-secondary"><?= getLevelLabel($course['level']) ?></span>
                </p>
                <p class="card-text"><?= substr(htmlspecialchars($course['description']), 0, 150) ?>...</p>
            </div>
            <div class="card-footer bg-transparent">
                <?php
                $enrollment = findEnrollment($userCourses, $course['id']);
                if ($enrollment):
                ?>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="progress flex-grow-1 me-3" style="height: 10px;">
                        <div class="progress-bar" role="progressbar" style="width: <?= $enrollment['progress'] ?>%"
                             aria-valuenow="<?= $enrollment['progress'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <span class="text-muted"><?= $enrollment['progress'] ?>%</span>
                </div>
                <a href="/courses/view/<?= $course['id'] ?>" class="btn btn-primary mt-2 w-100">Continuar</a>
                <?php else: ?>
                <a href="/courses/enroll/<?= $course['id'] ?>" class="btn btn-outline-primary w-100">Inscrever-se</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($courses)): ?>
    <div class="col-12">
        <div class="alert alert-info">
            Nenhum curso encontrado com os filtros selecionados.
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if (!empty($userCourses)): ?>
<div class="row mt-4">
    <div class="col-12">
        <h3>Meus Cursos</h3>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Curso</th>
                        <th>Progresso</th>
                        <th>Status</th>
                        <th>Certificado</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($userCourses as $enrollment): ?>
                    <tr>
                        <td><?= htmlspecialchars($enrollment['title']) ?></td>
                        <td>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar" role="progressbar" style="width: <?= $enrollment['progress'] ?>%"></div>
                            </div>
                        </td>
                        <td><span class="badge bg-<?= getStatusColor($enrollment['status']) ?>"><?= getStatusLabel($enrollment['status']) ?></span></td>
                        <td>
                            <?php if ($enrollment['certificate_issued']): ?>
                            <a href="/courses/certificate/<?= $enrollment['id'] ?>" class="btn btn-sm btn-success">Download</a>
                            <?php else: ?>
                            <span class="text-muted">Indisponível</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/courses/view/<?= $enrollment['id'] ?>" class="btn btn-sm btn-primary">Acessar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
function getCategoryLabel($category) {
    return [
        'leadership' => 'Liderança',
        'communication' => 'Comunicação',
        'management' => 'Gestão'
    ][$category] ?? $category;
}

function getLevelLabel($level) {
    return [
        'basic' => 'Básico',
        'intermediate' => 'Intermediário',
        'advanced' => 'Avançado'
    ][$level] ?? $level;
}

function getStatusColor($status) {
    return [
        'enrolled' => 'secondary',
        'in_progress' => 'primary',
        'completed' => 'success'
    ][$status] ?? 'secondary';
}

function getStatusLabel($status) {
    return [
        'enrolled' => 'Inscrito',
        'in_progress' => 'Em Andamento',
        'completed' => 'Concluído'
    ][$status] ?? 'Desconhecido';
}

function findEnrollment($enrollments, $courseId) {
    foreach ($enrollments as $enrollment) {
        if ($enrollment['id'] === $courseId) {
            return $enrollment;
        }
    }
    return null;
}
?>
HTML;

require __DIR__ . '/../base.php';

<?php
$pageTitle = "Módulo do Curso";
require_once TEMPLATES_PATH . '/base.php';
require_once SRC_PATH . '/models/Course.php';

if (!isset($_GET['id']) || !isset($_GET['module_id'])) {
    header('Location: ?route=courses/catalog');
    exit;
}

$courseModel = new \PDI\Models\Course($pdo);
$course = $courseModel->get($_GET['id']);
$module = $courseModel->getModule($_GET['module_id']);

if (!$course || !$module || $module['course_id'] != $course['id']) {
    header('Location: ?route=courses/catalog');
    exit;
}

// Verificar se o usuário está inscrito
$stmt = $pdo->prepare('SELECT * FROM course_enrollments WHERE user_id = ? AND course_id = ?');
$stmt->execute([$_SESSION['user']['id'], $course['id']]);
$enrollment = $stmt->fetch();

if (!$enrollment && $_SESSION['user']['role'] !== 'admin') {
    header('Location: ?route=courses/view&id=' . $course['id']);
    exit;
}
?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?route=courses/catalog">Catálogo</a></li>
            <li class="breadcrumb-item"><a href="?route=courses/view&id=<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['title']); ?></a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($module['title']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title"><?php echo htmlspecialchars($module['title']); ?></h2>
                    
                    <?php if (!empty($module['description'])): ?>
                        <div class="card-text mb-4">
                            <h5>Descrição</h5>
                            <?php echo nl2br(htmlspecialchars($module['description'])); ?>
                        </div>
                    <?php endif; ?>

                    <div class="card-text module-content">
                        <h5>Conteúdo</h5>
                        <?php echo $module['content']; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Módulos do Curso</h5>
                </div>
                <div class="list-group list-group-flush">
                    <?php 
                    $modules = $courseModel->getModules($course['id']);
                    foreach ($modules as $m): 
                        $isActive = $m['id'] == $module['id'];
                    ?>
                        <a href="?route=courses/module&id=<?php echo $course['id']; ?>&module_id=<?php echo $m['id']; ?>" 
                           class="list-group-item list-group-item-action <?php echo $isActive ? 'active' : ''; ?>">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1"><?php echo htmlspecialchars($m['title']); ?></h6>
                            </div>
                            <?php if (!empty($m['description'])): ?>
                                <small class="text-muted"><?php echo htmlspecialchars(substr($m['description'], 0, 100)); ?><?php echo strlen($m['description']) > 100 ? '...' : ''; ?></small>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.module-content img {
    max-width: 100%;
    height: auto;
}
</style>

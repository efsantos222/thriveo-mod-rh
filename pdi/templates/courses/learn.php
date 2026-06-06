<?php
$pageTitle = "Aprender";
require_once TEMPLATES_PATH . '/base.php';
require_once SRC_PATH . '/models/Course.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user'])) {
    header('Location: ?route=login');
    exit;
}

// Verificar se o ID do curso foi fornecido
if (!isset($_GET['id'])) {
    header('Location: ?route=courses/catalog');
    exit;
}

$courseId = $_GET['id'];
$userId = $_SESSION['user']['id'];

// Verificar se o usuário está inscrito
$stmt = $pdo->prepare('
    SELECT * FROM course_enrollments 
    WHERE user_id = ? AND course_id = ?
');
$stmt->execute([$userId, $courseId]);
$enrollment = $stmt->fetch();

if (!$enrollment) {
    $_SESSION['flash'] = [
        'type' => 'warning',
        'message' => 'Você precisa se inscrever no curso primeiro.'
    ];
    header('Location: ?route=courses/view&id=' . $courseId);
    exit;
}

// Buscar informações do curso
$courseModel = new \PDI\Models\Course($pdo);
$course = $courseModel->get($courseId);

if (!$course) {
    header('Location: ?route=courses/catalog');
    exit;
}

// Buscar módulos do curso
$stmt = $pdo->prepare('
    SELECT * FROM course_modules 
    WHERE course_id = ? 
    ORDER BY order_index
');
$stmt->execute([$courseId]);
$modules = $stmt->fetchAll();

// Buscar conteúdo dos módulos
$moduleContents = [];
if (!empty($modules)) {
    $moduleIds = array_column($modules, 'id');
    $placeholders = str_repeat('?,', count($moduleIds) - 1) . '?';
    
    $stmt = $pdo->prepare("
        SELECT * FROM module_contents 
        WHERE module_id IN ($placeholders)
        ORDER BY module_id, order_index
    ");
    $stmt->execute($moduleIds);
    
    while ($content = $stmt->fetch()) {
        $moduleContents[$content['module_id']][] = $content;
    }
}
?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?route=courses/catalog">Catálogo</a></li>
            <li class="breadcrumb-item"><a href="?route=courses/view&id=<?php echo $courseId; ?>"><?php echo htmlspecialchars($course['title']); ?></a></li>
            <li class="breadcrumb-item active">Aprender</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Sidebar com módulos -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Módulos</h5>
                </div>
                <div class="list-group list-group-flush">
                    <?php if (empty($modules)): ?>
                        <div class="list-group-item">
                            <p class="text-muted mb-0">Este curso ainda não possui módulos.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($modules as $module): ?>
                            <a href="#module-<?php echo $module['id']; ?>" class="list-group-item list-group-item-action">
                                <?php echo htmlspecialchars($module['title']); ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($enrollment['status'] !== 'completed'): ?>
            <div class="card mt-3">
                <div class="card-body">
                    <form action="?route=courses/complete" method="post">
                        <input type="hidden" name="course_id" value="<?php echo $courseId; ?>">
                        <button type="submit" class="btn btn-success w-100">Marcar como Concluído</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Conteúdo principal -->
        <div class="col-md-9">
            <?php if (empty($modules)): ?>
                <div class="alert alert-info">
                    Este curso ainda não possui conteúdo. Por favor, volte mais tarde.
                </div>
            <?php else: ?>
                <?php foreach ($modules as $module): ?>
                    <div class="card mb-4" id="module-<?php echo $module['id']; ?>">
                        <div class="card-header">
                            <h4 class="mb-0"><?php echo htmlspecialchars($module['title']); ?></h4>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($module['description'])): ?>
                                <p class="text-muted"><?php echo nl2br(htmlspecialchars($module['description'])); ?></p>
                            <?php endif; ?>

                            <?php if (isset($moduleContents[$module['id']])): ?>
                                <?php foreach ($moduleContents[$module['id']] as $content): ?>
                                    <div class="content-item mb-4">
                                        <h5><?php echo htmlspecialchars($content['title']); ?></h5>
                                        <div class="content-body">
                                            <?php echo $content['content']; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">Este módulo ainda não possui conteúdo.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once TEMPLATES_PATH . '/footer.php'; ?>

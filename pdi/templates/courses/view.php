<?php
$pageTitle = "Visualizar Curso";
require_once TEMPLATES_PATH . '/base.php';
require_once SRC_PATH . '/models/Course.php';

if (!isset($_GET['id'])) {
    header('Location: ?route=courses/catalog');
    exit;
}

$courseModel = new \PDI\Models\Course($pdo);
$course = $courseModel->get($_GET['id']);

if (!$course) {
    header('Location: ?route=courses/catalog');
    exit;
}

// Verificar se o usuário está inscrito
$enrollment = null;
$ratings = [];
$ratingStats = ['avg_rating' => 0, 'total_ratings' => 0];

try {
    // Verificar se a tabela course_enrollments existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'course_enrollments'");
    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->prepare('
            SELECT * FROM course_enrollments 
            WHERE user_id = ? AND course_id = ?
        ');
        $stmt->execute([$_SESSION['user']['id'], $course['id']]);
        $enrollment = $stmt->fetch();
    }

    // Verificar se a tabela course_ratings existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'course_ratings'");
    if ($stmt->rowCount() > 0) {
        // Buscar avaliações do curso
        $stmt = $pdo->prepare('
            SELECT cr.*, u.name as user_name 
            FROM course_ratings cr
            JOIN users u ON cr.user_id = u.id
            WHERE cr.course_id = ?
            ORDER BY cr.created_at DESC
        ');
        $stmt->execute([$course['id']]);
        $ratings = $stmt->fetchAll();

        // Calcular média das avaliações
        $stmt = $pdo->prepare('
            SELECT AVG(rating) as avg_rating, COUNT(*) as total_ratings 
            FROM course_ratings 
            WHERE course_id = ?
        ');
        $stmt->execute([$course['id']]);
        $ratingStats = $stmt->fetch();
    }
} catch (\PDOException $e) {
    error_log("Error checking course status: " . $e->getMessage());
}
?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?route=courses/catalog">Catálogo</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($course['title']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8">
            <h2><?php echo htmlspecialchars($course['title']); ?></h2>
            
            <div class="mb-3">
                <span class="badge bg-primary"><?php echo htmlspecialchars($course['category_name']); ?></span>
                <span class="badge bg-info">Nível: <?php echo htmlspecialchars($course['level']); ?></span>
                <span class="badge bg-secondary"><?php echo $course['duration_hours']; ?> horas</span>
            </div>

            <?php if ($ratingStats && $ratingStats['total_ratings'] > 0): ?>
            <div class="mb-3">
                <div class="rating">
                    <?php
                    $avgRating = round($ratingStats['avg_rating']);
                    for ($i = 1; $i <= 5; $i++) {
                        echo '<span class="star ' . ($i <= $avgRating ? 'filled' : '') . '">★</span>';
                    }
                    ?>
                </div>
                <small class="text-muted">(<?php echo $ratingStats['total_ratings']; ?> avaliações)</small>
            </div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Descrição</h5>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
                </div>
            </div>

            <?php
            // Carregar módulos do curso
            $modules = $courseModel->getModules($course['id']);
            ?>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Módulos do Curso</h5>
                </div>
                <div class="list-group list-group-flush">
                    <?php if (empty($modules)): ?>
                        <div class="list-group-item text-muted">
                            Nenhum módulo disponível.
                        </div>
                    <?php else: ?>
                        <?php foreach ($modules as $module): ?>
                            <?php if ($enrollment || $_SESSION['user']['role'] === 'admin'): ?>
                                <a href="?route=courses/module&id=<?php echo $course['id']; ?>&module_id=<?php echo $module['id']; ?>" 
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($module['title']); ?></h6>
                                    </div>
                                    <?php if (!empty($module['description'])): ?>
                                        <p class="mb-1 text-muted"><?php echo htmlspecialchars($module['description']); ?></p>
                                    <?php endif; ?>
                                </a>
                            <?php else: ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($module['title']); ?></h6>
                                    </div>
                                    <?php if (!empty($module['description'])): ?>
                                        <p class="mb-1 text-muted"><?php echo htmlspecialchars($module['description']); ?></p>
                                    <?php endif; ?>
                                    <small class="text-warning">
                                        <i class="fas fa-lock"></i> 
                                        Inscreva-se no curso para acessar este módulo
                                    </small>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Conteúdo do Curso</h5>
                    <div class="course-content">
                        <?php echo $course['content']; ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($ratings)): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Avaliações</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($ratings as $rating): ?>
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo htmlspecialchars($rating['user_name']); ?></strong>
                                <div class="rating">
                                    <?php
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo '<span class="star ' . ($i <= $rating['rating'] ? 'filled' : '') . '">★</span>';
                                    }
                                    ?>
                                </div>
                            </div>
                            <small class="text-muted">
                                <?php echo date('d/m/Y', strtotime($rating['created_at'])); ?>
                            </small>
                        </div>
                        <?php if (!empty($rating['comment'])): ?>
                        <p class="mt-2 mb-0"><?php echo nl2br(htmlspecialchars($rating['comment'])); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <?php if (!$enrollment): ?>
                    <h5 class="card-title">Inscreva-se neste curso</h5>
                    <p class="card-text">Ao se inscrever, você terá acesso a todo o conteúdo do curso.</p>
                    <form action="?route=courses/enroll" method="post">
                        <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                        <button type="submit" class="btn btn-primary w-100">Inscrever-se</button>
                    </form>
                    <?php else: ?>
                    <h5 class="card-title">Você está inscrito!</h5>
                    <p class="card-text">Status: <?php echo ucfirst($enrollment['status']); ?></p>
                    
                    <?php if ($enrollment['status'] === 'enrolled'): ?>
                        <a href="?route=courses/learn&id=<?php echo $course['id']; ?>" class="btn btn-primary w-100 mb-2">
                            Acessar Curso
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($enrollment['status'] === 'completed'): ?>
                        <a href="?route=courses/learn&id=<?php echo $course['id']; ?>" class="btn btn-success w-100 mb-2">
                            Revisar Curso
                        </a>
                        <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#rateModal">
                            Avaliar Curso
                        </button>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.rating {
    display: flex;
    align-items: center;
    gap: 2px;
}

.course-content {
    line-height: 1.6;
}

.course-content ul, 
.course-content ol {
    padding-left: 20px;
    margin-bottom: 1rem;
}

.course-content p {
    margin-bottom: 1rem;
}

.course-content a {
    color: #0d6efd;
    text-decoration: none;
}

.course-content a:hover {
    text-decoration: underline;
}

.star {
    color: #ddd;
    font-size: 1.2em;
}

.star.filled {
    color: #ffc107;
}
</style>

<?php if ($enrollment && $enrollment['status'] === 'completed'): ?>
<!-- Modal de Avaliação -->
<div class="modal fade" id="rateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Avaliar Curso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="ratingForm" action="?route=courses/rate" method="post">
                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Sua avaliação</label>
                        <div class="rating-input">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <input type="radio" name="rating" value="<?php echo $i; ?>" id="star<?php echo $i; ?>" required>
                            <label for="star<?php echo $i; ?>">★</label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="comment" class="form-label">Comentário (opcional)</label>
                        <textarea class="form-control" id="comment" name="comment" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="ratingForm" class="btn btn-primary">Enviar Avaliação</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once TEMPLATES_PATH . '/footer.php'; ?>

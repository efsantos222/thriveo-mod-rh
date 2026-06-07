<?php
$pageTitle = "Catálogo de Cursos";
require_once TEMPLATES_PATH . '/base.php';
require_once SRC_PATH . '/models/Course.php';

$courseModel = new \PDI\Models\Course($pdo);

$categories = $courseModel->getCategories();
$selectedCategory = $_GET['category'] ?? null;
$selectedLevel = $_GET['level'] ?? null;

$filters = [];
if ($selectedCategory) $filters['category_id'] = $selectedCategory;
if ($selectedLevel) $filters['level'] = $selectedLevel;

$courses = $courseModel->getAll($filters);
?>

<div class="container mt-4">
    <h2 class="mb-4">Catálogo de Cursos</h2>

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-md-6">
            <form class="d-flex gap-2" method="get">
                <input type="hidden" name="route" value="courses/catalog">
                <select name="category" class="form-select">
                    <option value="">Todas as Categorias</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" <?php echo $selectedCategory == $category['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <select name="level" class="form-select">
                    <option value="">Todos os Níveis</option>
                    <option value="basic" <?php echo $selectedLevel === 'basic' ? 'selected' : ''; ?>>Básico</option>
                    <option value="intermediate" <?php echo $selectedLevel === 'intermediate' ? 'selected' : ''; ?>>Intermediário</option>
                    <option value="advanced" <?php echo $selectedLevel === 'advanced' ? 'selected' : ''; ?>>Avançado</option>
                </select>
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </form>
        </div>
    </div>

    <!-- Lista de Cursos -->
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach ($courses as $course): ?>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                    <h6 class="card-subtitle mb-2 text-muted">
                        <?php echo htmlspecialchars($course['category_name']); ?> | 
                        <?php echo ucfirst($course['level']); ?>
                    </h6>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">
                            <i class="fas fa-clock"></i> <?php echo $course['duration_hours']; ?> horas
                        </span>
                        <a href="?route=courses/view&id=<?php echo $course['id']; ?>" class="btn btn-primary">
                            Ver Detalhes
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($courses)): ?>
    <div class="alert alert-info mt-4">
        Nenhum curso encontrado com os filtros selecionados.
    </div>
    <?php endif; ?>
</div>

<?php require_once TEMPLATES_PATH . '/footer.php'; ?>
